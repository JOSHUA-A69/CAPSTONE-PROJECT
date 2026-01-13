<?php

namespace App\Http\Controllers\Priest;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Services\ReservationNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use App\Support\Notifications as NotificationHelper;

/**
 * Priest Reservation Controller
 *
 * Allows priests to view their assigned services and confirm/decline availability
 */
class ReservationController extends Controller
{
    protected ReservationNotificationService $notificationService;

    public function __construct(ReservationNotificationService $notificationService)
    {
        $this->middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':priest']);
        $this->notificationService = $notificationService;
    }

    /**
     * Show all reservations assigned to this priest
     */
    public function index(Request $request)
    {
        $status = $request->input('status');
        $timeFilter = $request->input('time', 'upcoming'); // upcoming or past

        $query = Reservation::with(['user', 'service', 'venue', 'organization'])
            ->forPriest(Auth::id());

        // Status filter - special handling for pending_priest_confirmation
        if ($status === 'pending_priest_confirmation') {
            // Show all reservations awaiting priest confirmation (admin_approved with pending confirmation)
            $query->awaitingPriestConfirmation();
        } elseif ($status) {
            $query->where('status', $status);
        }

        // Time filter
        if ($timeFilter === 'upcoming') {
            $query->upcoming();
        } elseif ($timeFilter === 'past') {
            $query->past();
        }

        $reservations = $query->orderBy('schedule_date', $timeFilter === 'past' ? 'desc' : 'asc')
            ->paginate(20)
            ->appends($request->only('status', 'time'));

        // Get counts for dashboard
        $pendingConfirmationCount = Reservation::forPriest(Auth::id())
            ->awaitingPriestConfirmation()
            ->count();

        $upcomingCount = Reservation::forPriest(Auth::id())
            ->upcoming()
            ->where('priest_confirmation', 'confirmed')
            ->count();

        $declinedCount = \App\Models\PriestDecline::where('priest_id', Auth::id())
            ->count();

        return view('priest.reservations.index', compact('reservations', 'status', 'timeFilter', 'pendingConfirmationCount', 'upcomingCount', 'declinedCount'));
    }

    /**
     * Show reservation details
     */
    public function show($reservation_id)
    {
        $priestId = Auth::id();

        // Allow priests to view reservations where they are:
        // 1. Currently assigned as officiant (officiant_id = priest_id) OR via many-to-many priests
        // 2. Previously declined (has a decline record)
        // 3. Received notification for this reservation
        $reservation = Reservation::with([
            'user',
            'service',
            'venue',
            'organization.adviser',
            'history.performedBy',
            'priests'
        ])
            ->where(function ($query) use ($priestId, $reservation_id) {
                $query->where('officiant_id', $priestId)
                    ->orWhereHas('priests', function ($priestQuery) use ($priestId) {
                        $priestQuery->where('users.id', $priestId);
                    })
                    ->orWhereHas('declines', function ($q) use ($priestId) {
                        $q->where('priest_id', $priestId);
                    })
                    ->orWhereExists(function ($q) use ($priestId, $reservation_id) {
                        $q->select(DB::raw(1))
                            ->from('notifications')
                            ->where('reservation_id', $reservation_id)
                            ->where('user_id', $priestId)
                            ->where('type', 'Assignment');
                    });
            })
            ->findOrFail($reservation_id);

        // Build a lightweight list of available priests for optional replacement
        // Exclude current priest and anyone conflicting at this exact schedule_date
        try {
            $conflictingPriestIds = \App\Models\Reservation::where('schedule_date', $reservation->schedule_date)
                ->whereNotIn('status', ['cancelled', 'rejected'])
                ->pluck('officiant_id')
                ->filter()
                ->unique()
                ->values()
                ->all();

            $availablePriests = \App\Models\User::where('role', 'priest')
                ->when(\Illuminate\Support\Facades\Schema::hasColumn('users', 'status'), function ($q) {
                    $q->where('status', 'active');
                })
                ->where('id', '!=', $priestId)
                ->whereNotIn('id', $conflictingPriestIds)
                ->orderBy('first_name')
                ->get(['id', 'first_name', 'last_name']);
        } catch (\Throwable $e) {
            // Fallback in case schema/columns differ in some environments
            $availablePriests = collect();
        }

        return view('priest.reservations.show', compact('reservation', 'availablePriests'));
    }

    /**
     * Confirm availability for assigned service
     */
    public function confirm(Request $request, $reservation_id)
    {
        $priestId = Auth::id();

        // Find reservation where priest is assigned (via officiant_id OR priests relationship)
        $reservation = Reservation::with('priests')
            ->where(function($query) use ($priestId) {
                $query->where('officiant_id', $priestId)
                      ->orWhereHas('priests', function($q) use ($priestId) {
                          $q->where('users.id', $priestId);
                      });
            })
            ->findOrFail($reservation_id);

        // Allow confirmation when awaiting priest confirmation or approved by adviser/admin
        if (!in_array($reservation->status, ['adviser_approved', 'admin_approved', 'pending_priest_confirmation'])) {
            return Redirect::back()
                ->with('error', 'This reservation is not ready for confirmation.');
        }

        // Check if this priest has already confirmed (via pivot)
        $priestInPivot = $reservation->priests->firstWhere('id', $priestId);
        if ($priestInPivot && $priestInPivot->pivot->confirmation_status === 'confirmed') {
            return Redirect::back()
                ->with('error', 'You have already confirmed this reservation.');
        }

        // Legacy single-priest check
        if ($reservation->officiant_id === $priestId && $reservation->priest_confirmation === 'confirmed') {
            return Redirect::back()
                ->with('error', 'You have already confirmed this reservation.');
        }

        DB::beginTransaction();
        try {
            // Update pivot table for this priest
            $pivotExists = DB::table('reservation_priest')
                ->where('reservation_id', $reservation->reservation_id)
                ->where('priest_id', $priestId)
                ->exists();

            if ($pivotExists) {
                DB::table('reservation_priest')
                    ->where('reservation_id', $reservation->reservation_id)
                    ->where('priest_id', $priestId)
                    ->update([
                        'confirmation_status' => 'confirmed',
                        'responded_at' => now(),
                        'updated_at' => now(),
                    ]);
            } else {
                // Create pivot row if it doesn't exist (e.g., admin assignment flow)
                DB::table('reservation_priest')->insert([
                    'reservation_id' => $reservation->reservation_id,
                    'priest_id' => $priestId,
                    'confirmation_status' => 'confirmed',
                    'notified' => true,
                    'notified_at' => now(),
                    'responded_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Create history
            $remarks = $request->input('remarks', 'Priest confirmed availability');
            $reservation->history()->create([
                'performed_by' => $priestId,
                'action' => 'priest_confirmed',
                'remarks' => $remarks,
                'performed_at' => now(),
            ]);

            // Perform a robust check: properly reload relations + direct DB check
            $reservation->refresh(); 
            
            // Check if there are any unconfirmed priests for this reservation
            $hasUnconfirmed = DB::table('reservation_priest')
                ->where('reservation_id', $reservation->reservation_id)
                ->where('confirmation_status', '!=', 'confirmed')
                ->exists();

            if (!$hasUnconfirmed) {
                // All priests confirmed - update confirmation status
                // Status moves to 'admin_approved' so Admin can give Final Approval
                $reservation->update([
                    'priest_confirmation' => 'confirmed',
                    'priest_confirmed_at' => now(),
                    'status' => 'admin_approved',
                ]);

                // Notify admin that all priests confirmed and reservation is ready for final approval
                try {
                    $this->notificationService->notifyAllPriestsConfirmed($reservation);
                } catch (\Exception $e) {
                     // Log but don't fail the transaction
                     \Log::warning('Failed to notify admin of all priests confirmed: ' . $e->getMessage());
                }

                $message = "You have confirmed your availability. The Admin will now finalize the reservation.";
            } else {
                // Still waiting for other priests
                // Update legacy field for this priest if they're the officiant
                if ($reservation->officiant_id === $priestId) {
                    $reservation->update([
                        'priest_confirmation' => 'confirmed',
                        'priest_confirmed_at' => now(),
                    ]);
                }

                $pending = $reservation->pendingPriestCount();
                $message = "Your confirmation recorded ({$confirmedCount}/{$totalPriests}). Waiting for {$pending} more priest(s) to confirm.";
            }

            // Send notification to admin/staff and requestor about this priest's confirmation
            $this->notificationService->notifyPriestConfirmed($reservation, $priestId);

            DB::commit();

            $serviceName = $reservation->activity_name ?? $reservation->service->service_name;
            $serviceDate = $reservation->schedule_date->format('F d, Y \a\t g:i A');
            
            return Redirect::route('priest.reservations.index')
                ->with('status', 'reservation-confirmed')
                ->with('message', $message);

        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Priest confirmation failed: ' . $e->getMessage());
            return Redirect::back()->with('error', 'Failed to confirm: ' . $e->getMessage());
        }
    }

    /**
     * Decline assigned service (works for both unconfirmed and confirmed reservations)
     */
    public function decline(Request $request, $reservation_id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
            'replacement_priest_id' => 'nullable|integer|exists:users,id',
        ]);

        $priestId = Auth::id();

        // Find reservation where priest is assigned (via officiant_id OR priests relationship)
        $reservation = Reservation::where(function($query) use ($priestId) {
                $query->where('officiant_id', $priestId)
                      ->orWhereHas('priests', function($q) use ($priestId) {
                          $q->where('users.id', $priestId);
                      });
            })
            ->findOrFail($reservation_id);

        // Prevent duplicate declines - check if already declined
        if ($reservation->priest_confirmation === 'declined') {
            return Redirect::back()
                ->with('error', 'You have already declined this reservation.');
        }

        // Check if this is a cancellation of already confirmed reservation
        $isCancellation = ($reservation->priest_confirmation === 'confirmed');

        // Allow decline for: pending_priest_confirmation, adviser_approved, admin_approved, OR approved (confirmed)
        if (!in_array($reservation->status, ['pending_priest_confirmation', 'adviser_approved', 'admin_approved', 'approved'])) {
            return Redirect::back()
                ->with('error', 'This reservation cannot be declined at this stage.');
        }

    $reason = $request->input('reason');

        // Store the priest ID before clearing officiant_id
        $priestId = Auth::id();

        // Store decline record with reservation details
        \App\Models\PriestDecline::create([
            'reservation_id' => $reservation->reservation_id,
            'priest_id' => $priestId,
            'reason' => $reason,
            'declined_at' => now(),
            'reservation_activity_name' => $reservation->activity_name ?? $reservation->service->service_name,
            'reservation_schedule_date' => $reservation->schedule_date,
            'reservation_venue' => $reservation->custom_venue_name ?? $reservation->venue->name ?? 'N/A',
        ]);

        // Default transition after decline
        $updatePayload = [
            'priest_confirmation' => 'declined',
            'priest_confirmed_at' => now(),
            'status' => 'pending_priest_reassignment',
            'officiant_id' => null,
        ];

        // Update reservation_priest pivot for the declining priest
        try {
            $exists = DB::table('reservation_priest')
                ->where('reservation_id', $reservation->reservation_id)
                ->where('priest_id', $priestId)
                ->exists();

            if ($exists) {
                DB::table('reservation_priest')
                    ->where('reservation_id', $reservation->reservation_id)
                    ->where('priest_id', $priestId)
                    ->update([
                        'confirmation_status' => 'declined',
                        'decline_reason' => $reason,
                        'responded_at' => now(),
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('reservation_priest')->insert([
                    'reservation_id' => $reservation->reservation_id,
                    'priest_id' => $priestId,
                    'confirmation_status' => 'declined',
                    'decline_reason' => $reason,
                    'notified' => true,
                    'notified_at' => now(),
                    'responded_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            \Log::warning('Failed to update reservation_priest pivot on decline: ' . $e->getMessage());
        }

        // Create history
        $historyAction = $isCancellation ? 'priest_cancelled_confirmation' : 'priest_declined';
        $historyRemarks = $isCancellation
            ? 'Priest cancelled their previously confirmed reservation. Reason: ' . $reason
            : 'Priest declined availability. Reason: ' . $reason;

        $reservation->history()->create([
            'performed_by' => $priestId,
            'action' => $historyAction,
            'remarks' => $historyRemarks,
            'performed_at' => now(),
        ]);

        // Optional: Priest can assign a replacement immediately
        $replacementId = $request->input('replacement_priest_id');
        if ($replacementId) {
            // Prevent assigning external/internal mismatch
            if ($reservation->priest_selection_type === 'external') {
                return Redirect::back()->with('error', 'This reservation uses an external priest. You cannot assign an internal priest.');
            }

            // Validate selected user is a different priest
            $replacement = \App\Models\User::where('id', $replacementId)->where('role', 'priest')->first();
            if (! $replacement || $replacement->id == $priestId) {
                return Redirect::back()->with('error', 'Invalid replacement priest selected.');
            }

            // Check for scheduling conflicts for the replacement
            $conflict = Reservation::where('officiant_id', $replacement->id)
                ->where('schedule_date', $reservation->schedule_date)
                ->whereNotIn('status', ['cancelled', 'rejected'])
                ->where('reservation_id', '!=', $reservation_id)
                ->exists();

            if ($conflict) {
                return Redirect::back()->with('error', 'Selected replacement priest is not available at this date and time.');
            }

            // Apply reassignment instantly; keep decline record but move to awaiting new priest confirmation
            $updatePayload['officiant_id'] = $replacement->id;
            $updatePayload['status'] = 'admin_approved'; // unified awaiting priest confirmation state
            $updatePayload['priest_confirmation'] = 'pending';
            $updatePayload['priest_notified_at'] = now();

            // History for reassignment (performed by the cancelling priest)
            $reservation->history()->create([
                'performed_by' => $priestId,
                'action' => 'priest_reassigned',
                'remarks' => 'Reassigned by priest to: ' . ($replacement->first_name . ' ' . $replacement->last_name),
                'performed_at' => now(),
            ]);

            // Ensure pivot row exists/updated for the replacement priest
            try {
                $existsReplacement = DB::table('reservation_priest')
                    ->where('reservation_id', $reservation->reservation_id)
                    ->where('priest_id', $replacement->id)
                    ->exists();

                if ($existsReplacement) {
                    DB::table('reservation_priest')
                        ->where('reservation_id', $reservation->reservation_id)
                        ->where('priest_id', $replacement->id)
                        ->update([
                            'confirmation_status' => 'pending',
                            'decline_reason' => null,
                            'notified' => true,
                            'notified_at' => now(),
                            'responded_at' => null,
                            'updated_at' => now(),
                        ]);
                } else {
                    DB::table('reservation_priest')->insert([
                        'reservation_id' => $reservation->reservation_id,
                        'priest_id' => $replacement->id,
                        'confirmation_status' => 'pending',
                        'decline_reason' => null,
                        'notified' => true,
                        'notified_at' => now(),
                        'responded_at' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            } catch (\Throwable $e) {
                \Log::warning('Failed to upsert reservation_priest pivot for replacement: ' . $e->getMessage());
            }
        }

        // Persist updates
        $reservation->update($updatePayload);

        // Send notification to admin/staff and possibly the replacement priest
        if ($replacementId ?? false) {
            // Notify the newly assigned priest and requestor
            $this->notificationService->notifyPriestAssigned($reservation->fresh());
        }

        // Always notify admins about the decline/cancellation itself
        if ($isCancellation) {
            $this->notificationService->notifyPriestCancelledConfirmation($reservation, $reason, $priestId);
        } else {
            $this->notificationService->notifyPriestDeclined($reservation, $reason, $priestId);
        }

        $serviceName = $reservation->activity_name ?? $reservation->service->service_name;
        $serviceDate = $reservation->schedule_date->format('F d, Y \a\t g:i A');
        
        $message = $isCancellation
            ? (($replacementId ?? false)
                ? "Your confirmation for '{$serviceName}' on {$serviceDate} has been cancelled and reassigned to another priest. They have been notified to confirm their availability."
                : "Your confirmation for '{$serviceName}' on {$serviceDate} has been cancelled. Administrators have been notified to assign another priest urgently.")
            : (($replacementId ?? false)
                ? "You have declined '{$serviceName}' on {$serviceDate} and suggested a replacement priest. They have been notified to confirm their availability."
                : "You have declined '{$serviceName}' on {$serviceDate}. Administrators have been notified to assign another priest.");

        return Redirect::route('priest.reservations.index')
            ->with('status', 'reservation-declined')
            ->with('message', $message);
    }

    /**
     * Undo decline - Priest wants to accept the assignment they previously declined
     */
    public function undecline($reservation_id)
    {
        $priestId = Auth::id();

        // Find the reservation
        $reservation = Reservation::findOrFail($reservation_id);

        // Find the decline record
        $decline = \App\Models\PriestDecline::where('reservation_id', $reservation_id)
            ->where('priest_id', $priestId)
            ->latest('declined_at')
            ->first();

        if (!$decline) {
            return Redirect::back()->with('error', 'Decline record not found.');
        }

        // Check if reservation was already reassigned to another priest
        if ($reservation->officiant_id && $reservation->officiant_id != $priestId) {
            return Redirect::route('priest.reservations.declined')
                ->with('error', 'This reservation has already been reassigned to another priest. You cannot undo your decline.');
        }

        // Check if reservation status allows undecline
        if (!in_array($reservation->status, ['pending_priest_reassignment', 'adviser_approved', 'admin_approved'])) {
            return Redirect::route('priest.reservations.declined')
                ->with('error', 'This reservation is no longer available for reassignment (Status: ' . $reservation->status . ').');
        }

        // Restore the priest assignment
        $reservation->update([
            'officiant_id' => $priestId,
            'priest_confirmation' => null, // Reset to allow confirmation
            'priest_confirmed_at' => null,
            'status' => 'pending_priest_confirmation', // Back to awaiting priest confirmation
        ]);

        // Count total declines by this priest for this reservation (for admin awareness)
        $totalDeclines = \App\Models\PriestDecline::where('reservation_id', $reservation_id)
            ->where('priest_id', $priestId)
            ->count();

        // Delete the decline record
        $decline->delete();

        // Create history entry with decline count
        $historyRemarks = 'Priest undid their decline and is now available for this assignment.';
        if ($totalDeclines > 1) {
            $historyRemarks .= ' (This priest has declined this reservation ' . $totalDeclines . ' time(s))';
        }

        $reservation->history()->create([
            'performed_by' => $priestId,
            'action' => 'priest_reassigned',
            'remarks' => $historyRemarks,
            'performed_at' => now(),
        ]);

        // Create notifications for ALL admins and staff
        $admins = \App\Models\User::whereIn('role', ['admin', 'staff'])->get();
        $priestName = Auth::user()->first_name . ' ' . Auth::user()->last_name;

        // Add warning if priest has changed mind multiple times
        $indecisionWarning = '';
        if ($totalDeclines > 1) {
            $indecisionWarning = ' ⚠️ (Changed mind ' . $totalDeclines . ' times)';
        }

        foreach ($admins as $admin) {
            NotificationHelper::make([
                'user_id' => $admin->id,
                'reservation_id' => $reservation->reservation_id,
                'message' => '<strong>' . $priestName . '</strong> restored their previously declined reservation' . $indecisionWarning,
                'type' => NotificationHelper::TYPE_UPDATE,
                'sent_at' => now(),
                'data' => [
                    'priest_name' => $priestName,
                    'priest_id' => $priestId,
                    'service_name' => $reservation->service->service_name,
                    'schedule_date' => $reservation->schedule_date->format('Y-m-d H:i:s'),
                    'requestor_name' => $reservation->user->first_name . ' ' . $reservation->user->last_name,
                    'venue' => $reservation->custom_venue_name ?? $reservation->venue->name ?? 'N/A',
                    'action' => 'undecline',
                    'decline_count' => $totalDeclines,
                ],
            ]);
        }

    // Send email notifications to admin/staff
    $this->notificationService->notifyPriestUndeclined($reservation, $priestId);

    return Redirect::route('priest.reservations.show', $reservation_id)
            ->with('status', 'reservation-undeclined')
            ->with('message', 'Success! You have undone your decline. Please confirm your availability for this service.');
    }

    /**
     * View declined services history
     */
    public function declined()
    {
        $declines = \App\Models\PriestDecline::where('priest_id', Auth::id())
            ->with(['reservation', 'priest'])
            ->orderBy('declined_at', 'desc')
            ->paginate(20);

        return view('priest.reservations.declined', compact('declines'));
    }

    /**
     * View priest's calendar/schedule
     */
    public function calendar()
    {
        // Upcoming services for this priest, including approved and awaiting confirmation assignments
        $reservations = Reservation::with([
                'service:service_id,service_name,service_category',
                'venue:venue_id,name',
                'organization:org_id,org_name',
                'user:id,first_name,middle_name,last_name',
                'officiant:id,first_name,middle_name,last_name'
            ])
            ->forPriest(Auth::id())
            ->where(function ($q) {
                $q->whereIn('status', ['approved', 'admin_approved', 'pending', 'pending_priest_confirmation'])
                  ->orWhere('priest_confirmation', 'confirmed');
            })
            ->whereNotIn('status', ['cancelled', 'rejected', 'pending_priest_reassignment'])
            ->where('schedule_date', '>=', now())
            ->orderBy('schedule_date', 'asc')
            ->get([
                'reservation_id',
                'service_id',
                'venue_id',
                'org_id',
                'user_id',
                'schedule_date',
                'status',
                'priest_selection_type',
                'external_priest_name',
                'external_priest_contact',
                'activity_name',
                'purpose',
                'theme',
                'participants_count',
                'officiant_id',
            ]);

        // Staff-plotted liturgical schedules assigned to this priest
        try {
            $schedules = \App\Models\LiturgicalSchedule::with([
                    'priest:id,first_name,middle_name,last_name',
                    'venue:venue_id,name'
                ])
                ->where('priest_id', Auth::id())
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
        } catch (\Throwable $e) {
            $schedules = collect();
        }

        return view('priest.reservations.calendar', compact('reservations', 'schedules'));
    }
}
