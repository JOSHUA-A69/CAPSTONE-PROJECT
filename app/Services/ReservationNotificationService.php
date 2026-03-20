<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\User;
use App\Models\Notification;
use App\Mail\ReservationSubmitted;
use App\Mail\ReservationAdviserApproved;
use App\Mail\ReservationAdviserRejected;
use App\Mail\ReservationPriestAssigned;
use App\Mail\ReservationCancelled;
use App\Mail\ReservationPriestDeclined;
use App\Mail\ReservationUnnoticedAlert;
use App\Mail\ReservationPriestConfirmedToAdmin;
use App\Mail\ReservationAllPriestsConfirmed;
use App\Mail\ReservationPriestRestoredToAdmin;
use App\Mail\RequestorConfirmedToAdmin;
use App\Mail\PriestCancelledConfirmationToRequestor;
use App\Mail\PriestCancelledConfirmationToAdmin;
use App\Mail\RequestorPriestReassigned;
use App\Mail\ReservationFinalApprovalToAdviser;
use App\Mail\ReservationFinalApprovalToPriest;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Support\Notifications as NotificationHelper;

/**
 * ReservationNotificationService
 *
 * Handles all email and SMS notifications for the reservation workflow
 * according to the swim lane diagram process flow.
 */
class ReservationNotificationService
{
    /**
     * Send notification when requestor submits a reservation
     */
    public function notifyReservationSubmitted(Reservation $reservation): void
    {
        $priestSelectionType = $reservation->priest_selection_type ?? 'specific';

        // Email to requestor (confirmation)
        if ($reservation->user && $reservation->user->email) {
            $this->sendMailableSafely($reservation->user->email, new ReservationSubmitted($reservation), [
                'action' => 'reservation_submitted_requestor',
                'reservation_id' => $reservation->reservation_id,
                'user_id' => $reservation->user_id,
            ]);
        }

        // In-app notification to requestor (message varies by priest selection type)
        try {
            $serviceName = $reservation->service?->service_name ?? 'Unknown Service';
            $message = "Your reservation for <strong>{$serviceName}</strong> has been submitted. ";
            
            if ($priestSelectionType === 'specific') {
                $message .= "The adviser and priest have been notified. Kindly wait for their confirmations.";
            } elseif ($priestSelectionType === 'any_available') {
                $message .= "The adviser will review, and an admin will assign an available priest.";
            } elseif ($priestSelectionType === 'external') {
                $message .= "The adviser and admin will review your request with the external priest details.";
            }

            $notificationData = [
                'user_id' => $reservation->user_id,
                'reservation_id' => $reservation->reservation_id,
                'message' => $message,
                'type' => NotificationHelper::TYPE_UPDATE,
                'sent_at' => now(),
            ];
            if (Schema::hasColumn('notifications', 'data')) {
                $notificationData['data'] = json_encode([
                    'service_name' => $reservation->service?->service_name ?? 'Unknown Service',
                    'schedule_date' => optional($reservation->schedule_date)->format('Y-m-d H:i:s'),
                    'action' => 'request_submitted',
                    'priest_selection_type' => $priestSelectionType,
                ]);
            }
            NotificationHelper::make($notificationData);
        } catch (\Exception $e) {
            Log::warning('Failed to create requestor in-app notification (submitted): ' . $e->getMessage());
        }

        // Email + in-app notification to organization adviser
        if ($reservation->organization && $reservation->organization->adviser) {
            $adviser = $reservation->organization->adviser;
            Log::info('Notifying adviser: ' . $adviser->first_name . ' ' . $adviser->last_name . ' (ID: ' . $adviser->id . ') for reservation #' . $reservation->reservation_id);

            try {
                if ($adviser->email) {
                    $this->sendMailableSafely($adviser->email, new \App\Mail\ReservationSubmittedToAdviser($reservation, $adviser), [
                        'action' => 'reservation_submitted_adviser',
                        'reservation_id' => $reservation->reservation_id,
                        'user_id' => $adviser->id,
                    ]);
                    Log::info('Email sent to adviser: ' . $adviser->email);
                }
            } catch (\Throwable $e) {
                Log::warning('Failed to send adviser email on submission: '.$e->getMessage());
            }

            // In-app notification to adviser to review
            try {
                $serviceName = $reservation->service?->service_name ?? 'Unknown Service';
                $message = "New reservation request to review: <strong>{$serviceName}</strong> on " . $reservation->schedule_date->format('M d, Y h:i A');
                $notificationData = [
                    'user_id' => $adviser->id,
                    'reservation_id' => $reservation->reservation_id,
                    'message' => $message,
                    'type' => NotificationHelper::TYPE_UPDATE,
                    'sent_at' => now(),
                ];
                if (Schema::hasColumn('notifications', 'data')) {
                    $notificationData['data'] = json_encode([
                        'service_name' => $reservation->service?->service_name ?? 'Unknown Service',
                        'requestor_name' => $reservation->user ? ($reservation->user->first_name . ' ' . $reservation->user->last_name) : 'Unknown User',
                        'action' => 'adviser_review_required',
                    ]);
                }
                $notification = NotificationHelper::make($notificationData);
                Log::info('In-app notification created for adviser (ID: ' . $notification->id . ')');
            } catch (\Throwable $e) {
                Log::error('Failed to create adviser in-app notification: '.$e->getMessage());
                Log::error($e->getTraceAsString());
            }

            // SMS to adviser (optional)
            if ($adviser->phone) {
                $requestorName = $reservation->user ? ($reservation->user->first_name . ' ' . $reservation->user->last_name) : 'Unknown User';
                $serviceName = $reservation->service?->service_name ?? 'Unknown Service';
                $this->sendSMS(
                    $adviser->phone,
                    "New reservation request from {$requestorName} for {$serviceName} on " . $reservation->schedule_date->format('M d, Y h:i A') . ". Please review in eReligiousServices."
                );
            }
        } else {
            Log::warning('No organization or adviser found for reservation #' . $reservation->reservation_id . ' (org_id: ' . ($reservation->org_id ?? 'NULL') . ')');
        }

        // Handle priest/admin notification based on selection type
        if ($priestSelectionType === 'specific' && $reservation->officiant_id) {
            // Specific priest selected - notify them directly
            $priest = User::find($reservation->officiant_id);
            
            if ($priest) {
                Log::info('Notifying priest: ' . $priest->full_name . ' (ID: ' . $priest->id . ') for reservation #' . $reservation->reservation_id);
                
                // Send email to priest
                try {
                    if ($priest->email) {
                        $this->sendMailableSafely($priest->email, new \App\Mail\ReservationPriestAssigned($reservation), [
                            'action' => 'reservation_submitted_priest_assignment',
                            'reservation_id' => $reservation->reservation_id,
                            'user_id' => $priest->id,
                        ]);
                        Log::info('Email sent to priest: ' . $priest->email);
                    }
                } catch (\Throwable $e) {
                    Log::warning('Failed to send priest email on submission: ' . $e->getMessage());
                }
                
                // Create in-app notification for priest
                try {
                     $serviceName = $reservation->service?->service_name ?? 'Unknown Service';
                    $message = "You have been assigned to a new reservation: <strong>{$serviceName}</strong> on " . $reservation->schedule_date->format('M d, Y h:i A');
                    $notificationData = [
                        'user_id' => $priest->id,
                        'reservation_id' => $reservation->reservation_id,
                        'message' => $message,
                        'type' => 'Assignment',
                        'sent_at' => now(),
                    ];
                    if (Schema::hasColumn('notifications', 'data')) {
                        $notificationData['data'] = json_encode([
                            'service_name' => $reservation->service?->service_name ?? 'Unknown Service',
                            'schedule_date' => $reservation->schedule_date->format('Y-m-d H:i:s'),
                            'requestor_name' => $reservation->user ? ($reservation->user->first_name . ' ' . $reservation->user->last_name) : 'Unknown User',
                            'venue' => $reservation->custom_venue_name ?? $reservation->venue?->name ?? 'N/A',
                            'action' => 'priest_assignment',
                        ]);
                    }
                    $notification = Notification::create($notificationData);
                    Log::info('In-app notification created for priest (ID: ' . $notification->id . ')');
                } catch (\Throwable $e) {
                    Log::error('Failed to create priest in-app notification: ' . $e->getMessage());
                }
            }
        } elseif ($priestSelectionType === 'any_available' || $priestSelectionType === 'external') {
            // Notify admin/staff for priest assignment or external priest review
            $admins = User::whereIn('role', ['admin', 'staff'])->get();
            
            foreach ($admins as $admin) {
                try {
                    $serviceName = $reservation->service?->service_name ?? 'Unknown Service';
                    $actionType = $priestSelectionType === 'any_available' ? 'assign an available priest' : 'review external priest details';
                    $message = "New reservation requires admin action: <strong>{$serviceName}</strong> on " . $reservation->schedule_date->format('M d, Y h:i A') . ". Please {$actionType}.";
                    
                    $notificationData = [
                        'user_id' => $admin->id,
                        'reservation_id' => $reservation->reservation_id,
                        'message' => $message,
                        'type' => 'Assignment',
                        'sent_at' => now(),
                    ];
                    
                    if (Schema::hasColumn('notifications', 'data')) {
                        $dataContent = [
                            'service_name' => $reservation->service?->service_name ?? 'Unknown Service',
                            'schedule_date' => $reservation->schedule_date->format('Y-m-d H:i:s'),
                            'requestor_name' => $reservation->user ? ($reservation->user->first_name . ' ' . $reservation->user->last_name) : 'Unknown User',
                            'priest_selection_type' => $priestSelectionType,
                            'action' => $priestSelectionType === 'any_available' ? 'admin_priest_assignment_required' : 'admin_external_priest_review',
                        ];
                        
                        if ($priestSelectionType === 'external') {
                            $dataContent['external_priest_name'] = $reservation->external_priest_name ?? 'N/A';
                            $dataContent['external_priest_contact'] = $reservation->external_priest_contact ?? 'N/A';
                        }
                        
                        $notificationData['data'] = json_encode($dataContent);
                    }
                    
                    Notification::create($notificationData);
                    Log::info('In-app notification created for admin (ID: ' . $admin->id . ') - ' . $priestSelectionType);
                } catch (\Throwable $e) {
                    Log::error('Failed to create admin in-app notification: ' . $e->getMessage());
                }
            }
        }
    }

    /**
     * Send notification when adviser approves
     */
    public function notifyAdviserApproved(Reservation $reservation, string $remarks = ''): void
    {
        // Email to requestor
        try {
            if ($reservation->user && $reservation->user->email) {
                $this->sendMailableSafely($reservation->user->email, new ReservationAdviserApproved($reservation, $remarks), [
                    'action' => 'adviser_approved_requestor',
                    'reservation_id' => $reservation->reservation_id,
                    'user_id' => $reservation->user_id,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to send adviser approval email to requestor: ' . $e->getMessage());
        }

        // Email to CREaM Admin/Staff
        try {
            $admins = User::whereIn('role', ['admin', 'staff'])->get();
            foreach ($admins as $admin) {
                if ($admin->email) {
                    $this->sendMailableSafely($admin->email, new \App\Mail\AdviserApprovedToAdmin($reservation, $remarks), [
                        'action' => 'adviser_approved_admin_staff',
                        'reservation_id' => $reservation->reservation_id,
                        'user_id' => $admin->id,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to send adviser approval email to admin/staff: ' . $e->getMessage());
        }

        // Create in-app notifications for Admin/Staff to proceed with priest assignment
        try {
            $adminsAndStaff = User::whereIn('role', ['admin', 'staff'])->where('status', 'active')->get();
            foreach ($adminsAndStaff as $user) {
                $serviceName = $reservation->service?->service_name ?? 'Unknown Service';
                $message = "Adviser approved reservation for <strong>{$serviceName}</strong>. Please assign a priest.";
                $notificationData = [
                    'user_id' => $user->id,
                    'reservation_id' => $reservation->reservation_id,
                    'message' => $message,
                    'type' => NotificationHelper::TYPE_UPDATE,
                    'sent_at' => now(),
                ];
                if (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'data')) {
                    $notificationData['data'] = json_encode([
                        'action' => 'adviser_approved',
                        'service_name' => $reservation->service?->service_name ?? 'Unknown Service',
                        'schedule_date' => optional($reservation->schedule_date)->format('Y-m-d H:i:s'),
                        'requestor_name' => $reservation->user ? ($reservation->user->first_name . ' ' . $reservation->user->last_name) : 'Unknown User',
                        'organization' => optional($reservation->organization)->org_name,
                    ]);
                }
                NotificationHelper::make($notificationData);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to create admin/staff notification on adviser approval: ' . $e->getMessage());
        }

        // Notify assigned priest (if any)
        if ($reservation->officiant_id && $reservation->officiant) {
            try {
                // Email to priest
                if ($reservation->officiant->email) {
                    $this->sendMailableSafely($reservation->officiant->email, new ReservationPriestAssigned($reservation), [
                        'action' => 'adviser_approved_priest_assigned',
                        'reservation_id' => $reservation->reservation_id,
                        'user_id' => $reservation->officiant_id,
                    ]);
                }

                // In-app notification to priest
                $serviceName = $reservation->service?->service_name ?? 'Unknown Service';
                $message = "You have been assigned to a new reservation: <strong>{$serviceName}</strong> on " . $reservation->schedule_date->format('M d, Y h:i A');
                $notificationData = [
                    'user_id' => $reservation->officiant_id,
                    'reservation_id' => $reservation->reservation_id,
                    'message' => $message,
                    'type' => NotificationHelper::TYPE_ASSIGNMENT,
                    'sent_at' => now(),
                ];
                if (Schema::hasColumn('notifications', 'data')) {
                    $notificationData['data'] = json_encode([
                        'service_name' => $reservation->service?->service_name ?? 'Unknown Service',
                        'schedule_date' => $reservation->schedule_date->format('Y-m-d H:i:s'),
                        'action' => 'priest_assignment',
                    ]);
                }
                NotificationHelper::make($notificationData);

                // SMS to priest
                if ($reservation->officiant->phone) {
                    $serviceName = $reservation->service?->service_name ?? 'Unknown Service';
                    $this->sendSMS(
                        $reservation->officiant->phone,
                        "New service assignment: {$serviceName} on " . $reservation->schedule_date->format('M d, Y h:i A') . ". Please confirm in eReligiousServices."
                    );
                }
            } catch (\Exception $e) {
                Log::warning('Failed to notify priest on adviser approval: ' . $e->getMessage());
            }
        }

        // SMS to requestor
        if ($reservation->user && $reservation->user->phone) {
            $this->sendSMS(
                $reservation->user->phone,
                "Good news! Your reservation for " . ($reservation->service?->service_name ?? 'Unknown Service') . " has been approved by your organization adviser. Awaiting final CREaM approval."
            );
        }

    // In-app notification for requestor
        try {
            $message = "Your reservation was approved by your adviser";
            $notificationData = [
                'user_id' => $reservation->user_id,
                'reservation_id' => $reservation->reservation_id,
                'message' => $message,
                'type' => NotificationHelper::TYPE_UPDATE,
                'sent_at' => now(),
            ];
            if (Schema::hasColumn('notifications', 'data')) {
                $notificationData['data'] = [
                    'service_name' => $reservation->service?->service_name ?? 'Unknown Service',
                    'schedule_date' => $reservation->schedule_date->format('Y-m-d H:i:s'),
                    'action' => 'adviser_approved',
                ];
            }
            NotificationHelper::make($notificationData);
        } catch (\Exception $e) {
            Log::warning('Failed to create requestor in-app notification (adviser approved): ' . $e->getMessage());
        }
    }

    /**
     * Send notification when adviser rejects
     */
    public function notifyAdviserRejected(Reservation $reservation, string $reason, ?string $organizationName = null): void
    {
        $orgInfo = $organizationName ? " ({$organizationName})" : '';
        
        // Email to requestor
        try {
            if ($reservation->user && $reservation->user->email) {
                $this->sendMailableSafely($reservation->user->email, new ReservationAdviserRejected($reservation, $reason), [
                    'action' => 'adviser_rejected_requestor',
                    'reservation_id' => $reservation->reservation_id,
                    'user_id' => $reservation->user_id,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to send adviser rejection email to requestor: ' . $e->getMessage());
        }

        // SMS to requestor
        try {
            if ($reservation->user && $reservation->user->phone) {
                $serviceName = $reservation->service?->service_name ?? 'Unknown Service';
                $this->sendSMS(
                    $reservation->user->phone,
                    "Your reservation for {$serviceName} has been rejected by the adviser."
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to send adviser rejection SMS: ' . $e->getMessage());
        }

        // Notify CREaM staff for record keeping
        try {
            $staff = User::where('role', 'staff')->get();
            foreach ($staff as $member) {
                if ($member->email) {
                    $this->sendMailableSafely($member->email, new \App\Mail\AdviserRejectedToAdmin($reservation, $reason), [
                        'action' => 'adviser_rejected_staff',
                        'reservation_id' => $reservation->reservation_id,
                        'user_id' => $member->id,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to send adviser rejection email to staff: ' . $e->getMessage());
        }

        // In-app notification for requestor
        try {
            $message = $organizationName 
                ? "Your reservation was not approved by the adviser of {$organizationName}"
                : "Your reservation was not approved by your adviser";
            $notificationData = [
                'user_id' => $reservation->user_id,
                'reservation_id' => $reservation->reservation_id,
                'message' => $message,
                'type' => NotificationHelper::TYPE_UPDATE,
                'sent_at' => now(),
            ];
            if (Schema::hasColumn('notifications', 'data')) {
                $notificationData['data'] = [
                    'reason' => $reason,
                    'action' => 'adviser_rejected',
                    'organization' => $organizationName,
                ];
            }
            NotificationHelper::make($notificationData);
        } catch (\Exception $e) {
            Log::warning('Failed to create requestor in-app notification (adviser rejected): ' . $e->getMessage());
        }

        // In-app notification for staff
        try {
            $staffUsers = User::whereIn('role', ['staff', 'admin'])->where('status', 'active')->get();
            foreach ($staffUsers as $staffUser) {
                $serviceName = $reservation->service?->service_name ?? 'Unknown Service';
                $staffMessage = "Adviser{$orgInfo} rejected reservation for {$serviceName}.";
                NotificationHelper::make([
                    'user_id' => $staffUser->id,
                    'reservation_id' => $reservation->reservation_id,
                    'message' => $staffMessage,
                    'type' => NotificationHelper::TYPE_UPDATE,
                    'sent_at' => now(),
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to create staff in-app notification (adviser rejected): ' . $e->getMessage());
        }
    }

    /**
     * Send notification when admin rejects a reservation
     */
    public function notifyAdminRejected(Reservation $reservation, string $reason, ?User $actor = null): void
    {
        $adminName = $actor ? ($actor->first_name . ' ' . $actor->last_name) : 'an administrator';

        // Email to requestor (using styled mailable)
        if ($reservation->user && $reservation->user->email) {
            try {
                $this->sendMailableSafely($reservation->user->email, new \App\Mail\ReservationAdminRejected($reservation, $reason, $adminName), [
                    'action' => 'admin_rejected_requestor',
                    'reservation_id' => $reservation->reservation_id,
                    'user_id' => $reservation->user_id,
                ]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to send admin reject email to requestor: ' . $e->getMessage());
            }
        }

        // In-app notification for requestor
        try {
            $message = "Your reservation was rejected by admin";
            $notificationData = [
                'user_id' => $reservation->user_id,
                'reservation_id' => $reservation->reservation_id,
                'message' => $message,
                'type' => NotificationHelper::TYPE_UPDATE,
                'sent_at' => now(),
            ];
            if (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'data')) {
                $notificationData['data'] = [
                    'reason' => $reason,
                    'action' => 'admin_rejected',
                    'admin_name' => $adminName,
                ];
            }
            NotificationHelper::make($notificationData);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to create requestor in-app notification (admin rejected): ' . $e->getMessage());
        }

        // Optional: notify admins/staff for audit trail
        try {
            $adminsAndStaff = User::whereIn('role', ['admin', 'staff'])->where('status', 'active')->get();
            foreach ($adminsAndStaff as $user) {
                $message = "Reservation rejected by <strong>{$adminName}</strong>";
                $notificationData = [
                    'user_id' => $user->id,
                    'reservation_id' => $reservation->reservation_id,
                    'message' => $message,
                    'type' => NotificationHelper::TYPE_UPDATE,
                    'sent_at' => now(),
                ];
                if (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'data')) {
                    $notificationData['data'] = [
                        'reason' => $reason,
                        'action' => 'admin_rejected',
                        'service_name' => $reservation->service?->service_name ?? 'Unknown Service',
                        'requestor_name' => $reservation->user ? ($reservation->user->first_name . ' ' . $reservation->user->last_name) : 'Unknown User',
                    ];
                }
                NotificationHelper::make($notificationData);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to create admin/staff in-app notification (admin rejected): ' . $e->getMessage());
        }
    }

    /**
     * Send notification when staff rejects a reservation
     */
    public function notifyStaffRejected(Reservation $reservation, string $reason, ?User $actor = null): void
    {
        $staffName = $actor ? ($actor->first_name . ' ' . $actor->last_name) : 'a staff member';

        // 1. Notify Requestor (using AdminRejected mailable as generic management rejection)
        if ($reservation->user && $reservation->user->email) {
            try {
                $this->sendMailableSafely($reservation->user->email, new \App\Mail\ReservationAdminRejected($reservation, $reason, $staffName), [
                    'action' => 'staff_rejected_requestor',
                    'reservation_id' => $reservation->reservation_id,
                    'user_id' => $reservation->user_id,
                ]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to send staff reject email to requestor: ' . $e->getMessage());
            }
        }

        // In-app notification for requestor
        try {
            $message = "Your reservation was rejected by staff ({$staffName})";
            $notificationData = [
                'user_id' => $reservation->user_id,
                'reservation_id' => $reservation->reservation_id,
                'message' => $message,
                'type' => NotificationHelper::TYPE_UPDATE,
                'sent_at' => now(),
            ];
            if (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'data')) {
                $notificationData['data'] = [
                    'reason' => $reason,
                    'action' => 'staff_rejected',
                    'staff_name' => $staffName,
                ];
            }
            NotificationHelper::make($notificationData);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to create requestor in-app notification (staff rejected): ' . $e->getMessage());
        }

        // 2. Notify Advisers
        try {
            $advisersToNotify = collect([]);
            if ($reservation->organizations->isNotEmpty()) {
                foreach ($reservation->organizations as $org) {
                    if ($org->adviser) $advisersToNotify->push($org->adviser);
                }
            } elseif ($reservation->organization && $reservation->organization->adviser) {
                $advisersToNotify->push($reservation->organization->adviser);
            }

            foreach ($advisersToNotify->unique('id') as $adviser) {
                $serviceName = $reservation->service?->service_name ?? 'Unknown Service';
                NotificationHelper::make([
                    'user_id' => $adviser->id,
                    'reservation_id' => $reservation->reservation_id,
                    'message' => "Reservation for {$serviceName} was rejected by staff: {$staffName}",
                    'type' => NotificationHelper::TYPE_UPDATE,
                    'sent_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to notify advisers of staff rejection: ' . $e->getMessage());
        }

        // 3. Notify Priest (if assigned)
        try {
            if ($reservation->officiant_id) {
                $serviceName = $reservation->service?->service_name ?? 'Unknown Service';
                NotificationHelper::make([
                    'user_id' => $reservation->officiant_id,
                    'reservation_id' => $reservation->reservation_id,
                    'message' => "Reservation for {$serviceName} (scheduled {$reservation->schedule_date->format('M d, Y')}) was rejected by staff.",
                    'type' => NotificationHelper::TYPE_UPDATE,
                    'sent_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to notify priest of staff rejection: ' . $e->getMessage());
        }
    }

    /**
     * Send notification when priest is assigned
     */
    public function notifyPriestAssigned(Reservation $reservation): void
    {
        // Ensure reservation_priest pivot row exists/updated for the assigned priest
        try {
            if ($reservation->officiant_id) {
                $exists = DB::table('reservation_priest')
                    ->where('reservation_id', $reservation->reservation_id)
                    ->where('priest_id', $reservation->officiant_id)
                    ->exists();

                if ($exists) {
                    DB::table('reservation_priest')
                        ->where('reservation_id', $reservation->reservation_id)
                        ->where('priest_id', $reservation->officiant_id)
                        ->update([
                            'confirmation_status' => 'pending',
                            'notified' => true,
                            'notified_at' => now(),
                            'updated_at' => now(),
                        ]);
                } else {
                    DB::table('reservation_priest')->insert([
                        'reservation_id' => $reservation->reservation_id,
                        'priest_id' => $reservation->officiant_id,
                        'confirmation_status' => 'pending',
                        'notified' => true,
                        'notified_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to upsert reservation_priest pivot on notifyPriestAssigned: ' . $e->getMessage());
        }

        // Create in-app notification for priest
        if ($reservation->officiant) {
            try {
                $serviceName = $reservation->service?->service_name ?? 'Unknown Service';
                $message = "You have been assigned to officiate {$serviceName} on " .
                          $reservation->schedule_date->format('M d, Y h:i A') .
                          ". Please review and confirm your availability.";

                $notificationData = [
                    'user_id' => $reservation->officiant_id,
                    'reservation_id' => $reservation->reservation_id,
                    'message' => $message,
                    'type' => NotificationHelper::TYPE_ASSIGNMENT,
                    'sent_at' => now(),
                ];

                // Add data field if column exists
                try {
                    if (Schema::hasColumn('notifications', 'data')) {
                        $notificationData['data'] = [
                            'service_name' => $reservation->service?->service_name ?? 'Unknown Service',
                            'requestor_name' => $reservation->user ? ($reservation->user->first_name . ' ' . $reservation->user->last_name) : 'Unknown User',
                            'schedule_date' => $reservation->schedule_date->toDateTimeString(),
                            'venue' => $reservation->custom_venue_name ?? $reservation->venue?->name ?? 'N/A',
                            'admin_remarks' => $reservation->history()
                                ->where('action', 'priest_reassigned')
                                ->orWhere('action', 'priest_assigned')
                                ->latest()
                                ->value('remarks'),
                        ];
                    }
                } catch (\Exception $e) {
                    // Column doesn't exist or error, skip data field
                    Log::warning('Could not add data to priest assignment notification: ' . $e->getMessage());
                }

                NotificationHelper::make($notificationData);
                Log::info("In-app notification created for priest (ID: {$reservation->officiant_id}) for reservation {$reservation->reservation_id}");
            } catch (\Exception $e) {
                Log::error('Failed to create in-app notification for priest assignment: ' . $e->getMessage());
            }
        }

        // Email to priest
        if ($reservation->officiant && $reservation->officiant->email) {
            $this->sendMailableSafely($reservation->officiant->email, new ReservationPriestAssigned($reservation), [
                'action' => 'priest_assigned_officiant',
                'reservation_id' => $reservation->reservation_id,
                'user_id' => $reservation->officiant_id,
            ]);
        }

        // SMS to priest
        if ($reservation->officiant->phone) {
            $serviceName = $reservation->service?->service_name ?? 'Unknown Service';
            $venueName = $reservation->custom_venue_name ?? ($reservation->venue?->name ?? 'N/A');
            $this->sendSMS(
                $reservation->officiant->phone,
                "You have been assigned to officiate {$serviceName} on "
                . $reservation->schedule_date->format('M d, Y h:i A')
                . " at "
                . $venueName
                . ". Please confirm your availability in eReligiousServices."
            );
        }

        // Email to requestor (update)
        if ($reservation->user && $reservation->user->email) {
            $this->sendMailableSafely($reservation->user->email, new \App\Mail\PriestAssignedToRequestor($reservation), [
                'action' => 'priest_assigned_requestor_update',
                'reservation_id' => $reservation->reservation_id,
                'user_id' => $reservation->user_id,
            ]);
        }
    }

    /**
     * Send notification when priest declines assignment
     * Notifies admin/staff so they can reassign another priest
     */
    public function notifyPriestDeclined(Reservation $reservation, string $reason, $priestId = null): void
    {
        // Get priest who declined - use passed ID or try to fetch from history
        if ($priestId) {
            $declinedPriest = User::find($priestId);
        } else {
            $declinedPriest = $reservation->history()
                ->where('action', 'priest_declined')
                ->with('performer')
                ->latest()
                ->first()
                ->performer ?? null;
        }

        // Email to CREaM Admin/Staff for reassignment
        $admins = User::whereIn('role', ['admin', 'staff'])->get();
        foreach ($admins as $admin) {
            if ($admin->email) {
                $this->sendMailableSafely($admin->email, new ReservationPriestDeclined($reservation, $reason), [
                    'action' => 'priest_declined_admin_staff',
                    'reservation_id' => $reservation->reservation_id,
                    'user_id' => $admin->id,
                ]);
            }

            // Create in-app notification for each admin
            try {
                $priestName = $declinedPriest ? 'Fr. ' . $declinedPriest->first_name . ' ' . $declinedPriest->last_name : 'A priest';
                $message = "<strong>{$priestName}</strong> declined the reservation you assigned";

                $notificationData = [
                    'user_id' => $admin->id,
                    'reservation_id' => $reservation->reservation_id,
                    'message' => $message,
                    'type' => NotificationHelper::TYPE_PRIEST_DECLINED,
                    'sent_at' => now(),
                ];

                // Add data field if column exists (for future use)
                try {
                    if (Schema::hasColumn('notifications', 'data')) {
                        $notificationData['data'] = json_encode([
                            'reason' => $reason,
                            'service_name' => $reservation->service?->service_name ?? 'Unknown Service',
                            'schedule_date' => $reservation->schedule_date->format('Y-m-d H:i:s'),
                            'requestor_name' => $reservation->user ? ($reservation->user->first_name . ' ' . $reservation->user->last_name) : 'Unknown User',
                            'venue' => $reservation->custom_venue_name ?? $reservation->venue?->name ?? 'N/A',
                        ]);
                    }
                } catch (\Exception $e) {
                    // Data column doesn't exist, that's okay
                    Log::info('Data column check failed: ' . $e->getMessage());
                }

                $createdNotification = NotificationHelper::make($notificationData);
                Log::info('Notification created successfully', [
                    'notification_id' => $createdNotification->notification_id,
                    'user_id' => $admin->id,
                    'type' => NotificationHelper::TYPE_PRIEST_DECLINED,
                    'priest_name' => $priestName,
                    'message' => $message
                ]);
            } catch (\Exception $e) {
                // Log error but don't stop the process
                Log::error('Failed to create in-app notification: ' . $e->getMessage(), [
                    'admin_id' => $admin->id,
                    'reservation_id' => $reservation->reservation_id,
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        // Notify requestor (in-app)
        try {
            $serviceName = $reservation->service?->service_name ?? 'Unknown Service';
            $message = "A priest declined your reservation for <strong>{$serviceName}</strong>. The admin will assign another presider.";
            NotificationHelper::make([
                'user_id' => $reservation->user_id,
                'reservation_id' => $reservation->reservation_id,
                'message' => $message,
                'type' => NotificationHelper::TYPE_UPDATE,
                'sent_at' => now(),
                'data' => [
                    'action' => 'priest_declined',
                    'reason' => $reason,
                    'service_name' => $serviceName,
                    'schedule_date' => optional($reservation->schedule_date)->format('Y-m-d H:i:s'),
                ],
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to create requestor priest-declined notification: ' . $e->getMessage());
        }

        // Notify adviser (in-app)
        if ($reservation->organization && $reservation->organization->adviser) {
            try {
                $serviceName = $reservation->service?->service_name ?? 'Unknown Service';
                NotificationHelper::make([
                    'user_id' => $reservation->organization->adviser->id,
                    'reservation_id' => $reservation->reservation_id,
                    'message' => "Priest declined reservation for {$serviceName}. Slot is open for reassignment.",
                    'type' => NotificationHelper::TYPE_PRIEST_DECLINED,
                    'sent_at' => now(),
                    'data' => [
                        'reason' => $reason,
                        'service_name' => $serviceName,
                        'schedule_date' => optional($reservation->schedule_date)->format('Y-m-d H:i:s'),
                        'action' => 'priest_declined',
                    ],
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to create adviser priest-declined notification: ' . $e->getMessage());
            }
        }

        // SMS to admin/staff
        $adminWithPhone = User::whereIn('role', ['admin', 'staff'])
            ->whereNotNull('phone')
            ->first();

        if ($adminWithPhone && $adminWithPhone->phone) {
            $this->sendSMS(
                $adminWithPhone->phone,
                "URGENT: Priest declined reservation #{$reservation->reservation_id} for " . ($reservation->service?->service_name ?? 'Unknown Service') . " on " . $reservation->schedule_date->format('M d, Y') . ". Please assign another presider."
            );
        }
    }

    /**
     * Send notification when reservation is cancelled
     */
    public function notifyCancellation(Reservation $reservation, string $reason, string $cancelledBy): void
    {
        $reason = trim(strip_tags($reason));
        $cancelledBy = trim(strip_tags($cancelledBy));
        $recipients = [];

        // Always notify requestor
        if ($reservation->user) {
            $recipients[] = $reservation->user;
        }

        // Notify adviser
        if ($reservation->organization && $reservation->organization->adviser) {
            $recipients[] = $reservation->organization->adviser;
        }

        // Notify assigned priest
        if ($reservation->officiant) {
            $recipients[] = $reservation->officiant;
        }

        // Notify CREaM staff
        $staff = User::whereIn('role', ['admin', 'staff'])->get();
        foreach ($staff as $member) {
            $recipients[] = $member;
        }

        // Send emails
        foreach ($recipients as $recipient) {
            if ($recipient->email) {
                $this->sendMailableSafely($recipient->email, new ReservationCancelled($reservation, $reason, $cancelledBy), [
                    'action' => 'reservation_cancelled',
                    'reservation_id' => $reservation->reservation_id,
                    'recipient_id' => $recipient->id,
                ]);
            }

            // Send SMS to key parties
            if ($recipient->phone && in_array($recipient->id, [
                $reservation->user_id,
                $reservation->officiant_id,
                $reservation->organization?->adviser_id
            ])) {
                $serviceName = $reservation->service?->service_name ?? 'Unknown Service';
                $this->sendSMS(
                    $recipient->phone,
                    "Reservation for {$serviceName} on " . $reservation->schedule_date->format('M d, Y') . " has been cancelled. Reason: {$reason}"
                );
            }
        }

        // Create in-app notifications for all relevant recipients (except requestor handled below)
        foreach ($recipients as $recipient) {
            // Skip duplicate for requestor; we'll create a tailored one below
            if ($recipient->id === $reservation->user_id) {
                continue;
            }

            try {
                // Tailor message per role when possible
                $isPriest = isset($reservation->officiant_id) && $recipient->id === $reservation->officiant_id;
                $isAdviser = ($reservation->organization->adviser_id ?? null) === $recipient->id;
                $serviceName = $reservation->service?->service_name ?? 'Unknown Service';

                if ($isPriest) {
                    $message = "The requestor cancelled the reservation you were assigned to officiate for <strong>{$serviceName}</strong>";
                } elseif ($isAdviser) {
                    $requestorName = $reservation->user ? ($reservation->user->first_name . ' ' . $reservation->user->last_name) : 'Unknown User';
                    $message = "<strong>{$requestorName}</strong> cancelled their reservation for <strong>{$serviceName}</strong>";
                } else {
                    $message = "Reservation cancelled by <strong>{$cancelledBy}</strong> for <strong>{$serviceName}</strong>";
                }

                NotificationHelper::make([
                    'user_id' => $recipient->id,
                    'reservation_id' => $reservation->reservation_id,
                    'message' => $message,
                    'type' => NotificationHelper::TYPE_UPDATE,
                    'sent_at' => now(),
                    'data' => [
                        'reason' => $reason,
                        'cancelled_by' => $cancelledBy,
                        'service_name' => $serviceName,
                        'schedule_date' => optional($reservation->schedule_date)->format('Y-m-d H:i:s'),
                        'action' => 'reservation_cancelled',
                    ],
                ]);
            } catch (\Exception $e) {
                // Don't block other notifications if one fails
                Log::warning('Failed to create in-app cancellation notification: ' . $e->getMessage());
            }
        }

        // In-app notification to requestor
        try {
            $message = "Your reservation was cancelled";
            NotificationHelper::make([
                'user_id' => $reservation->user_id,
                'reservation_id' => $reservation->reservation_id,
                'message' => $message,
                'type' => NotificationHelper::TYPE_UPDATE,
                'sent_at' => now(),
                'data' => ['reason' => $reason, 'cancelled_by' => $cancelledBy]
            ]);
        } catch (\Exception $e) {
            // ignore
        }
    }

    /**
     * Send follow-up notification for unnoticed reservation (adviser hasn't acted in 24+ hours)
     * 
     * This method:
     * 1. Sends email notification to staff about the unnoticed reservation
     * 2. Sends in-app notification to staff with adviser contact info
     * 3. Sends reminder email to adviser
     * 4. Creates "Unnoticed Reservation" in-app notification for adviser with their own contact info displayed
     */
    public function notifyAdviserFollowUp(Reservation $reservation): void
    {
        $adviser = null;
        $adviserName = 'Unknown Adviser';
        $adviserEmail = 'N/A';
        $adviserPhone = 'N/A';

        if ($reservation->organization && $reservation->organization->adviser) {
            $adviser = $reservation->organization->adviser;
            $adviserName = $adviser->full_name ?? $adviser->name ?? 'Unknown';
            $adviserEmail = $adviser->email ?? 'N/A';
            $adviserPhone = $adviser->phone ?? $adviser->contact_number ?? 'N/A';
        }

        $requestorName = $reservation->user ? ($reservation->user->first_name . ' ' . $reservation->user->last_name) : 'Unknown User';
        $serviceName = $reservation->service?->service_name ?? 'N/A';
        $scheduleDate = $reservation->schedule_date ? $reservation->schedule_date->format('F d, Y - h:i A') : 'N/A';
        $orgName = $reservation->organization?->org_name ?? 'N/A';
        $hoursPending = $reservation->created_at->diffInHours(now());

        // 1. Send EMAIL notification to staff about unnoticed reservation
        $staffMembers = User::whereIn('role', ['staff', 'admin'])->get();
        foreach ($staffMembers as $staff) {
            if ($staff->email) {
                try {
                    $this->sendMailableSafely($staff->email, new ReservationUnnoticedAlert($reservation, $adviser, $hoursPending), [
                        'action' => 'adviser_follow_up_staff_alert',
                        'reservation_id' => $reservation->reservation_id,
                        'user_id' => $staff->id,
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Failed to send unnoticed reservation email to staff: ' . $e->getMessage());
                }
            }

            // 2. Send IN-APP notification to staff with adviser contact info
            try {
                $staffMessage = "⚠️ <strong>Unnoticed Reservation</strong>: Request #{$reservation->reservation_id} from <strong>{$requestorName}</strong> " .
                    "has been pending for <strong>{$hoursPending} hours</strong> without adviser response. " .
                    "Adviser: <strong>{$adviserName}</strong>";

                $notificationData = [
                    'user_id' => $staff->id,
                    'reservation_id' => $reservation->reservation_id,
                    'message' => $staffMessage,
                    'type' => NotificationHelper::TYPE_URGENT,
                    'sent_at' => now(),
                ];

                if (Schema::hasColumn('notifications', 'data')) {
                    $notificationData['data'] = [
                        'action' => 'unnoticed_reservation',
                        'adviser_name' => $adviserName,
                        'adviser_email' => $adviserEmail,
                        'adviser_phone' => $adviserPhone,
                        'hours_pending' => $hoursPending,
                        'requestor_name' => $requestorName,
                        'service_name' => $serviceName,
                        'organization_name' => $orgName,
                    ];
                }

                NotificationHelper::make($notificationData);
            } catch (\Exception $e) {
                Log::warning('Failed to create staff in-app notification for unnoticed reservation: ' . $e->getMessage());
            }
        }

        // 3. Send reminder EMAIL to adviser
        if ($adviser && $adviser->email) {
            try {
                $this->sendMailableSafely($adviser->email, new \App\Mail\AdviserPendingReservation($reservation, $adviser), [
                    'action' => 'adviser_follow_up_reminder',
                    'reservation_id' => $reservation->reservation_id,
                    'user_id' => $adviser->id,
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to send reminder email to adviser: ' . $e->getMessage());
            }
        }

        // 4. Create "Unnoticed Reservation" IN-APP notification for adviser
        // This notification will display their own contact info when tapped
        if ($adviser) {
            try {
                $adviserMessage = "⏰ <strong>Action Required</strong>: Reservation request from <strong>{$requestorName}</strong> " .
                    "for {$serviceName} has been waiting for your approval for <strong>{$hoursPending} hours</strong>. " .
                    "Please review and approve/reject this request.";

                $adviserNotificationData = [
                    'user_id' => $adviser->id,
                    'reservation_id' => $reservation->reservation_id,
                    'message' => $adviserMessage,
                    'type' => NotificationHelper::TYPE_URGENT,
                    'sent_at' => now(),
                ];

                if (Schema::hasColumn('notifications', 'data')) {
                    $adviserNotificationData['data'] = [
                        'action' => 'unnoticed_reservation_reminder',
                        'your_contact_email' => $adviserEmail,
                        'your_contact_phone' => $adviserPhone,
                        'hours_pending' => $hoursPending,
                        'requestor_name' => $requestorName,
                        'service_name' => $serviceName,
                        'schedule_date' => $scheduleDate,
                        'organization_name' => $orgName,
                        'show_adviser_contact' => true,
                    ];
                }

                NotificationHelper::make($adviserNotificationData);
            } catch (\Exception $e) {
                Log::warning('Failed to create adviser in-app notification for unnoticed reservation: ' . $e->getMessage());
            }
        }
    }

    /**
     * Send notification when priest confirms their assignment
     * Notifies admin/staff that priest has confirmed availability
     *
     * Note: If priest IS an admin, we skip notifying them to avoid self-notification
     */
    public function notifyPriestConfirmed(Reservation $reservation, $priestId): void
    {
        // Get priest info
        $priest = User::find($priestId);
        $priestName = $priest ? 'Fr. ' . $priest->first_name . ' ' . $priest->last_name : 'A priest';
        $requestorName = $reservation->user ? ($reservation->user->first_name . ' ' . $reservation->user->last_name) : 'Unknown User';

        // Get admins/staff but EXCLUDE the priest if they are admin
        $admins = User::whereIn('role', ['admin', 'staff'])
            ->where('id', '!=', $priestId) // Skip self-notification
            ->get();

        // Only proceed if there are OTHER admins to notify
        if ($admins->isEmpty()) {
            Log::info('No admins to notify (priest is admin themselves)', [
                'priest_id' => $priestId,
                'reservation_id' => $reservation->reservation_id,
            ]);
            return;
        }

        foreach ($admins as $admin) {
            // Email notification
            if ($admin->email) {
                // Create styled email notification
                try {
                    $this->sendMailableSafely($admin->email, new ReservationPriestConfirmedToAdmin($reservation, $priestName), [
                        'action' => 'priest_confirmed_admin_staff',
                        'reservation_id' => $reservation->reservation_id,
                        'user_id' => $admin->id,
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Failed to send priest confirmation email to admin: ' . $e->getMessage());
                }
            }

            // Create in-app notification for each admin
            try {
                $message = "<strong>{$priestName}</strong> approved the reservation from <strong>{$requestorName}</strong>";

                $notificationData = [
                    'user_id' => $admin->id,
                    'reservation_id' => $reservation->reservation_id,
                    'message' => $message,
                    'type' => NotificationHelper::TYPE_UPDATE,
                    'sent_at' => now(),
                ];

                // Add data field if column exists
                try {
                    if (Schema::hasColumn('notifications', 'data')) {
                        $notificationData['data'] = [
                            'priest_name' => $priest ? $priest->first_name . ' ' . $priest->last_name : 'Unknown',
                            'priest_id' => $priestId,
                            'service_name' => $reservation->service?->service_name ?? 'Unknown Service',
                            'schedule_date' => $reservation->schedule_date->format('Y-m-d H:i:s'),
                            'requestor_name' => $requestorName,
                            'venue' => $reservation->custom_venue_name ?? $reservation->venue?->name ?? 'N/A',
                            'action' => 'priest_confirmed',
                        ];
                    }
                } catch (\Exception $e) {
                    Log::info('Data column check failed: ' . $e->getMessage());
                }

                NotificationHelper::make($notificationData);
                Log::info('Confirmation notification created for admin', [
                    'admin_id' => $admin->id,
                    'reservation_id' => $reservation->reservation_id,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create in-app notification for confirmation: ' . $e->getMessage());
            }
        }

        // SMS to admin (optional)
        $adminWithPhone = User::whereIn('role', ['admin', 'staff'])
            ->whereNotNull('phone')
            ->first();

        if ($adminWithPhone && $adminWithPhone->phone) {
            $serviceName = $reservation->service?->service_name ?? 'Unknown Service';
            $this->sendSMS(
                $adminWithPhone->phone,
                "GOOD NEWS: {$priestName} confirmed availability for {$serviceName} on " . $reservation->schedule_date->format('M d, Y') . ". Reservation approved!"
            );
        }

        // Notify requestor in-app
        try {
            $serviceName = $reservation->service?->service_name ?? 'Unknown Service';
            $message = "Great news! <strong>{$priestName}</strong> has confirmed your reservation for <strong>{$serviceName}</strong> on " . $reservation->schedule_date->format('M d, Y h:i A') . ". Your reservation is now approved!";
            $notificationData = [
                'user_id' => $reservation->user_id,
                'reservation_id' => $reservation->reservation_id,
                'message' => $message,
                'type' => NotificationHelper::TYPE_UPDATE,
                'sent_at' => now(),
            ];
            if (Schema::hasColumn('notifications', 'data')) {
                $notificationData['data'] = [
                    'priest_id' => $priestId,
                    'priest_name' => $priest ? $priest->first_name . ' ' . $priest->last_name : 'Unknown',
                    'service_name' => $serviceName,
                    'schedule_date' => $reservation->schedule_date->format('Y-m-d H:i:s'),
                    'action' => 'priest_confirmed',
                ];
            }
            NotificationHelper::make($notificationData);
        } catch (\Exception $e) {
            Log::warning('Failed to create requestor in-app notification (priest confirmed): ' . $e->getMessage());
        }

        // Send email to requestor
        try {
            if ($reservation->user && $reservation->user->email) {
                $this->sendMailableSafely($reservation->user->email, new \App\Mail\ReservationConfirmed($reservation, $priestName), [
                    'action' => 'priest_confirmed_requestor',
                    'reservation_id' => $reservation->reservation_id,
                    'user_id' => $reservation->user_id,
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to send requestor confirmation email: ' . $e->getMessage());
        }
    }

    /**
     * Send notification when ALL priests have confirmed (multi-priest scenario)
     * Notifies admin/staff that reservation is ready for final approval
     */
    public function notifyAllPriestsConfirmed(Reservation $reservation): void
    {
        $requestorName = $reservation->user ? ($reservation->user->first_name . ' ' . $reservation->user->last_name) : 'Unknown User';
        $priestNames = $reservation->priests->map(fn($p) => 'Fr. ' . $p->first_name . ' ' . $p->last_name)->join(', ');
        
        if (empty($priestNames) && $reservation->officiant) {
            $priestNames = 'Fr. ' . $reservation->officiant->first_name . ' ' . $reservation->officiant->last_name;
        }

        // Notify admins/staff
        $admins = User::whereIn('role', ['admin', 'staff'])->where('status', 'active')->get();

        foreach ($admins as $admin) {
            // Email notification
            try {
                if ($admin->email) {
                    $this->sendMailableSafely($admin->email, new ReservationAllPriestsConfirmed($reservation, $requestorName, $priestNames), [
                        'action' => 'all_priests_confirmed_admin_staff',
                        'reservation_id' => $reservation->reservation_id,
                        'user_id' => $admin->id,
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning('Failed to send all-priests-confirmed email: ' . $e->getMessage());
            }

            // In-app notification
            try {
                $serviceName = $reservation->service?->service_name ?? 'Unknown Service';
                $message = "All priests have confirmed for <strong>{$serviceName}</strong>. Ready for final approval.";
                NotificationHelper::make([
                    'user_id' => $admin->id,
                    'reservation_id' => $reservation->reservation_id,
                    'message' => $message,
                    'type' => NotificationHelper::TYPE_UPDATE,
                    'sent_at' => now(),
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to create all-priests-confirmed in-app notification: ' . $e->getMessage());
            }
        }

        // Notify requestor that priests have confirmed
        try {
            $serviceName = $reservation->service?->service_name ?? 'Unknown Service';
            $message = "Good news! All priests have confirmed for your <strong>{$serviceName}</strong> reservation. Awaiting final admin approval.";
            NotificationHelper::make([
                'user_id' => $reservation->user_id,
                'reservation_id' => $reservation->reservation_id,
                'message' => $message,
                'type' => NotificationHelper::TYPE_UPDATE,
                'sent_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to create requestor all-priests-confirmed notification: ' . $e->getMessage());
        }
    }

    /**
     * Send notification when priest undoes their decline (restores assignment)
     * Notifies admin/staff that priest is available again
     */
    public function notifyPriestUndeclined(Reservation $reservation, $priestId): void
    {
        // Get priest info
        $priest = User::find($priestId);
        $priestName = $priest ? 'Fr. ' . $priest->first_name . ' ' . $priest->last_name : 'A priest';

        // Email to CREaM Admin/Staff
        $admins = User::whereIn('role', ['admin', 'staff'])->get();
        foreach ($admins as $admin) {
            if ($admin->email) {
                try {
                // Create styled email notification
                $this->sendMailableSafely($admin->email, new ReservationPriestRestoredToAdmin($reservation, $priestName), [
                    'action' => 'priest_undeclined_admin_staff',
                    'reservation_id' => $reservation->reservation_id,
                    'user_id' => $admin->id,
                ]);
                 } catch (\Exception $e) {
                    Log::warning('Failed to send priest restored email: ' . $e->getMessage());
                }
            }
        }

        // SMS to admin (optional)
        $adminWithPhone = User::whereIn('role', ['admin', 'staff'])
            ->whereNotNull('phone')
            ->first();

        if ($adminWithPhone && $adminWithPhone->phone) {
            $serviceName = $reservation->service?->service_name ?? 'Unknown Service';
            $this->sendSMS(
                $adminWithPhone->phone,
                "GOOD NEWS: {$priestName} restored their assignment for {$serviceName} on " . $reservation->schedule_date->format('M d, Y') . ". Awaiting priest confirmation."
            );
        }

        // Requestor in-app
        try {
            $message = "A priest restored their availability for your reservation";
            NotificationHelper::make([
                'user_id' => $reservation->user_id,
                'reservation_id' => $reservation->reservation_id,
                'message' => $message,
                'type' => NotificationHelper::TYPE_UPDATE,
                'sent_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to create requestor in-app notification (priest undeclined): ' . $e->getMessage());
        }
    }

    /**
     * Send notification to requestor with a confirmation link after staff contact
     * Also logs an in-app notification for future reference.
     */
    public function notifyRequestorConfirmation(Reservation $reservation, string $confirmationUrl): void
    {
        // Email to requestor with confirmation link
        try {
            if ($reservation->user && $reservation->user->email) {
                $this->sendMailableSafely($reservation->user->email, new \App\Mail\RequestorConfirmation($reservation, $confirmationUrl), [
                    'action' => 'requestor_confirmation_link',
                    'reservation_id' => $reservation->reservation_id,
                    'user_id' => $reservation->user_id,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to send requestor confirmation email: ' . $e->getMessage());
        }

        // Optional SMS to requestor
        try {
            if ($reservation->user && $reservation->user->phone) {
                $serviceName = $reservation->service?->service_name ?? 'Unknown Service';
                $this->sendSMS(
                    $reservation->user->phone,
                    'Please confirm your reservation for ' . $serviceName . ' on ' . optional($reservation->schedule_date)->format('M d, Y h:i A') . ': ' . $confirmationUrl
                );
            }
        } catch (\Throwable $e) {
            Log::info('Could not send SMS for requestor confirmation: ' . $e->getMessage());
        }

        // In-app notification to requestor
        try {
            $serviceName = $reservation->service?->service_name ?? 'Unknown Service';
            $message = 'Please confirm your reservation: <strong>' . $serviceName . '</strong>';
            $data = [
                'service_name' => $serviceName,
                'schedule_date' => optional($reservation->schedule_date)->format('Y-m-d H:i:s'),
                'action' => 'requestor_confirmation_required',
                'confirmation_url' => $confirmationUrl,
            ];
            $payload = [
                'user_id' => $reservation->user_id,
                'reservation_id' => $reservation->reservation_id,
                'message' => $message,
                'type' => NotificationHelper::TYPE_UPDATE,
                'sent_at' => now(),
            ];
            if (Schema::hasColumn('notifications', 'data')) {
                $payload['data'] = $data;
            }
            NotificationHelper::make($payload);
        } catch (\Throwable $e) {
            Log::warning('Failed to create in-app notification (requestor confirm link): ' . $e->getMessage());
        }
    }

    /**
     * Notify CREaM admin/staff that the requestor confirmed availability
     */
    public function notifyRequestorConfirmed(Reservation $reservation): void
    {
        $requestorName = $reservation->user ? ($reservation->user->first_name . ' ' . $reservation->user->last_name) : 'Unknown User';
        $serviceName = $reservation->service?->service_name ?? 'Unknown Service';

        // Notify admins/staff by email and in-app
        $admins = User::whereIn('role', ['admin', 'staff'])->where('status', 'active')->get();
        foreach ($admins as $admin) {
            // Email
            try {
                if ($admin->email) {
                    $this->sendMailableSafely($admin->email, new RequestorConfirmedToAdmin($reservation), [
                        'action' => 'requestor_confirmed_admin_staff',
                        'reservation_id' => $reservation->reservation_id,
                        'user_id' => $admin->id,
                    ]);
                }
            } catch (\Throwable $e) {
                Log::warning('Failed to send admin/staff email (requestor confirmed): ' . $e->getMessage());
            }

            // In-app notification
            try {
                $message = '<strong>' . e($requestorName) . '</strong> confirmed availability for <strong>' . e($serviceName) . '</strong>';
                $payload = [
                    'user_id' => $admin->id,
                    'reservation_id' => $reservation->reservation_id,
                    'message' => $message,
                    'type' => NotificationHelper::TYPE_UPDATE,
                    'sent_at' => now(),
                ];
                if (Schema::hasColumn('notifications', 'data')) {
                    $payload['data'] = [
                        'service_name' => $serviceName,
                        'schedule_date' => optional($reservation->schedule_date)->format('Y-m-d H:i:s'),
                        'requestor_name' => $requestorName,
                        'venue' => $reservation->custom_venue_name ?? optional($reservation->venue)->name ?? 'N/A',
                        'action' => 'requestor_confirmed',
                    ];
                }
                NotificationHelper::make($payload);
            } catch (\Throwable $e) {
                Log::warning('Failed to create admin/staff in-app notification (requestor confirmed): ' . $e->getMessage());
            }
        }

        // Optional SMS to first admin/staff with phone
        try {
            $adminWithPhone = User::whereIn('role', ['admin', 'staff'])
                ->whereNotNull('phone')
                ->first();
            if ($adminWithPhone && $adminWithPhone->phone) {
                $this->sendSMS(
                    $adminWithPhone->phone,
                    'Requestor confirmed reservation #' . $reservation->reservation_id . ' for ' . $serviceName . ' on ' . optional($reservation->schedule_date)->format('M d, Y h:i A')
                );
            }
        } catch (\Throwable $e) {
            Log::info('Could not send SMS for admin/staff (requestor confirmed): ' . $e->getMessage());
        }
    }

    /**
     * Send notification when priest cancels their already confirmed reservation
     * Notifies admin/staff so they can reassign another priest
     */
    public function notifyPriestCancelledConfirmation(Reservation $reservation, string $reason, $priestId): void
    {
        // Get priest info
        $priest = User::find($priestId);
        $priestName = $priest ? 'Fr. ' . $priest->first_name . ' ' . $priest->last_name : 'A priest';

        // 1. Notify Requestor
        $requestor = $reservation->user;
        if ($requestor) {
            // Send email to requestor
            if ($requestor->email) {
                try {
                    $this->sendMailableSafely($requestor->email, new PriestCancelledConfirmationToRequestor($reservation, $priestName, $reason), [
                        'action' => 'priest_cancelled_confirmation_requestor',
                        'reservation_id' => $reservation->reservation_id,
                        'user_id' => $requestor->id,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send cancellation email to requestor: ' . $e->getMessage());
                }
            }

            // Create in-app notification for requestor
            try {
                $message = "<strong>{$priestName}</strong> has cancelled their confirmation for your reservation: <strong>" . ($reservation->service?->service_name ?? 'Unknown Service') . "</strong> on " . $reservation->schedule_date->format('M d, Y h:i A');

                $notificationData = [
                    'user_id' => $requestor->id,
                    'reservation_id' => $reservation->reservation_id,
                    'message' => $message,
                    'type' => 'Cancellation',
                    'sent_at' => now(),
                ];

                if (Schema::hasColumn('notifications', 'data')) {
                    $notificationData['data'] = json_encode([
                        'reason' => $reason,
                        'priest_name' => $priest ? $priest->first_name . ' ' . $priest->last_name : 'Unknown',
                        'priest_id' => $priestId,
                        'service_name' => $reservation->service?->service_name ?? 'Unknown Service',
                        'schedule_date' => $reservation->schedule_date->format('Y-m-d H:i:s'),
                        'venue' => $reservation->custom_venue_name ?? $reservation->venue?->name ?? 'N/A',
                        'action' => 'priest_cancelled',
                    ]);
                }

                Notification::create($notificationData);
            } catch (\Exception $e) {
                Log::error('Failed to create in-app notification for requestor: ' . $e->getMessage());
            }
        }

        // 2. Notify Admins/Staff for reassignment
        $admins = User::whereIn('role', ['admin', 'staff'])->get();
        foreach ($admins as $admin) {
            if ($admin->email) {
                // Create email notification
                try {
                    $this->sendMailableSafely($admin->email, new PriestCancelledConfirmationToAdmin($reservation, $priestName, $reason), [
                        'action' => 'priest_cancelled_confirmation_admin_staff',
                        'reservation_id' => $reservation->reservation_id,
                        'user_id' => $admin->id,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send cancellation email to admin: ' . $e->getMessage());
                }
            }

            // Create in-app notification for each admin
            try {
                $requestorName = $reservation->user ? ($reservation->user->first_name . ' ' . $reservation->user->last_name) : 'Unknown User';
                $message = "<strong>{$priestName}</strong> cancelled back his reservation submitted by <strong>{$requestorName}</strong> ⚠️";

                $notificationData = [
                    'user_id' => $admin->id,
                    'reservation_id' => $reservation->reservation_id,
                    'message' => $message,
                    'type' => NotificationHelper::TYPE_URGENT,
                    'sent_at' => now(),
                ];

                // Add data field if column exists
                try {
                    if (Schema::hasColumn('notifications', 'data')) {
                        $notificationData['data'] = [
                            'reason' => $reason,
                            'priest_name' => $priest ? $priest->first_name . ' ' . $priest->last_name : 'Unknown',
                            'priest_id' => $priestId,
                            'service_name' => $reservation->service?->service_name ?? 'Unknown Service',
                            'schedule_date' => $reservation->schedule_date->format('Y-m-d H:i:s'),
                            'requestor_name' => $reservation->user ? ($reservation->user->first_name . ' ' . $reservation->user->last_name) : 'Unknown User',
                            'venue' => $reservation->custom_venue_name ?? $reservation->venue?->name ?? 'N/A',
                            'action' => 'cancelled_confirmation',
                        ];
                    }
                } catch (\Exception $e) {
                    Log::info('Data column check failed: ' . $e->getMessage());
                }

                NotificationHelper::make($notificationData);
                Log::info('Cancellation notification created for admin', [
                    'admin_id' => $admin->id,
                    'reservation_id' => $reservation->reservation_id,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create in-app notification for cancellation: ' . $e->getMessage());
            }
        }

        // SMS to admin/staff
        $adminWithPhone = User::whereIn('role', ['admin', 'staff'])
            ->whereNotNull('phone')
            ->first();

        if ($adminWithPhone && $adminWithPhone->phone) {
            $this->sendSMS(
                $adminWithPhone->phone,
                "⚠️ URGENT: {$priestName} CANCELLED confirmed reservation #{$reservation->reservation_id} for " . ($reservation->service?->service_name ?? 'Unknown Service') . " on " . $reservation->schedule_date->format('M d, Y') . ". Please reassign immediately!"
            );
        }
    }

    /**
     * Send mail with recipient validation and failure isolation.
     */
    private function sendMailableSafely(?string $email, object $mailable, array $context = []): void
    {
        if (!$email) {
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Log::warning('Skipping email due to invalid recipient email format', array_merge($context, [
                'email' => $email,
            ]));
            return;
        }

        try {
            Mail::to($email)->send($mailable);
        } catch (\Throwable $e) {
            Log::warning('Failed sending email', array_merge($context, [
                'email' => $email,
                'error' => $e->getMessage(),
            ]));
        }
    }

    /**
     * Send SMS using configured provider
     */
    private function sendSMS(string $phoneNumber, string $message): void
    {
        try {
            $provider = config('services.twilio.enabled') ? 'twilio' : (config('services.semaphore.enabled') ? 'semaphore' : null);

            // Check if ANY SMS is enabled
            if (!$provider) {
                Log::info('SMS disabled - would have sent: ' . $message . ' to ' . $phoneNumber);
                return;
            }

            if ($provider === 'twilio') {
                $sid = config('services.twilio.sid');
                $token = config('services.twilio.token');
                $from = config('services.twilio.from');
                $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";

                $response = Http::withBasicAuth($sid, $token)->asForm()->post($url, [
                    'To' => $this->formatPhoneNumber($phoneNumber),
                    'From' => $from,
                    'Body' => $message,
                ]);

                if ($response->successful()) {
                    Log::info('Twilio SMS sent to ' . $phoneNumber);
                } else {
                    Log::error('Twilio Error: ' . $response->body());
                }
            } 
            elseif ($provider === 'semaphore') {
                $apiKey = config('services.semaphore.api_key');
                $senderName = config('services.semaphore.sender_name', 'CREaM-HNU');

                $response = Http::asForm()->post('https://api.semaphore.co/api/v4/messages', [
                    'apikey' => $apiKey,
                    'number' => $this->formatPhoneNumber($phoneNumber),
                    'message' => $message,
                    'sendername' => $senderName,
                ]);

                if ($response->successful()) {
                    Log::info('Semaphore SMS sent to ' . $phoneNumber);
                } else {
                    Log::error('Semaphore Error: ' . $response->body());
                }
            }

        } catch (\Exception $e) {
            Log::error('SMS sending exception: ' . $e->getMessage());
        }
    }

    /**
     * Format phone number for Philippine format (+639XXXXXXXXX)
     */
    private function formatPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Convert to +63 format
        if (str_starts_with($phone, '0')) {
            $phone = '63' . substr($phone, 1);
        } elseif (!str_starts_with($phone, '63')) {
            $phone = '63' . $phone;
        }

        return '+' . $phone;
    }

    /**
     * Send notification to requestor when priest confirms
     * (Simplified version for priest confirmation)
     */
    public function notifyRequestorPriestConfirmed(Reservation $reservation, User $priest): void
    {
        $priestName = 'Fr. ' . $priest->first_name . ' ' . $priest->last_name;
        $serviceName = $reservation->service?->service_name ?? 'Unknown Service';

        // Email to requestor
        if ($reservation->user && $reservation->user->email) {
            $this->sendMailableSafely($reservation->user->email, new \App\Mail\ReservationConfirmed($reservation, $priestName), [
                'action' => 'requestor_priest_confirmed_requestor',
                'reservation_id' => $reservation->reservation_id,
                'user_id' => $reservation->user_id,
            ]);
        }

        // In-app notification
        try {
            $message = "<strong>{$priestName}</strong> has confirmed your reservation for <strong>{$serviceName}</strong>";

            $notificationData = [
                'user_id' => $reservation->user_id,
                'reservation_id' => $reservation->reservation_id,
                'message' => $message,
                'type' => 'Approved',
                'sent_at' => now(),
            ];

            if (Schema::hasColumn('notifications', 'data')) {
                $notificationData['data'] = json_encode([
                    'priest_name' => $priest->first_name . ' ' . $priest->last_name,
                    'service_name' => $serviceName,
                    'schedule_date' => $reservation->schedule_date->format('Y-m-d H:i:s'),
                    'action' => 'priest_confirmed',
                ]);
            }

            Notification::create($notificationData);
        } catch (\Exception $e) {
            Log::error('Failed to create requestor confirmation notification: ' . $e->getMessage());
        }

        // SMS to requestor
        if ($reservation->user && $reservation->user->phone) {
            $this->sendSMS(
                $reservation->user->phone,
                "{$priestName} confirmed your {$serviceName} on " . $reservation->schedule_date->format('M d, Y h:i A')
            );
        }
    }

    /**
     * Send notification to adviser when priest confirms
     */
    public function notifyAdviserPriestConfirmed(Reservation $reservation, User $priest): void
    {
        if (!$reservation->organization || !$reservation->organization->adviser) {
            return;
        }

        $adviser = $reservation->organization->adviser;
        $priestName = 'Fr. ' . $priest->first_name . ' ' . $priest->last_name;

        // Email to adviser
        if ($adviser->email) {
            try {
                $this->sendMailableSafely($adviser->email, new \App\Mail\AdviserPriestConfirmed($reservation, $priestName, $adviser), [
                    'action' => 'adviser_priest_confirmed',
                    'reservation_id' => $reservation->reservation_id,
                    'user_id' => $adviser->id,
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to send priest confirmation email to adviser: ' . $e->getMessage());
            }
        }

        // In-app notification
        try {
            $orgName = $reservation->organization?->org_name ?? 'Unknown Organization';
            $message = "<strong>{$priestName}</strong> confirmed the reservation from <strong>{$orgName}</strong>";

            $notificationData = [
                'user_id' => $adviser->id,
                'reservation_id' => $reservation->reservation_id,
                'message' => $message,
                'type' => 'Update',
                'sent_at' => now(),
            ];

            if (Schema::hasColumn('notifications', 'data')) {
                $notificationData['data'] = json_encode([
                    'priest_name' => $priest->first_name . ' ' . $priest->last_name,
                    'organization' => $orgName,
                    'service_name' => $reservation->service?->service_name ?? 'Unknown Service',
                    'action' => 'priest_confirmed',
                ]);
            }

            Notification::create($notificationData);
        } catch (\Exception $e) {
            Log::error('Failed to create adviser confirmation notification: ' . $e->getMessage());
        }
    }

    /**
     * Send notification to requestor when priest is reassigned
     */
    public function notifyRequestorPriestReassigned(Reservation $reservation, User $oldPriest, User $newPriest): void
    {
        $oldPriestName = 'Fr. ' . $oldPriest->first_name . ' ' . $oldPriest->last_name;
        $newPriestName = 'Fr. ' . $newPriest->first_name . ' ' . $newPriest->last_name;
        $serviceName = $reservation->service?->service_name ?? 'Unknown Service';

        // Email to requestor
        if ($reservation->user && $reservation->user->email) {
            try {
                $this->sendMailableSafely($reservation->user->email, new RequestorPriestReassigned($reservation, $oldPriestName, $newPriestName), [
                    'action' => 'requestor_priest_reassigned',
                    'reservation_id' => $reservation->reservation_id,
                    'user_id' => $reservation->user_id,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send priest reassigned email to requestor: ' . $e->getMessage());
            }
        }

        // In-app notification
        try {
            $message = "Priest reassigned: <strong>{$newPriestName}</strong> will now handle your <strong>{$serviceName}</strong> reservation";

            $notificationData = [
                'user_id' => $reservation->user_id,
                'reservation_id' => $reservation->reservation_id,
                'message' => $message,
                'type' => 'Update',
                'sent_at' => now(),
            ];

            if (Schema::hasColumn('notifications', 'data')) {
                $notificationData['data'] = json_encode([
                    'old_priest' => $oldPriest->first_name . ' ' . $oldPriest->last_name,
                    'new_priest' => $newPriest->first_name . ' ' . $newPriest->last_name,
                    'action' => 'priest_reassigned',
                ]);
            }

            Notification::create($notificationData);
        } catch (\Exception $e) {
            Log::error('Failed to create requestor reassignment notification: ' . $e->getMessage());
        }
    }

    /**
     * Send notification to adviser when priest is reassigned
     */
    public function notifyAdviserPriestReassigned(Reservation $reservation, User $oldPriest, User $newPriest): void
    {
        if (!$reservation->organization || !$reservation->organization->adviser) {
            return;
        }

        $adviser = $reservation->organization->adviser;
        $oldPriestName = 'Fr. ' . $oldPriest->first_name . ' ' . $oldPriest->last_name;
        $newPriestName = 'Fr. ' . $newPriest->first_name . ' ' . $newPriest->last_name;

        // In-app notification
        try {
            $orgName = $reservation->organization?->org_name ?? 'Unknown Organization';
            $message = "Priest reassigned from <strong>{$oldPriestName}</strong> to <strong>{$newPriestName}</strong> for {$orgName} reservation";

            $notificationData = [
                'user_id' => $adviser->id,
                'reservation_id' => $reservation->reservation_id,
                'message' => $message,
                'type' => 'Update',
                'sent_at' => now(),
            ];

            if (Schema::hasColumn('notifications', 'data')) {
                $notificationData['data'] = json_encode([
                    'old_priest' => $oldPriest->first_name . ' ' . $oldPriest->last_name,
                    'new_priest' => $newPriest->first_name . ' ' . $newPriest->last_name,
                    'organization' => $orgName,
                    'action' => 'priest_reassigned',
                ]);
            }

            Notification::create($notificationData);
        } catch (\Exception $e) {
            Log::error('Failed to create adviser reassignment notification: ' . $e->getMessage());
        }
    }

    /**
     * Send notification to new priest about assignment
     * (Wrapper for notifyPriestAssigned but includes logging)
     */
    public function notifyPriestAssignment(Reservation $reservation, User $priest): void
    {
        $priestName = 'Fr. ' . $priest->first_name . ' ' . $priest->last_name;
        $serviceName = $reservation->service?->service_name ?? 'Unknown Service';

        // Email to priest
        if ($priest->email) {
            try {
                $this->sendMailableSafely($priest->email, new \App\Mail\ReservationPriestAssigned($reservation), [
                    'action' => 'priest_assignment_notification',
                    'reservation_id' => $reservation->reservation_id,
                    'user_id' => $priest->id,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send priest assignment email: ' . $e->getMessage());
            }
        }

        // In-app notification
        try {
            $message = "You have been assigned to <strong>{$serviceName}</strong> on " . $reservation->schedule_date->format('M d, Y h:i A');

            $notificationData = [
                'user_id' => $priest->id,
                'reservation_id' => $reservation->reservation_id,
                'message' => $message,
                'type' => 'Assignment',
                'sent_at' => now(),
            ];

            if (Schema::hasColumn('notifications', 'data')) {
                $notificationData['data'] = json_encode([
                    'service_name' => $serviceName,
                    'schedule_date' => $reservation->schedule_date->format('Y-m-d H:i:s'),
                    'action' => 'priest_assignment',
                ]);
            }

            Notification::create($notificationData);
            Log::info('Priest assignment notification created', [
                'priest_id' => $priest->id,
                'reservation_id' => $reservation->reservation_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create priest assignment notification: ' . $e->getMessage());
        }

        // SMS to priest
        if ($priest->phone) {
            $this->sendSMS(
                $priest->phone,
                "New service assignment: {$serviceName} on " . $reservation->schedule_date->format('M d, Y h:i A') . ". Please confirm in eReligiousServices."
            );
        }
    }

    /**
     * Send notification when adviser cancels approval
     */
    public function notifyApprovalCancelled(Reservation $reservation, string $reason): void
    {
        $serviceName = $reservation->service?->service_name ?? 'Unknown Service';

        // In-app notification for requestor
        try {
            $message = "The approval for your reservation for <strong>{$serviceName}</strong> has been cancelled by your adviser";
            $notificationData = [
                'user_id' => $reservation->user_id,
                'reservation_id' => $reservation->reservation_id,
                'message' => $message,
                'type' => NotificationHelper::TYPE_UPDATE,
                'sent_at' => now(),
            ];
            if (Schema::hasColumn('notifications', 'data')) {
                $notificationData['data'] = [
                    'reason' => $reason,
                    'action' => 'approval_cancelled',
                ];
            }
            NotificationHelper::make($notificationData);
        } catch (\Exception $e) {
            Log::warning('Failed to create requestor in-app notification (approval cancelled): ' . $e->getMessage());
        }

        // SMS to requestor
        if ($reservation->user && $reservation->user->phone) {
            $this->sendSMS(
                $reservation->user->phone,
                "Your reservation for {$serviceName} approval has been cancelled. Reason: {$reason}. Your reservation is now pending review again."
            );
        }

        // Notify CREaM staff
        $staff = User::where('role', 'staff')->get();
        foreach ($staff as $member) {
            try {
                $staffMessage = "Adviser has cancelled approval for reservation #{$reservation->reservation_id} - {$serviceName}";
                $staffNotificationData = [
                    'user_id' => $member->id,
                    'reservation_id' => $reservation->reservation_id,
                    'message' => $staffMessage,
                    'type' => NotificationHelper::TYPE_URGENT,
                    'sent_at' => now(),
                ];
                if (Schema::hasColumn('notifications', 'data')) {
                    $staffNotificationData['data'] = [
                        'reason' => $reason,
                        'action' => 'approval_cancelled',
                        'requestor' => $reservation->user ? ($reservation->user->first_name . ' ' . $reservation->user->last_name) : 'Unknown User',
                    ];
                }
                NotificationHelper::make($staffNotificationData);
            } catch (\Exception $e) {
                Log::warning('Failed to create staff in-app notification (approval cancelled): ' . $e->getMessage());
            }
        }

        // Notify admin users
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            try {
                $adminMessage = "Adviser has cancelled approval for reservation #{$reservation->reservation_id} - {$serviceName}";
                $adminNotificationData = [
                    'user_id' => $admin->id,
                    'reservation_id' => $reservation->reservation_id,
                    'message' => $adminMessage,
                    'type' => NotificationHelper::TYPE_URGENT,
                    'sent_at' => now(),
                ];
                if (Schema::hasColumn('notifications', 'data')) {
                    $adminNotificationData['data'] = [
                        'reason' => $reason,
                        'action' => 'approval_cancelled',
                        'requestor' => $reservation->user ? ($reservation->user->first_name . ' ' . $reservation->user->last_name) : 'Unknown User',
                    ];
                }
                NotificationHelper::make($adminNotificationData);
            } catch (\Exception $e) {
                Log::warning('Failed to create admin in-app notification (approval cancelled): ' . $e->getMessage());
            }
        }
    }

    /**
     * Send notification when admin gives final approval after all priests confirmed
     * Notifies requestor that their reservation is fully approved
     */
    public function notifyFinalApproval(Reservation $reservation): void
    {
        $requestor = $reservation->user;
        $requestorName = $requestor ? ($requestor->first_name . ' ' . $requestor->last_name) : 'Unknown User';
        
        // Build priest names list
        $priestNames = $reservation->priests->map(function ($priest) {
            return 'Fr. ' . $priest->first_name . ' ' . $priest->last_name;
        })->implode(', ');
        
        if (empty($priestNames) && $reservation->external_priest_name) {
            $priestNames = $reservation->external_priest_name . ' (External)';
        }
        
        $venueName = $reservation->custom_venue_name ?? $reservation->venue?->name ?? 'N/A';
        
        // Email notification to requestor
        if ($requestor && $requestor->email) {
            try {
                $this->sendMailableSafely($requestor->email, new \App\Mail\ReservationFinalApproved($reservation, $priestNames), [
                    'action' => 'final_approval_requestor',
                    'reservation_id' => $reservation->reservation_id,
                    'user_id' => $requestor->id,
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to send final approval email to requestor: ' . $e->getMessage());
            }
        }

        // In-app notification to requestor
        try {
            $serviceName = $reservation->service?->service_name ?? 'Unknown Service';
            $message = "Your reservation for {$serviceName} on " .
                $reservation->schedule_date->format('F d, Y') .
                " has been fully approved by the CREaM Office.";

            NotificationHelper::make([
                'user_id' => $requestor->id,
                'reservation_id' => $reservation->reservation_id,
                'message' => $message,
                'type' => NotificationHelper::TYPE_SUCCESS,
                'sent_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to create requestor final approval notification: ' . $e->getMessage());
        }

        // Notify advisers of the organizations involved
        foreach ($reservation->organizations as $organization) {
            if ($organization->adviser && $organization->adviser->email) {
                try {
                    $this->sendMailableSafely($organization->adviser->email, new ReservationFinalApprovalToAdviser($reservation), [
                        'action' => 'final_approval_adviser',
                        'reservation_id' => $reservation->reservation_id,
                        'user_id' => $organization->adviser->id,
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Failed to send final approval email to adviser: ' . $e->getMessage());
                }

                // In-app notification to adviser
                try {
                    NotificationHelper::make([
                        'user_id' => $organization->adviser->id,
                        'reservation_id' => $reservation->reservation_id,
                        'message' => "Reservation for " . ($reservation->service?->service_name ?? 'Unknown Service') . " by {$requestorName} has been fully approved.",
                        'type' => NotificationHelper::TYPE_SUCCESS,
                        'sent_at' => now(),
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Failed to create adviser final approval notification: ' . $e->getMessage());
                }
            }
        }

        // Notify priests involved
        foreach ($reservation->priests as $priest) {
            if ($priest->email) {
                try {
                    $this->sendMailableSafely($priest->email, new ReservationFinalApprovalToPriest($reservation, $priest->first_name . ' ' . $priest->last_name), [
                        'action' => 'final_approval_priest',
                        'reservation_id' => $reservation->reservation_id,
                        'user_id' => $priest->id,
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Failed to send final approval email to priest: ' . $e->getMessage());
                }

                // In-app notification to priest
                try {
                    NotificationHelper::make([
                        'user_id' => $priest->id,
                        'reservation_id' => $reservation->reservation_id,
                        'message' => "Reservation for " . ($reservation->service?->service_name ?? 'Unknown Service') . " on " . $reservation->schedule_date->format('F d, Y') . " has been fully approved.",
                        'type' => NotificationHelper::TYPE_SUCCESS,
                        'sent_at' => now(),
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Failed to create priest final approval notification: ' . $e->getMessage());
                }
            }
        }
    }
}

