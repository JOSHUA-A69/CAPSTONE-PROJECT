<?php

namespace App\Http\Controllers\Adviser;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\LiturgicalSchedule;
use App\Services\ReservationNotificationService;
use App\Services\AvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReservationController extends Controller
{
    protected ReservationNotificationService $notificationService;
    protected AvailabilityService $availabilityService;

    public function __construct(ReservationNotificationService $notificationService, AvailabilityService $availabilityService)
    {
        $this->middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':adviser']);
        $this->notificationService = $notificationService;
        $this->availabilityService = $availabilityService;
    }

    public function index(Request $request)
    {
        // Get organizations where this adviser is assigned
        $adviserOrgs = Auth::user()->organizations->pluck('org_id');

        // Start building the query
        $query = Reservation::with(['user', 'service', 'venue', 'organization'])
            ->whereIn('org_id', $adviserOrgs);

        // Apply filters
        if ($request->has('filter')) {
            $filter = $request->input('filter');
            switch ($filter) {
                case 'pending':
                    $query->where('status', 'pending');
                    break;
                case 'adviser_approved':
                    $query->where('status', 'adviser_approved');
                    break;
                case 'upcoming':
                    $query->whereIn('status', ['admin_approved', 'approved'])
                          ->where('schedule_date', '>=', now());
                    break;
                case 'unnoticed':
                    $query->unnoticedByAdviser();
                    break;
                default:
                    $query->whereIn('status', ['pending', 'adviser_approved', 'admin_approved', 'approved', 'rejected']);
                    break;
            }
        } else {
            // Default view
            $query->whereIn('status', ['pending', 'adviser_approved', 'admin_approved', 'approved', 'rejected']);
        }

        // Get reservations linked to those organizations
        $reservations = $query->orderByRaw("CASE
                WHEN status = 'pending' THEN 1
                WHEN status = 'adviser_approved' THEN 2
                ELSE 3
            END")
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        // Get count of unnoticed requests (>24 hours old)
        $unnoticedCount = Reservation::whereIn('org_id', $adviserOrgs)
            ->unnoticedByAdviser()
            ->count();

        return view('adviser.reservations.index', compact('reservations', 'unnoticedCount'));
    }

    public function show($reservation_id)
    {
        // Get organizations where this adviser is assigned
        $adviserOrgs = Auth::user()->organizations->pluck('org_id');

        $reservation = Reservation::with(['user', 'service', 'venue', 'organization', 'history'])
            ->whereIn('org_id', $adviserOrgs)
            ->findOrFail($reservation_id);

        return view('adviser.reservations.show', compact('reservation'));
    }

    public function approve(Request $request, $reservation_id)
    {
        $reservation = Reservation::with(['organizations', 'priests'])->findOrFail($reservation_id);
        $adviser = Auth::user();

        // Get the adviser's organization IDs
        $adviserOrgIds = $adviser->organizations->pluck('org_id');

        // Check if adviser is associated with any organization in this reservation
        $reservationOrgIds = $reservation->organizations->pluck('org_id');
        
        // Support both multi-org and legacy single org_id
        if ($reservationOrgIds->isEmpty()) {
            $reservationOrgIds = collect([$reservation->org_id]);
        }

        $matchingOrgId = $adviserOrgIds->intersect($reservationOrgIds)->first();
        
        if (!$matchingOrgId) {
            abort(403, 'You are not the adviser for any organization in this reservation.');
        }

        // Allow approval if pending (waiting for advisers)
        if (!in_array($reservation->status, ['pending'])) {
            return Redirect::back()
                ->with('error', 'This reservation cannot be approved at this time. Current status: ' . $reservation->status);
        }

        // If a priest is already assigned, validate their availability hasn't changed
        if ($reservation->officiant_id) {
            $availabilityCheck = $this->availabilityService->isPriestAvailable(
                $reservation->officiant_id,
                $reservation->schedule_date,
                $reservation_id
            );

            if (!$availabilityCheck['available']) {
                return Redirect::back()
                    ->with('error', 'Cannot approve: ' . $availabilityCheck['message']);
            }
        }

        // If a venue is assigned, validate its availability hasn't changed
        if ($reservation->venue_id) {
            $venueAvailabilityCheck = $this->availabilityService->isVenueAvailable(
                $reservation->venue_id,
                $reservation->schedule_date,
                $reservation_id
            );

            if (!$venueAvailabilityCheck['available']) {
                return Redirect::back()
                    ->with('error', 'Cannot approve: ' . $venueAvailabilityCheck['message']);
            }
        }

        $remarks = $request->input('remarks') ?? 'Approved by organization adviser';

        DB::beginTransaction();
        try {
            // Update the pivot table for this adviser's organization
            if ($reservation->organizations->isNotEmpty()) {
                $reservation->organizations()->updateExistingPivot($matchingOrgId, [
                    'approval_status' => 'approved',
                    'responded_by' => $adviser->id,
                    'responded_at' => now(),
                ]);
            }

            // Create history entry for this adviser's approval
            $reservation->history()->create([
                'performed_by' => $adviser->id,
                'action' => 'adviser_approved',
                'remarks' => $remarks . ' (Organization: ' . ($adviser->organizations->where('org_id', $matchingOrgId)->first()->org_name ?? 'Unknown') . ')',
                'performed_at' => now(),
            ]);

            // Reload organizations to check updated status
            $reservation->load('organizations');

            // Check if ALL advisers have now approved
            $allApproved = $reservation->allAdvisersApproved();

            if ($allApproved) {
                // All advisers approved - move to next status
                $reservation->update([
                    'status' => 'adviser_approved',
                    'adviser_responded_at' => now(),
                    'admin_notified_at' => now(),
                ]);

                // Notify priests about the reservation (they need to confirm)
                $this->notifyPriestsForConfirmation($reservation);

                // Send notifications to requestor and CREaM admin/staff
                $this->notificationService->notifyAdviserApproved($reservation, $remarks);

                $message = 'All advisers have approved. Reservation is now awaiting priest confirmation.';
            } else {
                // Still waiting for other advisers
                $pending = $reservation->pendingAdviserCount();
                $approved = $reservation->approvedAdviserCount();
                $total = $reservation->organizations->count();

                $message = "Your approval recorded ({$approved}/{$total}). Waiting for {$pending} more adviser(s) to approve.";
            }

            DB::commit();

            return Redirect::back()
                ->with('status', 'reservation-approved')
                ->with('message', $message);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Adviser approval failed: ' . $e->getMessage());
            return Redirect::back()->with('error', 'Failed to approve reservation: ' . $e->getMessage());
        }
    }

    /**
     * Notify priests that they need to confirm their availability
     */
    protected function notifyPriestsForConfirmation(Reservation $reservation): void
    {
        // If using multiple priests from pivot table
        if ($reservation->priests->isNotEmpty()) {
            foreach ($reservation->priests as $priest) {
                // Update notification status in pivot
                $reservation->priests()->updateExistingPivot($priest->id, [
                    'notified' => true,
                    'notified_at' => now(),
                ]);

                // Create in-app notification
                try {
                    $this->notificationService->notifyPriestAssigned($reservation, $priest->id);
                } catch (\Throwable $e) {
                    Log::warning('Failed to notify priest ' . $priest->id . ': ' . $e->getMessage());
                }
            }
        } elseif ($reservation->officiant_id) {
            // Legacy single priest
            try {
                $this->notificationService->notifyPriestAssigned($reservation);
            } catch (\Throwable $e) {
                Log::warning('Failed to notify officiant: ' . $e->getMessage());
            }
        }
    }

    public function reject(Request $request, $reservation_id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $reservation = Reservation::with('organizations')->findOrFail($reservation_id);
        $adviser = Auth::user();

        // Get the adviser's organization IDs
        $adviserOrgIds = $adviser->organizations->pluck('org_id');

        // Check if adviser is associated with any organization in this reservation
        $reservationOrgIds = $reservation->organizations->pluck('org_id');
        
        // Support both multi-org and legacy single org_id
        if ($reservationOrgIds->isEmpty()) {
            $reservationOrgIds = collect([$reservation->org_id]);
        }

        $matchingOrgId = $adviserOrgIds->intersect($reservationOrgIds)->first();
        
        if (!$matchingOrgId) {
            abort(403, 'You are not the adviser for any organization in this reservation.');
        }

        // Allow rejection if not already rejected or cancelled
        if (in_array($reservation->status, ['rejected', 'cancelled'])) {
            return Redirect::back()
                ->with('error', 'This reservation has already been rejected or cancelled.');
        }

        $reason = $request->input('reason');
        $orgName = $adviser->organizations->where('org_id', $matchingOrgId)->first()->org_name ?? 'Unknown';

        DB::beginTransaction();
        try {
            // Update the pivot table for this adviser's organization
            if ($reservation->organizations->isNotEmpty()) {
                $reservation->organizations()->updateExistingPivot($matchingOrgId, [
                    'approval_status' => 'rejected',
                    'rejection_reason' => $reason,
                    'responded_by' => $adviser->id,
                    'responded_at' => now(),
                ]);
            }

            // Create history entry
            $reservation->history()->create([
                'performed_by' => $adviser->id,
                'action' => 'adviser_rejected',
                'remarks' => "Rejected by adviser ({$orgName}): {$reason}",
                'performed_at' => now(),
            ]);

            // Notify requestor and staff about the rejection (even partial rejection)
            $this->notificationService->notifyAdviserRejected($reservation, $reason, $orgName);

            // Check if this is a single-org reservation or if all orgs have now responded
            $totalOrgs = $reservation->organizations->count();
            
            if ($totalOrgs <= 1) {
                // Single org - reject the whole reservation
                $reservation->update([
                    'status' => 'rejected',
                    'adviser_responded_at' => now(),
                ]);
                $message = 'Reservation has been rejected.';
            } else {
                // Multi-org - notify but don't reject entire reservation yet
                // The reservation stays in pending status until all required approvals are met
                // Other advisers can still approve
                $message = "Your rejection has been recorded and the requestor/staff have been notified. Other advisers may still approve for their organizations.";
            }

            DB::commit();

            return Redirect::back()
                ->with('status', 'reservation-rejected')
                ->with('message', $message);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Adviser rejection failed: ' . $e->getMessage());
            return Redirect::back()->with('error', 'Failed to reject reservation: ' . $e->getMessage());
        }
    }

    public function cancelApproval(Request $request, $reservation_id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $reservation = Reservation::findOrFail($reservation_id);

        // Verify this adviser is assigned to the reservation's organization
        if (!Auth::user()->organizations->pluck('org_id')->contains($reservation->org_id)) {
            abort(403, 'You are not the adviser for this organization.');
        }

        // Only allow cancellation of approved reservations
        if (!in_array($reservation->status, ['adviser_approved', 'admin_approved', 'approved'])) {
            return Redirect::back()
                ->with('error', 'Only approved reservations can have their approval cancelled.');
        }

        // Check if the mass is at least 6 days away
        $massDate = \Carbon\Carbon::parse($reservation->schedule_date);
        $daysUntilMass = now()->diffInDays($massDate, false);

        if ($daysUntilMass < 6) {
            return Redirect::back()
                ->with('error', 'Cannot cancel approval for reservations less than 6 days before the scheduled date.');
        }

        $reason = $request->input('reason');

        // Update reservation status to pending
        $reservation->update([
            'status' => 'pending',
            'adviser_responded_at' => null,
            'admin_notified_at' => null,
        ]);

        $reservation->history()->create([
            'performed_by' => Auth::id(),
            'action' => \App\Models\ReservationHistory::ACTION_STATUS_UPDATED,
            'remarks' => 'Approval cancelled by adviser: ' . $reason,
            'performed_at' => now(),
        ]);

        // Send notifications to requestor and CREaM staff
        $this->notificationService->notifyApprovalCancelled($reservation, $reason);

        return Redirect::back()
            ->with('status', 'approval-cancelled')
            ->with('message', 'Approval cancelled successfully. The requestor and staff have been notified.');
    }

    /**
     * Allow adviser to cancel a reservation for their organization.
     */
    public function cancel(Request $request, $reservation_id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $reservation = Reservation::with(['organizations', 'user'])->findOrFail($reservation_id);
        $adviser = Auth::user();

        // Verify adviser is associated with reservation's organizations
        $adviserOrgIds = $adviser->organizations->pluck('org_id');
        $reservationOrgIds = $reservation->organizations->pluck('org_id');
        if ($reservationOrgIds->isEmpty()) {
            $reservationOrgIds = collect([$reservation->org_id]);
        }
        $matchingOrgId = $adviserOrgIds->intersect($reservationOrgIds)->first();
        if (!$matchingOrgId) {
            abort(403, 'You are not the adviser for any organization in this reservation.');
        }

        // Disallow double-cancel
        if ($reservation->status === 'cancelled') {
            return Redirect::back()->with('error', 'Reservation is already cancelled.');
        }

        $reason = $request->input('reason');

        DB::beginTransaction();
        try {
            // Update reservation status
            $reservation->update([
                'status' => 'cancelled',
            ]);

            // Record history
            $orgName = $adviser->organizations->where('org_id', $matchingOrgId)->first()->org_name ?? 'Unknown';
            $reservation->history()->create([
                'performed_by' => $adviser->id,
                'action' => \App\Models\ReservationHistory::ACTION_CANCELLED,
                'remarks' => "Cancelled by adviser ({$orgName}): {$reason}",
                'performed_at' => now(),
            ]);

            // Notify stakeholders
            try {
                $this->notificationService->notifyReservationCancelled($reservation, $reason, actor: 'adviser');
            } catch (\Throwable $e) {
                Log::warning('Failed to notify on adviser cancel: ' . $e->getMessage());
            }

            DB::commit();
            return Redirect::back()
                ->with('status', 'reservation-cancelled')
                ->with('message', 'Reservation cancelled. The requestor and staff/admin have been notified.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Adviser cancellation failed: ' . $e->getMessage());
            return Redirect::back()->with('error', 'Failed to cancel reservation: ' . $e->getMessage());
        }
    }

    /**
     * Adviser calendar: upcoming reservations for adviser's organizations + staff-plotted schedules
     */
    public function calendar()
    {
        $adviserOrgs = Auth::user()->organizations->pluck('org_id');

            $reservations = Reservation::with([
                    'service:service_id,service_name,service_category',
                    'venue:venue_id,name',
                    'officiant:id,first_name,middle_name,last_name'
                ])
            ->whereIn('org_id', $adviserOrgs)
            ->whereDate('schedule_date', '>=', now()->toDateString())
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->orderBy('schedule_date')
                ->get([
                    'reservation_id',
                    'service_id',
                    'venue_id',
                    'custom_venue_name',
                    'schedule_date',
                    DB::raw('TIME(schedule_date) as schedule_time'),
                    'status',
                    'participants_count',
                    'activity_name',
                    'purpose',
                    'theme',
                    'commentator',
                    'readers',
                    'psalmist',
                    'prayer_leader',
                    'details',
                    'priest_selection_type',
                    'external_priest_name',
                    'external_priest_contact',
                    'officiant_id'
                ]);

        // Show all upcoming staff-plotted schedules to adviser
            $schedules = LiturgicalSchedule::with([
                    'priest:id,first_name,middle_name,last_name',
                    'venue:venue_id,name'
                ])
            ->upcoming()
                ->get([
                    'schedule_id',
                    'title',
                    'event_type',
                    'mass_subtype',
                    'schedule_date',
                    'start_time',
                    'end_time',
                    'location',
                    'venue_id',
                    'priest_id',
                    'external_priest_name',
                    'external_priest_contact',
                    'is_public',
                    'description'
                ]);

        // Upcoming organization bookings for adviser's assigned organizations
        $orgBookings = \App\Models\OrganizationBookingRequest::with([
                'organization:org_id,org_name',
                'requestor:id,first_name,last_name'
            ])
            ->whereIn('organization_id', $adviserOrgs)
            ->whereDate('requested_date', '>=', now()->toDateString())
            ->whereIn('status', ['pending', 'approved'])
            ->orderBy('requested_date')
            ->get([
                'id',
                'organization_id',
                'requestor_id',
                'activity_name',
                'purpose',
                'requested_date',
                'requested_venue',
                'estimated_participants',
                'special_requirements',
                'status'
            ]);

        return view('adviser.reservations.calendar', compact('reservations', 'schedules', 'orgBookings'));
    }
}

