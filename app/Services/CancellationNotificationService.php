<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\ReservationCancellation;
use App\Models\User;
use App\Models\Notification;
use App\Mail\ReservationCancellationConfirmed;
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
                    Mail::to($user->email)->send(
                        new \App\Mail\ReservationCancellationRequested(
                            $reservation, 
                            $cancellation, 
                            $requestorName,
                            $user->first_name // Personalize for staff
                        )
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
                Mail::to($adviser->email)->send(
                    new \App\Mail\ReservationCancellationRequested(
                        $reservation, 
                        $cancellation, 
                        $requestorName,
                        $adviser->first_name
                    )
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
                Mail::to($priest->email)->send(
                    new \App\Mail\ReservationCancellationRequested(
                        $reservation, 
                        $cancellation, 
                        $requestorName,
                        'Fr. ' . $priest->first_name
                    )
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
                    Mail::to($staffMember->email)->send(
                        new \App\Mail\ReservationCancellationUnresponsive(
                            $reservation,
                            $role,
                            $contactInfo
                        )
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
     * Notify requestor when cancellation request is rejected
     */
    public function notifyCancellationRejected(ReservationCancellation $cancellation, string $rejectorName, string $rejectorRole): void
    {
        $reservation = $cancellation->reservation;
        $requestor = $cancellation->requestor; // The user who made the cancellation request

        // Notify requestor in-app
        NotificationHelper::make([
            'user_id' => $requestor->id,
            'reservation_id' => $reservation->reservation_id,
            'message' => "Your cancellation request for <strong>{$reservation->service->service_name}</strong> has been rejected by {$rejectorRole} {$rejectorName}. The reservation remains active.",
            'type' => NotificationHelper::TYPE_UPDATE, // Or add a specific constant if preferred
            'sent_at' => now(),
            'data' => [
                'cancellation_id' => $cancellation->cancellation_id,
                'action' => 'cancellation_rejected',
                'service_name' => $reservation->service->service_name,
                'rejector_name' => $rejectorName,
                'rejector_role' => $rejectorRole
            ],
        ]);
        
        // Notify Staff/Admin that it was rejected (for tracking)
        $staffAndAdmin = User::whereIn('role', ['staff', 'admin'])->where('status', 'active')->get();
        foreach ($staffAndAdmin as $user) {
            NotificationHelper::make([
                'user_id' => $user->id,
                'reservation_id' => $reservation->reservation_id,
                'message' => "Cancellation request for <strong>{$reservation->service->service_name}</strong> was rejected by {$rejectorRole} {$rejectorName}",
                'type' => NotificationHelper::TYPE_UPDATE,
                'sent_at' => now(),
                'data' => [
                    'cancellation_id' => $cancellation->cancellation_id,
                    'action' => 'cancellation_rejected',
                    'service_name' => $reservation->service->service_name,
                    'rejector_name' => $rejectorName,
                    'rejector_role' => $rejectorRole
                ],
            ]);
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
                Mail::to($requestor->email)
                    ->send(new ReservationCancellationConfirmed($reservation));
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
