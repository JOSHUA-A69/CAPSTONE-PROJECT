<?php

namespace App\Http\Controllers\Requestor;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReservationRequest;
use App\Models\Organization;
use App\Models\Reservation;
use App\Models\Service;
use App\Models\Venue;
use App\Models\User;
use App\Models\Notification;
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

        // Include both priests and admins (admin can also serve as priest)
        $priests = User::whereIn('role', ['priest', 'admin'])
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get();

        return view('requestor.reservations.create', compact('services', 'venues', 'organizations', 'priests'));
    }

    public function store(ReservationRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();

        // Always start at adviser approval so advisers can approve/reject
        // Officiant selection may be provided but priest is not notified yet.
        $data['status'] = 'pending';
        $data['adviser_notified_at'] = now(); // Adviser is notified immediately (email + in-app)

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
            'remarks' => 'Reservation request submitted by requestor - pending adviser review',
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

    /**
     * Show form to edit reservation (request changes)
     */
    public function edit($reservation_id)
    {
        $reservation = Reservation::with(['service', 'venue', 'organization', 'officiant'])
            ->where('user_id', Auth::id())
            ->findOrFail($reservation_id);

        // Only allow editing if reservation is in certain statuses
        $editableStatuses = ['pending', 'adviser_approved', 'pending_priest_confirmation'];
        if (!in_array($reservation->status, $editableStatuses)) {
            return Redirect::back()->with('error', 'This reservation cannot be edited in its current status.');
        }

        // Get available options for dropdowns
        $services = Service::orderBy('service_name')->get();
        $venues = Venue::orderBy('name')->get();
        $organizations = Organization::with('adviser')->orderBy('org_name')->get();
        $priests = User::whereIn('role', ['priest', 'admin'])
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get();

        return view('requestor.reservations.edit', compact('reservation', 'services', 'venues', 'organizations', 'priests'));
    }

    /**
     * Submit change request for admin approval
     */
    public function update(Request $request, $reservation_id)
    {
        $reservation = Reservation::with(['service', 'venue', 'organization', 'officiant'])
            ->where('user_id', Auth::id())
            ->findOrFail($reservation_id);

        // Validate the changes
        $validated = $request->validate([
            'service_id' => ['required', 'integer', 'exists:services,service_id'],
            'venue_id' => ['required'],
            'org_id' => ['nullable', 'integer', 'exists:organizations,org_id'],
            'officiant_id' => ['required', 'integer', 'exists:users,id'],
            'schedule_date' => ['required', 'date', 'after:now'],
            'schedule_time' => ['nullable', 'date_format:H:i'],
            'activity_name' => ['required', 'string', 'max:255'],
            'theme' => ['nullable', 'string', 'max:1000'],
            'purpose' => ['nullable', 'string', 'max:150'],
            'details' => ['nullable', 'string'],
            'participants_count' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'commentator' => ['nullable', 'string', 'max:255'],
            'servers' => ['nullable', 'string', 'max:500'],
            'readers' => ['nullable', 'string', 'max:500'],
            'choir' => ['nullable', 'string', 'max:255'],
            'psalmist' => ['nullable', 'string', 'max:255'],
            'prayer_leader' => ['nullable', 'string', 'max:255'],
            'custom_venue' => ['nullable', 'string', 'max:255'],
            'notes' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        // Combine date and time
        if ($request->has('schedule_date') && $request->has('schedule_time')) {
            $validated['schedule_date'] = $request->schedule_date . ' ' . $request->schedule_time;
        }

        // Handle custom venue
        if ($request->venue_id === 'custom') {
            $validated['venue_id'] = null;
            $validated['custom_venue_name'] = $request->custom_venue;
        } else {
            $validated['custom_venue_name'] = null;
        }

        // Build changes array comparing old vs new
        $changes = [];
        $fieldLabels = [
            'service_id' => 'Service',
            'venue_id' => 'Venue',
            'custom_venue_name' => 'Custom Venue',
            'org_id' => 'Organization',
            'officiant_id' => 'Officiant/Priest',
            'schedule_date' => 'Schedule',
            'activity_name' => 'Activity Name',
            'theme' => 'Theme',
            'purpose' => 'Purpose',
            'details' => 'Details',
            'participants_count' => 'Participants Count',
            'commentator' => 'Commentator',
            'servers' => 'Servers',
            'readers' => 'Readers',
            'choir' => 'Choir',
            'psalmist' => 'Psalmist',
            'prayer_leader' => 'Prayer Leader',
        ];

        foreach ($fieldLabels as $field => $label) {
            if (isset($validated[$field]) && $reservation->$field != $validated[$field]) {
                $changes[$field] = [
                    'label' => $label,
                    'old' => $reservation->$field,
                    'new' => $validated[$field],
                ];
            }
        }

        if (empty($changes)) {
            return Redirect::back()->with('info', 'No changes detected.');
        }

        // Create change request
        $changeRequest = \App\Models\ReservationChange::create([
            'reservation_id' => $reservation->reservation_id,
            'requested_by' => Auth::id(),
            'changes_requested' => $changes,
            'requestor_notes' => $request->notes,
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        // Create history record
        $reservation->history()->create([
            'performed_by' => Auth::id(),
            'action' => 'change_requested',
            'remarks' => 'Requestor submitted change request (pending admin approval)',
            'performed_at' => now(),
        ]);

        // Notify all admins about the change request
        $admins = User::where('role', 'admin')->where('status', 'active')->get();
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'Edit Request',
                'title' => 'Reservation Change Request',
                'message' => Auth::user()->full_name . ' requested changes to reservation #' . $reservation->reservation_id,
                'reservation_id' => $reservation->reservation_id,
                'data' => json_encode([
                    'action' => 'change_requested',
                    'change_request_id' => $changeRequest->change_id,
                    'requestor_name' => Auth::user()->full_name,
                ]),
                'is_read' => false,
            ]);
        }

        return Redirect::route('requestor.reservations.show', $reservation_id)
            ->with('status', 'change-request-submitted')
            ->with('message', 'Your change request has been submitted and the admin has been notified. Changes will be applied after approval.');
    }
}


