<?php

namespace App\Services;

use App\Models\OrganizationBookingRequest;
use App\Models\OrganizationBookingCancellation;
use App\Models\User;
use App\Models\Notification;
use App\Mail\OrganizationBookingAdviserNotification;
use App\Mail\OrganizationBookingApprovalNotification;
use App\Mail\OrganizationBookingRejectionNotification;
use App\Mail\OrganizationBookingStaffReminder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

/**
 * Organization Booking Notification Service
 *
 * Handles all notification logic for the organization booking workflow:
 * - Notify advisers of new requests
 * - Notify requestors of approval/rejection
 * - Send staff reminders for overdue requests
 */
class OrganizationBookingNotificationService
{
    /**
     * Collect all target organizations for adviser notification.
     * Falls back to legacy single organization relation when needed.
     */
    private function resolveTargetOrganizations(OrganizationBookingRequest $request)
    {
        $request->loadMissing(['organizations.adviser', 'organization.adviser', 'requestor']);

        $organizations = $request->organizations ?? collect();

        if ($organizations->isEmpty() && $request->organization) {
            $organizations = collect([$request->organization]);
        }

        return $organizations->filter();
    }

    /**
     * Notify organization adviser about a new booking request
     */
    public function notifyAdviserOfNewRequest(OrganizationBookingRequest $request)
    {
        $organizations = $this->resolveTargetOrganizations($request);

        if ($organizations->isEmpty()) {
            Log::warning("No organizations found for booking request #{$request->id}");
            return false;
        }

        // Group organizations by adviser to avoid duplicate notifications to the same adviser.
        $adviserOrganizations = [];
        foreach ($organizations as $organization) {
            $adviser = $organization->adviser;
            if (!$adviser || !$adviser->id) {
                Log::warning("No adviser assigned to organization {$organization->org_name} for booking request #{$request->id}");
                continue;
            }

            if (!isset($adviserOrganizations[$adviser->id])) {
                $adviserOrganizations[$adviser->id] = [
                    'adviser' => $adviser,
                    'organizations' => collect(),
                ];
            }

            $adviserOrganizations[$adviser->id]['organizations']->push($organization);
        }

        if (empty($adviserOrganizations)) {
            Log::warning("No advisers found for booking request #{$request->id}");
            return false;
        }

        $successCount = 0;

        foreach ($adviserOrganizations as $adviserData) {
            $adviser = $adviserData['adviser'];
            $assignedOrganizations = $adviserData['organizations'];
            $assignedOrganizationNames = $assignedOrganizations->pluck('org_name')->values()->all();
            $organizationListLabel = implode(', ', $assignedOrganizationNames);
            $emailSent = false;

            try {
                if ($adviser->email) {
                    try {
                        // Use first assigned org as the per-email context organization.
                        $primaryContextOrg = $assignedOrganizations->first();
                        Mail::to($adviser->email)->send(new OrganizationBookingAdviserNotification($request, $primaryContextOrg));
                        $emailSent = true;
                    } catch (\Exception $mailException) {
                        Log::warning("Email delivery failed for booking request #{$request->id} to adviser #{$adviser->id}: " . $mailException->getMessage());
                    }
                }

                Notification::create([
                    'user_id' => $adviser->id,
                    'message' => "New organization booking request for \"{$request->activity_name}\" from {$organizationListLabel} requires your approval.",
                    'type' => 'org_booking_new_request',
                    'sent_at' => now(),
                    'data' => [
                        'organization_booking_request_id' => $request->id,
                        'activity_name' => $request->activity_name,
                        'organization_name' => $organizationListLabel,
                        'organization_names' => $assignedOrganizationNames,
                        'requestor_name' => $request->requestor ? ($request->requestor->full_name ?? $request->requestor->name) : 'Unknown User',
                    ],
                ]);

                foreach ($assignedOrganizations as $assignedOrganization) {
                    $request->organizations()
                        ->updateExistingPivot($assignedOrganization->org_id, [
                            'notified' => true,
                            'notified_at' => now(),
                            'updated_at' => now(),
                        ]);
                }

                $successCount++;
                Log::info("Adviser notification recorded for booking request #{$request->id} to adviser #{$adviser->id}", [
                    'email_sent' => $emailSent,
                    'email' => $adviser->email,
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to send adviser notification for booking request #{$request->id} to adviser #{$adviser->id}: " . $e->getMessage());
            }
        }

        if ($successCount > 0) {
            $request->markAdviserNotified();
            return true;
        }

        return false;
    }

    /**
     * Notify requestor that their booking request was approved
     */
    public function notifyRequestorOfApproval(OrganizationBookingRequest $request, $comments = null)
    {
        $requestor = $request->requestor;
        $organization = $request->organization;

        if (!$requestor) return false;

        try {
            // Send email notification
            if ($requestor->email) {
                Mail::to($requestor->email)->send(new OrganizationBookingApprovalNotification($request, $comments));
            }

            // Create in-app database notification
            Notification::create([
                'user_id' => $requestor->id,
                'message' => "Your organization booking request for \"{$request->activity_name}\" ({$organization->org_name}) has been approved by your adviser.",
                'type' => 'org_booking_approved',
                'sent_at' => now(),
                'data' => [
                    'organization_booking_request_id' => $request->id,
                    'activity_name' => $request->activity_name,
                    'organization_name' => $organization->org_name,
                    'comments' => $comments,
                ],
            ]);

            Log::info("Approval notification sent for booking request #{$request->id} to {$requestor->email}");
            return true;

        } catch (\Exception $e) {
            Log::error("Failed to send approval notification for booking request #{$request->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Notify requestor that their booking request was rejected
     */
    public function notifyRequestorOfRejection(OrganizationBookingRequest $request, $reason, $comments = null)
    {
        $requestor = $request->requestor;
        $organization = $request->organization;

        if (!$requestor) return false;

        try {
            // Send email notification
            if ($requestor->email) {
                Mail::to($requestor->email)->send(new OrganizationBookingRejectionNotification($request, $reason, $comments));
            }

            // Create in-app database notification
            Notification::create([
                'user_id' => $requestor->id,
                'message' => "Your organization booking request for \"{$request->activity_name}\" ({$organization->org_name}) was not approved by your adviser. Reason: {$reason}",
                'type' => 'org_booking_rejected',
                'sent_at' => now(),
                'data' => [
                    'organization_booking_request_id' => $request->id,
                    'activity_name' => $request->activity_name,
                    'organization_name' => $organization->org_name,
                    'reason' => $reason,
                    'comments' => $comments,
                ],
            ]);

            Log::info("Rejection notification sent for booking request #{$request->id} to {$requestor->email}");
            return true;

        } catch (\Exception $e) {
            Log::error("Failed to send rejection notification for booking request #{$request->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send reminder to staff about overdue adviser responses
     */
    public function sendStaffReminderForOverdueRequest(OrganizationBookingRequest $request)
    {
        // Get all staff members
        $staffMembers = User::where('role', 'staff')->get();

        if ($staffMembers->isEmpty()) {
            Log::warning("No staff members found to send reminder for overdue booking request #{$request->id}");
            return false;
        }

        $success = true;

        foreach ($staffMembers as $staff) {
            try {
                Mail::to($staff->email)->send(new OrganizationBookingStaffReminder($request));
                Log::info("Staff reminder sent for booking request #{$request->id} to {$staff->email}");
            } catch (\Exception $e) {
                Log::error("Failed to send staff reminder for booking request #{$request->id} to {$staff->email}: " . $e->getMessage());
                $success = false;
            }
        }

        if ($success) {
            // Mark that staff has been reminded
            $request->markStaffReminded();
        }

        return $success;
    }

    /**
     * Process all overdue requests and send staff reminders
     */
    public function processOverdueRequests()
    {
        $overdueRequests = OrganizationBookingRequest::needingReminder()->get();

        $processedCount = 0;
        $successCount = 0;

        foreach ($overdueRequests as $request) {
            $processedCount++;

            if ($this->sendStaffReminderForOverdueRequest($request)) {
                $successCount++;
            }
        }

        Log::info("Processed {$processedCount} overdue booking requests, {$successCount} reminders sent successfully");

        return [
            'processed' => $processedCount,
            'successful' => $successCount,
        ];
    }

    /**
     * Send notification to admin about organization booking statistics
     */
    public function sendAdminStatisticsNotification($period = 'daily')
    {
        $admins = User::where('role', 'admin')->get();

        if ($admins->isEmpty()) {
            return false;
        }

        // Calculate statistics based on period
        $stats = $this->calculateBookingStatistics($period);

        // TODO: Create AdminStatisticsNotification mail class
        // This would include pending requests, approval rates, overdue requests, etc.

        return true;
    }

    /**
     * Calculate booking statistics for reporting
     */
    private function calculateBookingStatistics($period = 'daily')
    {
        $startDate = match($period) {
            'weekly' => now()->subWeek(),
            'monthly' => now()->subMonth(),
            default => now()->subDay(),
        };

        return [
            'period' => $period,
            'total_requests' => OrganizationBookingRequest::where('created_at', '>=', $startDate)->count(),
            'pending_requests' => OrganizationBookingRequest::pending()->count(),
            'approved_requests' => OrganizationBookingRequest::approved()->where('created_at', '>=', $startDate)->count(),
            'rejected_requests' => OrganizationBookingRequest::rejected()->where('created_at', '>=', $startDate)->count(),
            'overdue_requests' => OrganizationBookingRequest::needingReminder()->count(),
        ];
    }

    /**
     * Get organizations needing adviser assignment
     */
    public function getOrganizationsWithoutAdvisers()
    {
        return \App\Models\Organization::whereNull('adviser_id')->get();
    }

    /**
     * Notify adviser about a cancellation request
     */
    public function notifyAdviserOfCancellationRequest(OrganizationBookingRequest $request)
    {
        $organizations = $this->resolveTargetOrganizations($request);

        if ($organizations->isEmpty()) {
            Log::warning("No organizations found for cancellation request notification on booking #{$request->id}");
            return false;
        }

        $adviserOrganizations = [];
        foreach ($organizations as $organization) {
            $adviser = $organization->adviser;
            if (!$adviser || !$adviser->id) {
                Log::warning("No adviser assigned to organization {$organization->org_name} for cancellation request on booking #{$request->id}");
                continue;
            }

            if (!isset($adviserOrganizations[$adviser->id])) {
                $adviserOrganizations[$adviser->id] = [
                    'adviser' => $adviser,
                    'organizations' => collect(),
                ];
            }

            $adviserOrganizations[$adviser->id]['organizations']->push($organization);
        }

        if (empty($adviserOrganizations)) {
            return false;
        }

        $successCount = 0;

        foreach ($adviserOrganizations as $adviserData) {
            $adviser = $adviserData['adviser'];
            $assignedOrganizationNames = $adviserData['organizations']->pluck('org_name')->values()->all();
            $organizationListLabel = implode(', ', $assignedOrganizationNames);

            try {
                Notification::create([
                    'user_id' => $adviser->id,
                    'message' => "Cancellation request received for \"{$request->activity_name}\" from {$organizationListLabel}. Please review and respond.",
                    'type' => 'org_booking_cancellation_request',
                    'sent_at' => now(),
                    'data' => [
                        'organization_booking_request_id' => $request->id,
                        'activity_name' => $request->activity_name,
                        'organization_name' => $organizationListLabel,
                        'organization_names' => $assignedOrganizationNames,
                        'requestor_name' => $request->requestor ? ($request->requestor->full_name ?? $request->requestor->name) : 'Unknown User',
                    ],
                ]);

                $successCount++;
                Log::info("Cancellation request notification sent for booking #{$request->id} to adviser {$adviser->email}");
            } catch (\Exception $e) {
                Log::error("Failed to send cancellation request notification for booking #{$request->id} to adviser #{$adviser->id}: " . $e->getMessage());
            }
        }

        return $successCount > 0;
    }

    /**
     * Notify requestor that their cancellation request was approved
     */
    public function notifyRequestorOfCancellationApproval(OrganizationBookingCancellation $cancellation)
    {
        $request = $cancellation->bookingRequest;
        $requestor = $request->requestor;
        $organization = $request->organization;

        if (!$requestor) return false;

        try {
            // Create in-app database notification
            Notification::create([
                'user_id' => $requestor->id,
                'message' => "Your cancellation request for \"{$request->activity_name}\" ({$organization->org_name}) has been approved. The booking has been cancelled.",
                'type' => 'org_booking_cancellation_approved',
                'sent_at' => now(),
                'data' => [
                    'organization_booking_request_id' => $request->id,
                    'activity_name' => $request->activity_name,
                    'organization_name' => $organization->org_name,
                    'adviser_response' => $cancellation->adviser_response,
                ],
            ]);

            Log::info("Cancellation approval notification sent for booking #{$request->id} to {$requestor->email}");
            return true;

        } catch (\Exception $e) {
            Log::error("Failed to send cancellation approval notification for booking #{$request->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Notify requestor that their cancellation request was rejected
     */
    public function notifyRequestorOfCancellationRejection(OrganizationBookingCancellation $cancellation)
    {
        $request = $cancellation->bookingRequest;
        $requestor = $request->requestor;
        $organization = $request->organization;

        if (!$requestor) return false;

        try {
            // Create in-app database notification
            Notification::create([
                'user_id' => $requestor->id,
                'message' => "Your cancellation request for \"{$request->activity_name}\" ({$organization->org_name}) was not approved. The booking remains active.",
                'type' => 'org_booking_cancellation_rejected',
                'sent_at' => now(),
                'data' => [
                    'organization_booking_request_id' => $request->id,
                    'activity_name' => $request->activity_name,
                    'organization_name' => $organization->org_name,
                    'adviser_response' => $cancellation->adviser_response,
                ],
            ]);

            Log::info("Cancellation rejection notification sent for booking #{$request->id} to {$requestor->email}");
            return true;

        } catch (\Exception $e) {
            Log::error("Failed to send cancellation rejection notification for booking #{$request->id}: " . $e->getMessage());
            return false;
        }
    }
}
