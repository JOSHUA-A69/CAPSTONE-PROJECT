<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\OrganizationBookingRequest;
use App\Services\OrganizationBookingNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrganizationBookingController extends Controller
{
    protected $notificationService;

    public function __construct(OrganizationBookingNotificationService $notificationService)
    {
        $this->middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':staff']);
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of all organization booking requests for staff monitoring.
     */
    public function index()
    {
        $requests = OrganizationBookingRequest::with(['requestor', 'organization', 'organization.adviser'])
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')")
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Get summary statistics
        $stats = [
            'total' => OrganizationBookingRequest::count(),
            'pending' => OrganizationBookingRequest::pending()->count(),
            'approved' => OrganizationBookingRequest::approved()->count(),
            'rejected' => OrganizationBookingRequest::rejected()->count(),
            'overdue' => OrganizationBookingRequest::needingReminder()->count(),
        ];

        return view('staff.organization-bookings.index', compact('requests', 'stats'));
    }

    /**
     * Display overdue organization booking requests that need staff intervention.
     */
    public function overdue()
    {
        $overdueRequests = OrganizationBookingRequest::with(['requestor', 'organization', 'organization.adviser'])
            ->needingReminder()
            ->orderBy('adviser_notified_at')
            ->paginate(15);

        return view('staff.organization-bookings.overdue', compact('overdueRequests'));
    }

    /**
     * Display the specified organization booking request.
     */
    public function show(OrganizationBookingRequest $organizationBookingRequest)
    {
        $organizationBookingRequest->load([
            'requestor', 
            'organization', 
            'organization.adviser', 
            'approvedBy', 
            'rejectedBy'
        ]);

        // Check if this request is overdue
        $isOverdue = $organizationBookingRequest->is_overdue;
        $daysPending = $organizationBookingRequest->adviser_notified_at ? 
            $organizationBookingRequest->adviser_notified_at->diffInDays(now()) : 0;

        return view('staff.organization-bookings.show', compact('organizationBookingRequest', 'isOverdue', 'daysPending'));
    }

    /**
     * Send a reminder to the adviser about a pending request.
     */
    public function sendReminder(Request $request, OrganizationBookingRequest $organizationBookingRequest)
    {
        // Only allow reminders for pending requests
        if ($organizationBookingRequest->status !== 'pending') {
            return redirect()->back()->with('error', 'Reminders can only be sent for pending requests.');
        }

        // Check if adviser is assigned
        if (!$organizationBookingRequest->organization->adviser) {
            return redirect()->back()->with('error', 'This organization has no assigned adviser. Please assign an adviser first.');
        }

        // Send staff reminder (this will also notify the adviser)
        $success = $this->notificationService->sendStaffReminderForOverdueRequest($organizationBookingRequest);

        if ($success) {
            return redirect()->back()
                ->with('status', 'reminder-sent')
                ->with('message', 'Reminder has been sent to the organization adviser. Staff members have been notified of the overdue request.');
        } else {
            return redirect()->back()
                ->with('error', 'Failed to send reminder. Please try again or contact the system administrator.');
        }
    }

    /**
     * Get organization booking statistics for dashboard widgets.
     */
    public function getStatistics()
    {
        $stats = [
            'total_requests' => OrganizationBookingRequest::count(),
            'pending_requests' => OrganizationBookingRequest::pending()->count(),
            'approved_this_week' => OrganizationBookingRequest::approved()
                ->where('adviser_responded_at', '>=', now()->subWeek())
                ->count(),
            'overdue_requests' => OrganizationBookingRequest::needingReminder()->count(),
            'organizations_without_advisers' => $this->notificationService->getOrganizationsWithoutAdvisers()->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Process all overdue requests and send reminders.
     */
    public function processOverdueRequests()
    {
        $results = $this->notificationService->processOverdueRequests();
        
        if ($results['processed'] > 0) {
            $message = "Processed {$results['processed']} overdue requests. {$results['successful']} reminders sent successfully.";
            return redirect()->route('staff.organization-bookings.overdue')
                ->with('status', 'reminders-processed')
                ->with('message', $message);
        } else {
            return redirect()->route('staff.organization-bookings.overdue')
                ->with('info', 'No overdue requests found that need reminders.');
        }
    }
}