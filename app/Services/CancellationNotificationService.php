<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\ReservationCancellation;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Support\Notifications as NotificationHelper;

class CancellationNotificationService
{
    /**
     * Send notifications when requestor requests cancellation
     */
    public function notifyCancellationRequest(Reservation $reservation, ReservationCancellation $cancellation): void
    {
        $requestor = $reservation->user;
        $requestorName = $requestor->first_name . ' ' . $requestor->last_name;
        $serviceName = $reservation->service->service_name;
        $scheduleDate = $reservation->schedule_date->format('F d, Y - h:i A');

        // Notify Staff/Admin
        $this->notifyStaffAndAdmin($reservation, $cancellation, $requestorName, $serviceName, $scheduleDate);

        // Notify Adviser if exists
        if ($reservation->organization && $reservation->organization->adviser) {
            $this->notifyAdviser($reservation, $cancellation, $requestorName, $serviceName, $scheduleDate);
        }

        // Notify Priest if assigned
        if ($reservation->officiant_id) {
            $this->notifyPriest($reservation, $cancellation, $requestorName, $serviceName, $scheduleDate);
        }
    }

    /**
     * Notify staff and admin about cancellation request
     */
    private function notifyStaffAndAdmin(Reservation $reservation, ReservationCancellation $cancellation, $requestorName, $serviceName, $scheduleDate): void
    {
        $staffAndAdmin = User::whereIn('role', ['staff', 'admin'])->where('status', 'active')->get();

        foreach ($staffAndAdmin as $user) {
            // Send email
            if ($user->email) {
                try {
                    Mail::raw(
                        "Cancellation Request Received\n\n" .
                        "{$requestorName} has requested to cancel their reservation.\n\n" .
                        "Reservation Details:\n" .
                        "Service: {$serviceName}\n" .
                        "Date & Time: {$scheduleDate}\n" .
                        "Venue: " . ($reservation->custom_venue_name ?? $reservation->venue->name ?? 'N/A') . "\n\n" .
                        "Reason:\n{$cancellation->reason}\n\n" .
                        "Please log in to the system to confirm this cancellation.\n\n" .
                        "CREaM - eReligiousServices Management System",
                        function ($message) use ($user, $requestorName) {
                            $message->to($user->email)
                                ->subject("🚫 Cancellation Request from {$requestorName}");
                        }
                    );
                } catch (\Exception $e) {
                    Log::error('Failed to send cancellation email to staff/admin: ' . $e->getMessage());
                }
            }

            // Create in-app notification
            NotificationHelper::make([
                'user_id' => $user->id,
                'reservation_id' => $reservation->reservation_id,
                'message' => "<strong>{$requestorName}</strong> requested to cancel their reservation for <strong>{$serviceName}</strong>",
                'type' => NotificationHelper::TYPE_CANCELLATION_REQUEST,
                'sent_at' => now(),
                'data' => [
                    'cancellation_id' => $cancellation->cancellation_id,
                    'requestor_name' => $requestorName,
                    'service_name' => $serviceName,
                    'schedule_date' => $reservation->schedule_date->format('Y-m-d H:i:s'),
                    'reason' => $cancellation->reason,
                    'action' => 'cancellation_requested',
                    'requires_confirmation' => true,
                ],
            ]);
        }
    }

    /**
     * Notify adviser about cancellation request
     */
    private function notifyAdviser(Reservation $reservation, ReservationCancellation $cancellation, $requestorName, $serviceName, $scheduleDate): void
    {
        $adviser = $reservation->organization->adviser;
        if (!$adviser) return;

        // Update cancellation record
        $cancellation->update(['adviser_notified_at' => now()]);

        // Send email
        if ($adviser->email) {
            try {
                Mail::raw(
                    "Dear {$adviser->first_name},\n\n" .
                    "{$requestorName} from your organization has requested to cancel their reservation.\n\n" .
                    "Reservation Details:\n" .
                    "Service: {$serviceName}\n" .
                    "Date & Time: {$scheduleDate}\n\n" .
                    "Reason:\n{$cancellation->reason}\n\n" .
                    "Please log in to confirm this cancellation within the next few minutes.\n\n" .
                    "CREaM - eReligiousServices Management System",
                    function ($message) use ($adviser, $requestorName) {
                        $message->to($adviser->email)
                            ->subject("🚫 Cancellation Request from {$requestorName}");
                    }
                );
            } catch (\Exception $e) {
                Log::error('Failed to send cancellation email to adviser: ' . $e->getMessage());
            }
        }

        // Create in-app notification
        NotificationHelper::make([
            'user_id' => $adviser->id,
            'reservation_id' => $reservation->reservation_id,
            'message' => "<strong>{$requestorName}</strong> from your organization requested to cancel their reservation for <strong>{$serviceName}</strong>",
            'type' => NotificationHelper::TYPE_CANCELLATION_REQUEST,
            'sent_at' => now(),
            'data' => [
                'cancellation_id' => $cancellation->cancellation_id,
                'requestor_name' => $requestorName,
                'service_name' => $serviceName,
                'schedule_date' => $reservation->schedule_date->format('Y-m-d H:i:s'),
                'reason' => $cancellation->reason,
                'action' => 'cancellation_requested',
                'requires_confirmation' => true,
                'role' => 'adviser',
            ],
        ]);
    }

    /**
     * Notify priest about cancellation request
     */
    private function notifyPriest(Reservation $reservation, ReservationCancellation $cancellation, $requestorName, $serviceName, $scheduleDate): void
    {
        $priest = $reservation->officiant;
        if (!$priest) return;

        // Update cancellation record
        $cancellation->update(['priest_notified_at' => now()]);

        // Send email
        if ($priest->email) {
            try {
                Mail::raw(
                    "Dear Fr. {$priest->first_name},\n\n" .
                    "{$requestorName} has requested to cancel the reservation for which you were assigned.\n\n" .
                    "Reservation Details:\n" .
                    "Service: {$serviceName}\n" .
                    "Date & Time: {$scheduleDate}\n\n" .
                    "Reason:\n{$cancellation->reason}\n\n" .
                    "Please log in to acknowledge this cancellation.\n\n" .
                    "CREaM - eReligiousServices Management System",
                    function ($message) use ($priest, $requestorName) {
                        $message->to($priest->email)
                            ->subject("🚫 Cancellation: Assignment Cancelled by {$requestorName}");
                    }
                );
            } catch (\Exception $e) {
                Log::error('Failed to send cancellation email to priest: ' . $e->getMessage());
            }
        }

        // Create in-app notification
        NotificationHelper::make([
            'user_id' => $priest->id,
            'reservation_id' => $reservation->reservation_id,
            'message' => "<strong>{$requestorName}</strong> requested to cancel the reservation you were assigned to officiate",
            'type' => NotificationHelper::TYPE_CANCELLATION_REQUEST,
            'sent_at' => now(),
            'data' => [
                'cancellation_id' => $cancellation->cancellation_id,
                'requestor_name' => $requestorName,
                'service_name' => $serviceName,
                'schedule_date' => $reservation->schedule_date->format('Y-m-d H:i:s'),
                'reason' => $cancellation->reason,
                'action' => 'cancellation_requested',
                'requires_confirmation' => true,
                'role' => 'priest',
            ],
        ]);
    }

    /**
     * Notify staff when adviser/priest doesn't respond within 1 minute
     */
    public function notifyStaffOfUnresponsive(Reservation $reservation, ReservationCancellation $cancellation, $role): void
    {
        $staff = User::where('role', 'staff')->where('status', 'active')->get();
        $unresponsivePerson = null;
        $contactInfo = [];

        if ($role === 'adviser' && $reservation->organization && $reservation->organization->adviser) {
            $unresponsivePerson = $reservation->organization->adviser;
            $contactInfo = [
                'name' => $unresponsivePerson->first_name . ' ' . $unresponsivePerson->last_name,
                'email' => $unresponsivePerson->email,
                'phone' => $unresponsivePerson->phone,
                'role' => 'Organization Adviser',
            ];
        } elseif ($role === 'priest' && $reservation->officiant) {
            $unresponsivePerson = $reservation->officiant;
            $contactInfo = [
                'name' => 'Fr. ' . $unresponsivePerson->first_name . ' ' . $unresponsivePerson->last_name,
                'email' => $unresponsivePerson->email,
                'phone' => $unresponsivePerson->phone,
                'role' => 'Assigned Priest',
            ];
        }

        if (!$unresponsivePerson) return;

        foreach ($staff as $staffMember) {
            // Send email
            if ($staffMember->email) {
                try {
                    Mail::raw(
                        "⚠️ FOLLOW-UP REQUIRED\n\n" .
                        "The {$contactInfo['role']} has not responded to a cancellation request within 1 minute.\n\n" .
                        "Contact Information:\n" .
                        "Name: {$contactInfo['name']}\n" .
                        "Email: {$contactInfo['email']}\n" .
                        "Phone: {$contactInfo['phone']}\n\n" .
                        "Reservation Details:\n" .
                        "Service: {$reservation->service->service_name}\n" .
                        "Date: {$reservation->schedule_date->format('F d, Y - h:i A')}\n\n" .
                        "Please follow up with them via phone or email.\n\n" .
                        "CREaM - eReligiousServices Management System",
                        function ($message) use ($staffMember, $role) {
                            $message->to($staffMember->email)
                                ->subject("⚠️ Unresponsive " . ucfirst($role) . " - Follow-up Needed");
                        }
                    );
                } catch (\Exception $e) {
                    Log::error('Failed to send escalation email to staff: ' . $e->getMessage());
                }
            }

            // Create in-app notification with contact info
            NotificationHelper::make([
                'user_id' => $staffMember->id,
                'reservation_id' => $reservation->reservation_id,
                'message' => "⚠️ <strong>{$contactInfo['name']}</strong> has not responded to cancellation request - Follow-up required",
                'type' => NotificationHelper::TYPE_URGENT,
                'sent_at' => now(),
                'data' => [
                    'cancellation_id' => $cancellation->cancellation_id,
                    'action' => 'escalation_to_staff',
                    'unresponsive_role' => $role,
                    'contact_info' => $contactInfo,
                    'service_name' => $reservation->service->service_name,
                    'schedule_date' => $reservation->schedule_date->format('Y-m-d H:i:s'),
                ],
            ]);
        }

        // Update cancellation record
        if ($role === 'adviser') {
            $cancellation->update(['staff_escalated_adviser_at' => now()]);
        } else {
            $cancellation->update(['staff_escalated_priest_at' => now()]);
        }
    }

    /**
     * Notify all parties when cancellation is confirmed
     */
    public function notifyCancellationCompleted(Reservation $reservation, ReservationCancellation $cancellation): void
    {
        $requestor = $reservation->user;
        $requestorName = $requestor->first_name . ' ' . $requestor->last_name;

        // Notify requestor
        if ($requestor->email) {
            try {
                Mail::raw(
                    "Dear {$requestor->first_name},\n\n" .
                    "Your cancellation request has been confirmed by all parties.\n\n" .
                    "Reservation Details:\n" .
                    "Service: {$reservation->service->service_name}\n" .
                    "Original Date: {$reservation->schedule_date->format('F d, Y - h:i A')}\n\n" .
                    "The reservation has been cancelled.\n\n" .
                    "CREaM - eReligiousServices Management System",
                    function ($message) use ($requestor) {
                        $message->to($requestor->email)
                            ->subject("✓ Cancellation Confirmed");
                    }
                );
            } catch (\Exception $e) {
                Log::error('Failed to send completion email to requestor: ' . $e->getMessage());
            }
        }

        // Notify requestor in-app
        NotificationHelper::make([
            'user_id' => $requestor->id,
            'reservation_id' => $reservation->reservation_id,
            'message' => "Your cancellation request for <strong>{$reservation->service->service_name}</strong> has been confirmed",
            'type' => NotificationHelper::TYPE_UPDATE,
            'sent_at' => now(),
            'data' => [
                'cancellation_id' => $cancellation->cancellation_id,
                'action' => 'cancellation_completed',
                'service_name' => $reservation->service->service_name,
            ],
        ]);

        // Also notify staff/admin that cancellation was completed (audit/awareness)
        $staffAndAdmin = User::whereIn('role', ['staff', 'admin'])->where('status', 'active')->get();
        foreach ($staffAndAdmin as $user) {
            NotificationHelper::make([
                'user_id' => $user->id,
                'reservation_id' => $reservation->reservation_id,
                'message' => "Cancellation completed for <strong>{$reservation->service->service_name}</strong> by <strong>{$requestorName}</strong>",
                'type' => NotificationHelper::TYPE_UPDATE,
                'sent_at' => now(),
                'data' => [
                    'cancellation_id' => $cancellation->cancellation_id,
                    'action' => 'cancellation_completed',
                    'service_name' => $reservation->service->service_name,
                    'requestor_name' => $requestorName,
                ],
            ]);
        }
    }
}
