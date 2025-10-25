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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class ReservationController extends Controller
{
    protected ReservationNotificationService $notificationService;

    public function __construct(
        ReservationNotificationService $notificationService
    ) {
        $this->middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':requestor']);
        $this->notificationService = $notificationService;
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
        $priests = User::where('role', 'priest')->where('status','active')->orderBy('first_name')->get();

        return view('requestor.reservations.create', compact('services', 'venues', 'organizations', 'priests'));
    }

    public function store(ReservationRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();

        // Initial status per process: pending adviser approval
        $data['status'] = 'pending';

        $data['adviser_notified_at'] = now(); // Mark adviser as notified immediately

        // Handle custom venue
        if ($request->venue_id === 'custom') {
            // Set venue_id to null and store custom venue name
            $data['venue_id'] = null;
            $data['custom_venue_name'] = $request->custom_venue_name;
        } else {
            // Regular venue selected, ensure custom_venue_name is null
            $data['custom_venue_name'] = null;
        }

    // Enforce policy: requestor can only indicate a preference; do not assign officiant here
    unset($data['officiant_id']);

    $reservation = Reservation::create($data);

        // Create history record
        $reservation->history()->create([
            'performed_by' => Auth::id(),
            'action' => 'submitted',
            'remarks' => 'Reservation request submitted by requestor' .
                        (!empty($data['preferred_officiant_id']) ? ' - Preferred priest selected' : ''),
            'performed_at' => now(),
        ]);

        // Send notifications to requestor and organization adviser
        $this->notificationService->notifyReservationSubmitted($reservation);

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

        // Validate 7-day notice requirement
        $daysUntilEvent = now()->diffInDays($reservation->schedule_date, false);
        if ($daysUntilEvent < 7) {
            return Redirect::back()
                ->with('error', 'Cancellation is only allowed at least 7 days before the scheduled date. Your event is in ' . $daysUntilEvent . ' day(s).');
        }
        
        // Immediately cancel the reservation (simplified flow)
        $reservation->update(['status' => 'cancelled']);

        // Log history with reason
        $reservation->history()->create([
            'performed_by' => Auth::id(),
            'action' => 'cancelled',
            'remarks' => 'Requestor cancelled reservation. Reason: ' . $request->input('reason'),
            'performed_at' => now(),
        ]);

        // Notify all concerned parties
        $this->notificationService->notifyCancellation(
            $reservation,
            $request->input('reason'),
            Auth::user()->full_name
        );

        return Redirect::route('requestor.reservations.index')
            ->with('status', 'cancellation-completed')
            ->with('message', 'Your reservation has been cancelled.');
    }

    // Confirmation step removed: token-based show/confirm/decline no longer used
}

