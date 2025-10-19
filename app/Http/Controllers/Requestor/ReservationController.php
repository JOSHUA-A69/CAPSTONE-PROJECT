<?php

namespace App\Http\Controllers\Requestor;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReservationRequest;
use App\Models\Organization;
use App\Models\Reservation;
use App\Models\Service;
use App\Models\Venue;
use App\Models\User;
use App\Services\ReservationNotificationService;
use App\Services\CancellationNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class ReservationController extends Controller
{
    protected ReservationNotificationService $notificationService;
    protected CancellationNotificationService $cancellationService;

    public function __construct(
        ReservationNotificationService $notificationService,
        CancellationNotificationService $cancellationService
    ) {
        $this->middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':requestor']);
        $this->notificationService = $notificationService;
        $this->cancellationService = $cancellationService;
    }

    public function index()
    {
        $reservations = Reservation::with(['service', 'venue', 'organization', 'officiant'])
            ->where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('requestor.reservations.index', compact('reservations'));
    }

    public function create()
    {
        $services = Service::orderBy('service_name')->get();
        $venues = Venue::orderBy('name')->get();
        $organizations = Organization::with('adviser')->orderBy('org_name')->get();
        $priests = User::where('role', 'priest')->where('status', 'active')->orderBy('first_name')->get();

        return view('requestor.reservations.create', compact('services', 'venues', 'organizations', 'priests'));
    }

    public function store(ReservationRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();

        // If priest is selected, set status to pending_priest_confirmation and notify priest
        // Otherwise, set to pending (old workflow for backwards compatibility)
        if (!empty($data['officiant_id'])) {
            $data['status'] = 'pending_priest_confirmation';
            $data['priest_notified_at'] = now();
        } else {
            $data['status'] = 'pending';
        }

        $data['adviser_notified_at'] = now(); // Mark adviser as notified immediately

        // Handle custom venue
        if ($request->venue_id === 'custom') {
            // Set venue_id to null and store custom venue name
            $data['venue_id'] = null;
            $data['custom_venue_name'] = $request->custom_venue;
        } else {
            // Regular venue selected, ensure custom_venue_name is null
            $data['custom_venue_name'] = null;
        }

        $reservation = Reservation::create($data);

        // Create history record
        $reservation->history()->create([
            'performed_by' => Auth::id(),
            'action' => 'submitted',
            'remarks' => 'Reservation request submitted by requestor' .
                        (!empty($data['officiant_id']) ? ' - Priest notified for confirmation' : ''),
            'performed_at' => now(),
        ]);

        // Send notifications to requestor and organization adviser
        $this->notificationService->notifyReservationSubmitted($reservation);

        // If priest is assigned, also notify the priest
        if (!empty($data['officiant_id'])) {
            // TODO: Send notification to priest
            // $this->notificationService->notifyPriestAssignment($reservation);
        }

        $message = 'Your reservation request has been submitted and the organization adviser has been notified.';
        if (!empty($data['officiant_id'])) {
            $message .= ' The assigned priest has also been notified.';
        }

        return Redirect::route('requestor.reservations.index')
            ->with('status', 'reservation-submitted')
            ->with('message', $message);
    }

    public function show($reservation_id)
    {
        $reservation = Reservation::with(['service', 'venue', 'organization.adviser', 'officiant', 'history.reservation'])
            ->where('user_id', Auth::id())
            ->findOrFail($reservation_id);

        return view('requestor.reservations.show', compact('reservation'));
    }

    /**
     * Cancel a reservation (Requestor-initiated)
     * Requires 7 days notice before event
     */
    public function cancel(Request $request, $reservation_id)
    {
        $request->validate([
            'reason' => 'required|string|min:10|max:1000',
        ]);

        $reservation = Reservation::with(['service', 'organization.adviser', 'officiant'])
            ->where('user_id', Auth::id())
            ->findOrFail($reservation_id);

        // Check if already cancelled
        if (in_array($reservation->status, ['cancelled', 'rejected'])) {
            return Redirect::back()
                ->with('error', 'This reservation cannot be cancelled as it is already ' . $reservation->status);
        }

        // Check if there's already a pending cancellation request
        $existingCancellation = \App\Models\ReservationCancellation::where('reservation_id', $reservation_id)
            ->whereIn('status', ['pending', 'confirmed_by_staff', 'confirmed_by_admin'])
            ->first();

        if ($existingCancellation) {
            return Redirect::back()
                ->with('error', 'A cancellation request for this reservation is already pending.');
        }

        // Validate 7-day notice requirement
        $daysUntilEvent = now()->diffInDays($reservation->schedule_date, false);
        if ($daysUntilEvent < 7) {
            return Redirect::back()
                ->with('error', 'Cancellation is only allowed at least 7 days before the scheduled date. Your event is in ' . $daysUntilEvent . ' day(s).');
        }

        // Create cancellation request
        $cancellation = \App\Models\ReservationCancellation::create([
            'reservation_id' => $reservation_id,
            'requestor_id' => Auth::id(),
            'reason' => $request->input('reason'),
            'status' => 'pending',
        ]);

        // Create history record
        $reservation->history()->create([
            'performed_by' => Auth::id(),
            'action' => 'cancellation_requested',
            'remarks' => 'Requestor requested cancellation. Reason: ' . $request->input('reason'),
            'performed_at' => now(),
        ]);

        // Send cancellation notifications to all parties
        $this->cancellationService->notifyCancellationRequest($reservation, $cancellation);

        return Redirect::route('requestor.reservations.index')
            ->with('status', 'cancellation-requested')
            ->with('message', 'Your cancellation request has been submitted. All involved parties have been notified and will confirm the cancellation.');
    }

    /**
     * Show confirmation page (accessed via token link)
     */
    public function showConfirmation($reservation_id, $token)
    {
        $reservation = Reservation::with(['service', 'venue', 'organization'])
            ->where('reservation_id', $reservation_id)
            ->where('user_id', Auth::id())
            ->where('requestor_confirmation_token', $token)
            ->firstOrFail();

        // Check if already confirmed
        if ($reservation->requestor_confirmed_at) {
            return Redirect::route('requestor.reservations.show', $reservation_id)
                ->with('info', 'This reservation has already been confirmed.');
        }

        // Check if status is valid for confirmation
        if ($reservation->status !== 'adviser_approved' || !$reservation->contacted_at) {
            return Redirect::route('requestor.reservations.show', $reservation_id)
                ->with('error', 'This reservation is not ready for confirmation.');
        }

        return view('requestor.reservations.confirm', compact('reservation', 'token'));
    }

    /**
     * Confirm reservation (requestor accepts)
     */
    public function confirmReservation($reservation_id, $token)
    {
        $reservation = Reservation::where('reservation_id', $reservation_id)
            ->where('user_id', Auth::id())
            ->where('requestor_confirmation_token', $token)
            ->firstOrFail();

        // Validate current state
        if ($reservation->requestor_confirmed_at) {
            return Redirect::route('requestor.reservations.show', $reservation_id)
                ->with('info', 'This reservation was already confirmed.');
        }

        if ($reservation->status !== 'adviser_approved' || !$reservation->contacted_at) {
            return Redirect::route('requestor.reservations.show', $reservation_id)
                ->with('error', 'This reservation cannot be confirmed at this time.');
        }

        // Update reservation
        $reservation->update([
            'requestor_confirmed_at' => now(),
        ]);

        // Create history record
        $reservation->history()->create([
            'performed_by' => Auth::id(),
            'action' => 'confirmed_by_requestor',
            'remarks' => 'Requestor confirmed availability and reservation details',
            'performed_at' => now(),
        ]);

        // TODO: Notify staff that requestor has confirmed
        // $this->notificationService->notifyRequestorConfirmed($reservation);

        return Redirect::route('requestor.reservations.show', $reservation_id)
            ->with('status', 'reservation-confirmed')
            ->with('message', 'Thank you for confirming! The CREaM staff will now proceed to assign an officiant.');
    }

    /**
     * Decline reservation (requestor declines after being contacted)
     */
    public function declineReservation($reservation_id, $token)
    {
        $reservation = Reservation::where('reservation_id', $reservation_id)
            ->where('user_id', Auth::id())
            ->where('requestor_confirmation_token', $token)
            ->firstOrFail();

        // Validate current state
        if ($reservation->requestor_confirmed_at) {
            return Redirect::route('requestor.reservations.show', $reservation_id)
                ->with('error', 'This reservation was already confirmed and cannot be declined.');
        }

        if ($reservation->status !== 'adviser_approved') {
            return Redirect::route('requestor.reservations.show', $reservation_id)
                ->with('error', 'This reservation cannot be declined at this time.');
        }

        // Update reservation to cancelled
        $reservation->update([
            'status' => 'cancelled',
            'cancellation_reason' => 'Declined by requestor after staff contact',
            'cancelled_by' => Auth::id(),
        ]);

        // Create history record
        $reservation->history()->create([
            'performed_by' => Auth::id(),
            'action' => 'declined_by_requestor',
            'remarks' => 'Requestor declined reservation after being contacted by staff',
            'performed_at' => now(),
        ]);

        // Notify staff
        $this->notificationService->notifyCancellation(
            $reservation,
            'Declined by requestor after staff contact',
            Auth::user()->full_name
        );

        return Redirect::route('requestor.reservations.index')
            ->with('status', 'reservation-declined')
            ->with('message', 'You have declined this reservation. The CREaM staff has been notified.');
    }
}

