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
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

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
        // Email to requestor (confirmation)
        if ($reservation->user->email) {
            Mail::to($reservation->user->email)
                ->send(new ReservationSubmitted($reservation));
        }

        // In-app notification to requestor
        try {
            $message = "We received your reservation request for <strong>{$reservation->service->service_name}</strong>";
            $notificationData = [
                'user_id' => $reservation->user_id,
                'reservation_id' => $reservation->reservation_id,
                'message' => $message,
                'type' => 'Update',
                'sent_at' => now(),
            ];
            if (Schema::hasColumn('notifications', 'data')) {
                $notificationData['data'] = json_encode([
                    'service_name' => $reservation->service->service_name,
                    'schedule_date' => optional($reservation->schedule_date)->format('Y-m-d H:i:s'),
                    'action' => 'request_submitted',
                ]);
            }
            Notification::create($notificationData);
        } catch (\Exception $e) {
            Log::warning('Failed to create requestor in-app notification (submitted): ' . $e->getMessage());
        }

        // Email + in-app to organization adviser
        if ($reservation->organization && $reservation->organization->adviser) {
            $adviser = $reservation->organization->adviser;
            if ($adviser->email) {
                Mail::to($adviser->email)
                    ->send(new ReservationSubmitted($reservation));
            }

            // SMS to adviser
            if ($adviser->phone) {
                $this->sendSMS(
                    $adviser->phone,
                    "New reservation request from {$reservation->user->first_name} {$reservation->user->last_name} for {$reservation->service->service_name} on " . $reservation->schedule_date->format('M d, Y h:i A') . ". Please review in eReligiousServices."
                );
            }

            // In-app notification to adviser
            try {
                $message = "New reservation request awaiting your review: <strong>{$reservation->service->service_name}</strong>";
                $notificationData = [
                    'user_id' => $adviser->id,
                    'reservation_id' => $reservation->reservation_id,
                    'message' => $message,
                    'type' => 'Update',
                    'sent_at' => now(),
                ];
                if (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'data')) {
                    $notificationData['data'] = json_encode([
                        'service_name' => $reservation->service->service_name,
                        'schedule_date' => optional($reservation->schedule_date)->format('Y-m-d H:i:s'),
                        'action' => 'request_submitted',
                    ]);
                }
                Notification::create($notificationData);
            } catch (\Exception $e) {
                Log::warning('Failed to create adviser in-app notification (submitted): ' . $e->getMessage());
            }
        }
    }

    /**
     * Send notification when adviser approves
     */
    public function notifyAdviserApproved(Reservation $reservation, string $remarks = ''): void
    {
        // Email to requestor
        if ($reservation->user->email) {
            Mail::to($reservation->user->email)
                ->send(new ReservationAdviserApproved($reservation, $remarks));
        }

        // Email to CREaM Admin/Staff
        $admins = User::whereIn('role', ['admin', 'staff'])->get();
        foreach ($admins as $admin) {
            if ($admin->email) {
                Mail::to($admin->email)
                    ->send(new ReservationAdviserApproved($reservation, $remarks));
            }
        }

        // SMS to requestor
        if ($reservation->user->phone) {
            $this->sendSMS(
                $reservation->user->phone,
                "Good news! Your reservation for {$reservation->service->service_name} has been approved by your organization adviser. Awaiting final CREaM approval."
            );
        }

        // In-app notification for requestor
        try {
            $message = "Your reservation was approved by your adviser";
            $notificationData = [
                'user_id' => $reservation->user_id,
                'reservation_id' => $reservation->reservation_id,
                'message' => $message,
                'type' => 'Update',
                'sent_at' => now(),
            ];
            if (Schema::hasColumn('notifications', 'data')) {
                $notificationData['data'] = json_encode([
                    'service_name' => $reservation->service->service_name,
                    'schedule_date' => $reservation->schedule_date->format('Y-m-d H:i:s'),
                    'action' => 'adviser_approved',
                ]);
            }
            Notification::create($notificationData);
        } catch (\Exception $e) {
            Log::warning('Failed to create requestor in-app notification (adviser approved): ' . $e->getMessage());
        }

        // In-app notification for CREaM Admin/Staff
        try {
            $admins = User::whereIn('role', ['admin', 'staff'])->get();
            foreach ($admins as $admin) {
                $notificationData = [
                    'user_id' => $admin->id,
                    'reservation_id' => $reservation->reservation_id,
                    'message' => 'Adviser approved a reservation. Proceed to contact requestor and assign a priest.',
                    'type' => 'Update',
                    'sent_at' => now(),
                ];
                if (Schema::hasColumn('notifications', 'data')) {
                    $notificationData['data'] = json_encode([
                        'action' => 'adviser_approved',
                        'remarks' => $remarks,
                        'service_name' => $reservation->service->service_name,
                        'schedule_date' => $reservation->schedule_date->format('Y-m-d H:i:s'),
                    ]);
                }
                Notification::create($notificationData);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to create admin/staff in-app notification (adviser approved): ' . $e->getMessage());
        }
    }

    /**
     * Notify requestor with confirmation link after staff contact
     */
    public function notifyRequestorConfirmation(Reservation $reservation, string $confirmationUrl): void
    {
        // Email to requestor with the confirmation URL
        if ($reservation->user && $reservation->user->email) {
            try {
                \Illuminate\Support\Facades\Mail::raw(
                    "The CREaM Staff has contacted you regarding your reservation.\n\n" .
                    "Please confirm your reservation by clicking the link below:\n" .
                    $confirmationUrl . "\n\n" .
                    "If you did not request this, you can ignore this message.",
                    function ($message) use ($reservation) {
                        $message->to($reservation->user->email)
                            ->subject('Please confirm your reservation');
                    }
                );
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to send requestor confirmation email: ' . $e->getMessage());
            }
        }

        // In-app notification to requestor
        try {
            $message = "Please confirm your reservation via the link sent to your email.";
            Notification::create([
                'user_id' => $reservation->user_id,
                'reservation_id' => $reservation->reservation_id,
                'message' => $message,
                'type' => 'Action Required',
                'sent_at' => now(),
                'data' => json_encode([
                    'action' => 'requestor_confirm_link_sent',
                ])
            ]);
        } catch (\Exception $e) {
            // ignore
        }
    }

    /**
     * Notify admins/staff that the requestor confirmed their availability
     */
    public function notifyRequestorConfirmed(Reservation $reservation): void
    {
        $admins = User::whereIn('role', ['admin', 'staff'])->get();

        foreach ($admins as $admin) {
            if ($admin->email) {
                try {
                    \Illuminate\Support\Facades\Mail::raw(
                        "The requestor has confirmed availability for reservation #{$reservation->reservation_id}.\n\n" .
                        "Service: {$reservation->service->service_name}\n" .
                        "Schedule: " . $reservation->schedule_date->format('F d, Y h:i A') . "\n\n" .
                        "You may proceed to assign a priest.",
                        function ($message) use ($admin) {
                            $message->to($admin->email)
                                ->subject('Requestor confirmed availability');
                        }
                    );
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning('Failed to notify admin/staff of requestor confirmation: ' . $e->getMessage());
                }
            }

            // In-app notification
            try {
                Notification::create([
                    'user_id' => $admin->id,
                    'reservation_id' => $reservation->reservation_id,
                    'message' => 'Requestor confirmed availability. Proceed to assign a priest.',
                    'type' => 'Update',
                    'sent_at' => now(),
                    'data' => json_encode(['action' => 'requestor_confirmed'])
                ]);
            } catch (\Exception $e) {
                // ignore
            }
        }
    }

    /**
     * Send notification when adviser rejects
     */
    public function notifyAdviserRejected(Reservation $reservation, string $reason): void
    {
        // Email to requestor
        if ($reservation->user->email) {
            Mail::to($reservation->user->email)
                ->send(new ReservationAdviserRejected($reservation, $reason));
        }

        // SMS to requestor
        if ($reservation->user->phone) {
            $this->sendSMS(
                $reservation->user->phone,
                "Your reservation for {$reservation->service->service_name} was not approved by your adviser. Reason: {$reason}"
            );
        }

        // Notify CREaM staff for record keeping
        $staff = User::where('role', 'staff')->get();
        foreach ($staff as $member) {
            if ($member->email) {
                Mail::to($member->email)
                    ->send(new ReservationAdviserRejected($reservation, $reason));
            }
        }

        // In-app notification for requestor
        try {
            $message = "Your reservation was not approved by your adviser";
            $notificationData = [
                'user_id' => $reservation->user_id,
                'reservation_id' => $reservation->reservation_id,
                'message' => $message,
                'type' => 'Update',
                'sent_at' => now(),
            ];
            if (Schema::hasColumn('notifications', 'data')) {
                $notificationData['data'] = json_encode([
                    'reason' => $reason,
                    'action' => 'adviser_rejected',
                ]);
            }
            Notification::create($notificationData);
        } catch (\Exception $e) {
            Log::warning('Failed to create requestor in-app notification (adviser rejected): ' . $e->getMessage());
        }

        // In-app notification for CREaM Admin/Staff
        try {
            $admins = User::whereIn('role', ['admin', 'staff'])->get();
            foreach ($admins as $admin) {
                $notificationData = [
                    'user_id' => $admin->id,
                    'reservation_id' => $reservation->reservation_id,
                    'message' => 'Adviser rejected a reservation. No further action required unless appealed.',
                    'type' => 'Update',
                    'sent_at' => now(),
                ];
                if (Schema::hasColumn('notifications', 'data')) {
                    $notificationData['data'] = json_encode([
                        'reason' => $reason,
                        'action' => 'adviser_rejected',
                    ]);
                }
                Notification::create($notificationData);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to create admin/staff in-app notification (adviser rejected): ' . $e->getMessage());
        }
    }

    /**
     * Send notification when priest is assigned
     */
    public function notifyPriestAssigned(Reservation $reservation): void
    {
        // Create in-app notification for priest
        if ($reservation->officiant) {
            try {
                $message = "You have been assigned to officiate {$reservation->service->service_name} on " .
                          $reservation->schedule_date->format('M d, Y h:i A') .
                          ". Please review and confirm your availability.";

                $notificationData = [
                    'user_id' => $reservation->officiant_id,
                    'reservation_id' => $reservation->reservation_id,
                    'message' => $message,
                    'type' => 'Assignment',
                    'sent_at' => now(),
                ];

                // Add data field if column exists
                try {
                    if (Schema::hasColumn('notifications', 'data')) {
                        $notificationData['data'] = json_encode([
                            'service_name' => $reservation->service->service_name,
                            'requestor_name' => $reservation->user->first_name . ' ' . $reservation->user->last_name,
                            'schedule_date' => $reservation->schedule_date->toDateTimeString(),
                            'venue' => $reservation->custom_venue_name ?? $reservation->venue->name ?? 'N/A',
                            'admin_remarks' => $reservation->history()
                                ->where('action', 'priest_reassigned')
                                ->orWhere('action', 'priest_assigned')
                                ->latest()
                                ->value('remarks'),
                        ]);
                    }
                } catch (\Exception $e) {
                    // Column doesn't exist or error, skip data field
                    Log::warning('Could not add data to priest assignment notification: ' . $e->getMessage());
                }

                Notification::create($notificationData);
                Log::info("In-app notification created for priest (ID: {$reservation->officiant_id}) for reservation {$reservation->reservation_id}");
            } catch (\Exception $e) {
                Log::error('Failed to create in-app notification for priest assignment: ' . $e->getMessage());
            }
        }

        // Email to priest
        if ($reservation->officiant && $reservation->officiant->email) {
            Mail::to($reservation->officiant->email)
                ->send(new ReservationPriestAssigned($reservation));
        }

        // SMS to priest
        if ($reservation->officiant && $reservation->officiant->phone) {
            $this->sendSMS(
                $reservation->officiant->phone,
                "You have been assigned to officiate {$reservation->service->service_name} on " . $reservation->schedule_date->format('M d, Y h:i A') . " at {$reservation->venue->name}. Please confirm your availability in eReligiousServices."
            );
        }

        // Email to requestor (update)
        if ($reservation->user->email) {
            Mail::to($reservation->user->email)
                ->send(new ReservationPriestAssigned($reservation));
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
                Mail::to($admin->email)
                    ->send(new ReservationPriestDeclined($reservation, $reason));
            }

            // Create in-app notification for each admin
            try {
                $priestName = $declinedPriest ? 'Fr. ' . $declinedPriest->first_name . ' ' . $declinedPriest->last_name : 'A priest';
                $message = "<strong>{$priestName}</strong> declined the reservation you assigned";

                $notificationData = [
                    'user_id' => $admin->id,
                    'reservation_id' => $reservation->reservation_id,
                    'message' => $message,
                    'type' => 'Priest Declined',
                    'sent_at' => now(),
                ];

                // Add data field if column exists (for future use)
                try {
                    if (Schema::hasColumn('notifications', 'data')) {
                        $notificationData['data'] = json_encode([
                            'reason' => $reason,
                            'priest_name' => $declinedPriest ? $declinedPriest->first_name . ' ' . $declinedPriest->last_name : 'Unknown',
                            'priest_id' => $declinedPriest ? $declinedPriest->id : null,
                            'service_name' => $reservation->service->service_name,
                            'schedule_date' => $reservation->schedule_date->format('Y-m-d H:i:s'),
                            'requestor_name' => $reservation->user->first_name . ' ' . $reservation->user->last_name,
                            'venue' => $reservation->custom_venue_name ?? $reservation->venue->name ?? 'N/A',
                        ]);
                    }
                } catch (\Exception $e) {
                    // Data column doesn't exist, that's okay
                    Log::info('Data column check failed: ' . $e->getMessage());
                }

                $createdNotification = Notification::create($notificationData);
                Log::info('Notification created successfully', [
                    'notification_id' => $createdNotification->notification_id,
                    'user_id' => $admin->id,
                    'type' => 'Priest Declined',
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

        // SMS to admin/staff
        $adminWithPhone = User::whereIn('role', ['admin', 'staff'])
            ->whereNotNull('phone')
            ->first();

        if ($adminWithPhone && $adminWithPhone->phone) {
            $this->sendSMS(
                $adminWithPhone->phone,
                "URGENT: Priest declined reservation #{$reservation->reservation_id} for {$reservation->service->service_name} on " . $reservation->schedule_date->format('M d, Y') . ". Please assign another presider."
            );
        }
    }

    /**
     * Send notification when reservation is cancelled
     */
    public function notifyCancellation(Reservation $reservation, string $reason, string $cancelledBy): void
    {
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
                Mail::to($recipient->email)
                    ->send(new ReservationCancelled($reservation, $reason, $cancelledBy));
            }

            // Send SMS to key parties
            if ($recipient->phone && in_array($recipient->id, [
                $reservation->user_id,
                $reservation->officiant_id,
                $reservation->organization->adviser_id ?? null
            ])) {
                $this->sendSMS(
                    $recipient->phone,
                    "Reservation for {$reservation->service->service_name} on " . $reservation->schedule_date->format('M d, Y') . " has been cancelled. Reason: {$reason}"
                );
            }
        }

        // In-app notification to requestor
        try {
            $message = "Your reservation was cancelled";
            Notification::create([
                'user_id' => $reservation->user_id,
                'reservation_id' => $reservation->reservation_id,
                'message' => $message,
                'type' => 'Update',
                'sent_at' => now(),
                'data' => json_encode(['reason' => $reason, 'cancelled_by' => $cancelledBy])
            ]);
        } catch (\Exception $e) {
            // ignore
        }

        // In-app notifications to other parties (adviser, priest, admins/staff)
        try {
            $notifiableUsers = collect($recipients)
                ->pluck('id')
                ->filter()
                ->unique()
                ->reject(fn ($id) => $id == $reservation->user_id) // avoid duplicate for requestor
                ->values();

            foreach ($notifiableUsers as $uid) {
                $notificationData = [
                    'user_id' => $uid,
                    'reservation_id' => $reservation->reservation_id,
                    'message' => 'A reservation you are involved with was cancelled.',
                    'type' => 'Update',
                    'sent_at' => now(),
                ];
                if (Schema::hasColumn('notifications', 'data')) {
                    $notificationData['data'] = json_encode([
                        'reason' => $reason,
                        'cancelled_by' => $cancelledBy,
                        'action' => 'reservation_cancelled',
                    ]);
                }
                Notification::create($notificationData);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to create in-app notifications for cancellation: ' . $e->getMessage());
        }
    }

    /**
     * Send follow-up notification to adviser (unnoticed request > 24 hours)
     */
    public function notifyAdviserFollowUp(Reservation $reservation): void
    {
        if ($reservation->organization && $reservation->organization->adviser) {
            $adviser = $reservation->organization->adviser;

            // Email reminder
            if ($adviser->email) {
                Mail::to($adviser->email)
                    ->send(new ReservationSubmitted($reservation));
            }

            // SMS reminder
            if ($adviser->phone) {
                $this->sendSMS(
                    $adviser->phone,
                    "REMINDER: Reservation request from {$reservation->user->first_name} {$reservation->user->last_name} for {$reservation->service->service_name} is still pending your approval. Please review."
                );
            }
        }

        // Notify CREaM Staff that follow-up was sent
        $staff = User::where('role', 'staff')->get();
        foreach ($staff as $member) {
            if ($member->email) {
                Mail::raw(
                    "Follow-up sent to adviser for Reservation #{$reservation->reservation_id}. Original request date: {$reservation->created_at->format('M d, Y h:i A')}",
                    function ($message) use ($member) {
                        $message->to($member->email)
                            ->subject('Follow-up Sent - Unnoticed Reservation Request');
                    }
                );
            }
        }
    }

    /**
     * Send notification when priest confirms their assignment
     * Notifies admin/staff that priest has confirmed availability
     */
    public function notifyPriestConfirmed(Reservation $reservation, $priestId): void
    {
        // Get priest info
        $priest = User::find($priestId);
        $priestName = $priest ? 'Fr. ' . $priest->first_name . ' ' . $priest->last_name : 'A priest';
        $requestorName = $reservation->user->first_name . ' ' . $reservation->user->last_name;

        // Email to CREaM Admin/Staff
        $admins = User::whereIn('role', ['admin', 'staff'])->get();
        foreach ($admins as $admin) {
            if ($admin->email) {
                // Create simple email notification
                Mail::raw(
                    "Good news!\n\n" .
                    "{$priestName} has CONFIRMED their availability for the following reservation:\n\n" .
                    "Service: {$reservation->service->service_name}\n" .
                    "Date & Time: {$reservation->schedule_date->format('F d, Y - h:i A')}\n" .
                    "Venue: " . ($reservation->custom_venue_name ?? $reservation->venue->name ?? 'N/A') . "\n" .
                    "Requestor: {$requestorName}\n\n" .
                    "✓ The priest has confirmed their availability and the reservation is now approved.\n" .
                    "No further action required.\n\n" .
                    "Please check the eReligiousServices system for details.\n\n" .
                    "---\n" .
                    "CREaM - eReligiousServices Management System\n" .
                    "Holy Name University",
                    function ($message) use ($admin, $priestName, $reservation) {
                        $message->to($admin->email)
                            ->subject("✓ {$priestName} Confirmed Availability - Reservation #{$reservation->reservation_id}");
                    }
                );
            }

            // Create in-app notification for each admin
            try {
                $message = "<strong>{$priestName}</strong> approved the reservation from <strong>{$requestorName}</strong>";

                $notificationData = [
                    'user_id' => $admin->id,
                    'reservation_id' => $reservation->reservation_id,
                    'message' => $message,
                    'type' => 'Update',
                    'sent_at' => now(),
                ];

                // Add data field if column exists
                try {
                    if (Schema::hasColumn('notifications', 'data')) {
                        $notificationData['data'] = json_encode([
                            'priest_name' => $priest ? $priest->first_name . ' ' . $priest->last_name : 'Unknown',
                            'priest_id' => $priestId,
                            'service_name' => $reservation->service->service_name,
                            'schedule_date' => $reservation->schedule_date->format('Y-m-d H:i:s'),
                            'requestor_name' => $requestorName,
                            'venue' => $reservation->custom_venue_name ?? $reservation->venue->name ?? 'N/A',
                            'action' => 'priest_confirmed',
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::info('Data column check failed: ' . $e->getMessage());
                }

                Notification::create($notificationData);
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
            $this->sendSMS(
                $adminWithPhone->phone,
                "GOOD NEWS: {$priestName} confirmed availability for {$reservation->service->service_name} on " . $reservation->schedule_date->format('M d, Y') . ". Reservation approved!"
            );
        }

        // Notify requestor in-app
        try {
            $message = "A priest confirmed your reservation";
            $notificationData = [
                'user_id' => $reservation->user_id,
                'reservation_id' => $reservation->reservation_id,
                'message' => $message,
                'type' => 'Update',
                'sent_at' => now(),
            ];
            if (Schema::hasColumn('notifications', 'data')) {
                $notificationData['data'] = json_encode([
                    'priest_id' => $priestId,
                    'action' => 'priest_confirmed',
                ]);
            }
            Notification::create($notificationData);
        } catch (\Exception $e) {
            Log::warning('Failed to create requestor in-app notification (priest confirmed): ' . $e->getMessage());
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
                // Create simple email notification
                Mail::raw(
                    "Good news!\n\n" .
                    "{$priestName} has restored their assignment for the following reservation:\n\n" .
                    "Service: {$reservation->service->service_name}\n" .
                    "Date & Time: {$reservation->schedule_date->format('F d, Y - h:i A')}\n" .
                    "Venue: " . ($reservation->custom_venue_name ?? $reservation->venue->name ?? 'N/A') . "\n" .
                    "Requestor: {$reservation->user->first_name} {$reservation->user->last_name}\n\n" .
                    "The priest previously declined this assignment but has now undone their decline.\n" .
                    "The priest will need to confirm their availability.\n\n" .
                    "Please check the eReligiousServices system for details.\n\n" .
                    "---\n" .
                    "CREaM - eReligiousServices Management System\n" .
                    "Holy Name University",
                    function ($message) use ($admin, $priestName, $reservation) {
                        $message->to($admin->email)
                            ->subject("✓ {$priestName} Restored Assignment - Reservation #{$reservation->reservation_id}");
                    }
                );
            }
        }

        // SMS to admin (optional)
        $adminWithPhone = User::whereIn('role', ['admin', 'staff'])
            ->whereNotNull('phone')
            ->first();

        if ($adminWithPhone && $adminWithPhone->phone) {
            $this->sendSMS(
                $adminWithPhone->phone,
                "GOOD NEWS: {$priestName} restored their assignment for {$reservation->service->service_name} on " . $reservation->schedule_date->format('M d, Y') . ". Awaiting priest confirmation."
            );
        }

        // Requestor in-app
        try {
            $message = "A priest restored their availability for your reservation";
            Notification::create([
                'user_id' => $reservation->user_id,
                'reservation_id' => $reservation->reservation_id,
                'message' => $message,
                'type' => 'Update',
                'sent_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to create requestor in-app notification (priest undeclined): ' . $e->getMessage());
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

        // Email to CREaM Admin/Staff for reassignment
        $admins = User::whereIn('role', ['admin', 'staff'])->get();
        foreach ($admins as $admin) {
            if ($admin->email) {
                // Create email notification
                Mail::raw(
                    "URGENT: Confirmed Reservation Cancelled\n\n" .
                    "{$priestName} has CANCELLED their previously confirmed reservation:\n\n" .
                    "Service: {$reservation->service->service_name}\n" .
                    "Date & Time: {$reservation->schedule_date->format('F d, Y - h:i A')}\n" .
                    "Venue: " . ($reservation->custom_venue_name ?? $reservation->venue->name ?? 'N/A') . "\n" .
                    "Requestor: {$reservation->user->first_name} {$reservation->user->last_name}\n\n" .
                    "Reason for cancellation:\n" .
                    "{$reason}\n\n" .
                    "⚠️ This priest had already confirmed their availability but has now cancelled.\n" .
                    "Action Required: Please reassign another priest immediately.\n\n" .
                    "Please check the eReligiousServices system to reassign this reservation.\n\n" .
                    "---\n" .
                    "CREaM - eReligiousServices Management System\n" .
                    "Holy Name University",
                    function ($message) use ($admin, $priestName, $reservation) {
                        $message->to($admin->email)
                            ->subject("⚠️ URGENT: {$priestName} Cancelled Confirmed Reservation #{$reservation->reservation_id}");
                    }
                );
            }

            // Create in-app notification for each admin
            try {
                $requestorName = $reservation->user->first_name . ' ' . $reservation->user->last_name;
                $message = "<strong>{$priestName}</strong> cancelled back his reservation submitted by <strong>{$requestorName}</strong> ⚠️";

                $notificationData = [
                    'user_id' => $admin->id,
                    'reservation_id' => $reservation->reservation_id,
                    'message' => $message,
                    'type' => 'Urgent',
                    'sent_at' => now(),
                ];

                // Add data field if column exists
                try {
                    if (Schema::hasColumn('notifications', 'data')) {
                        $notificationData['data'] = json_encode([
                            'reason' => $reason,
                            'priest_name' => $priest ? $priest->first_name . ' ' . $priest->last_name : 'Unknown',
                            'priest_id' => $priestId,
                            'service_name' => $reservation->service->service_name,
                            'schedule_date' => $reservation->schedule_date->format('Y-m-d H:i:s'),
                            'requestor_name' => $reservation->user->first_name . ' ' . $reservation->user->last_name,
                            'venue' => $reservation->custom_venue_name ?? $reservation->venue->name ?? 'N/A',
                            'action' => 'cancelled_confirmation',
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::info('Data column check failed: ' . $e->getMessage());
                }

                Notification::create($notificationData);
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
                "⚠️ URGENT: {$priestName} CANCELLED confirmed reservation #{$reservation->reservation_id} for {$reservation->service->service_name} on " . $reservation->schedule_date->format('M d, Y') . ". Please reassign immediately!"
            );
        }
    }

    /**
     * Send SMS using Semaphore API (Philippine SMS provider)
     * Replace with Twilio or other provider as needed
     */
    private function sendSMS(string $phoneNumber, string $message): void
    {
        try {
            // Check if SMS is enabled
            if (!config('services.semaphore.enabled', false)) {
                Log::info('SMS disabled - would have sent: ' . $message . ' to ' . $phoneNumber);
                return;
            }

            $apiKey = config('services.semaphore.api_key');
            $senderName = config('services.semaphore.sender_name', 'CREaM-HNU');

            // Semaphore API endpoint
            $response = Http::asForm()->post('https://api.semaphore.co/api/v4/messages', [
                'apikey' => $apiKey,
                'number' => $this->formatPhoneNumber($phoneNumber),
                'message' => $message,
                'sendername' => $senderName,
            ]);

            if ($response->successful()) {
                Log::info('SMS sent successfully to ' . $phoneNumber);
            } else {
                Log::error('Failed to send SMS: ' . $response->body());
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
     * Notify all relevant parties that a reservation was rescheduled
     */
    public function notifyReservationRescheduled(Reservation $reservation, \Carbon\Carbon $oldDate, string $remarks = ''): void
    {
        try {
            $old = $oldDate->format('M d, Y h:i A');
            $new = optional($reservation->schedule_date)->format('M d, Y h:i A');

            $message = "Reservation schedule changed from <strong>{$old}</strong> to <strong>{$new}</strong>.";

            $allRecipients = collect();
            // Requestor
            if ($reservation->user) { $allRecipients->push($reservation->user); }
            // Adviser
            if ($reservation->organization && $reservation->organization->adviser) { $allRecipients->push($reservation->organization->adviser); }
            // Priest (if assigned)
            if ($reservation->officiant) { $allRecipients->push($reservation->officiant); }
            // Admin/Staff
            $admins = User::whereIn('role', ['admin', 'staff'])->get();
            $allRecipients = $allRecipients->merge($admins)->unique('id');

            foreach ($allRecipients as $recipient) {
                Notification::create([
                    'user_id' => $recipient->id,
                    'reservation_id' => $reservation->reservation_id,
                    'message' => $message,
                    'type' => 'Update',
                    'sent_at' => now(),
                    'data' => json_encode([
                        'action' => 'rescheduled',
                        'old' => $oldDate->toDateTimeString(),
                        'new' => optional($reservation->schedule_date)?->toDateTimeString(),
                        'remarks' => $remarks,
                    ]),
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to send reschedule notifications: ' . $e->getMessage());
        }
    }
}
