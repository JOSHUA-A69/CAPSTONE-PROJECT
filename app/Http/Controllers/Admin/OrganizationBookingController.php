<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrganizationBookingRequest;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrganizationBookingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':admin']);
    }

    /**
     * Display a listing of all organization booking requests with admin oversight.
     */
    public function index()
    {
        $requests = OrganizationBookingRequest::with(['requestor', 'organization', 'organization.adviser', 'approvedBy', 'rejectedBy'])
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')")
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Get comprehensive statistics
        $stats = [
            'total' => OrganizationBookingRequest::count(),
            'pending' => OrganizationBookingRequest::pending()->count(),
            'approved' => OrganizationBookingRequest::approved()->count(),
            'rejected' => OrganizationBookingRequest::rejected()->count(),
            'overdue' => OrganizationBookingRequest::needingReminder()->count(),
            'this_month' => OrganizationBookingRequest::whereMonth('created_at', now()->month)->count(),
            'last_month' => OrganizationBookingRequest::whereMonth('created_at', now()->subMonth()->month)->count(),
        ];

        // Get organizations without advisers
        $orgsWithoutAdvisers = Organization::whereNull('adviser_id')->count();
        $stats['orgs_without_advisers'] = $orgsWithoutAdvisers;

        return view('admin.organization-bookings.index', compact('requests', 'stats'));
    }

    /**
     * Display the specified organization booking request with full admin view.
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

        // Get available advisers for potential reassignment
        $availableAdvisers = User::where('role', 'adviser')->orderBy('first_name')->get();

        // Check if this request is overdue
        $isOverdue = $organizationBookingRequest->is_overdue;
        $daysPending = $organizationBookingRequest->adviser_notified_at ? 
            $organizationBookingRequest->adviser_notified_at->diffInDays(now()) : 0;

        return view('admin.organization-bookings.show', compact(
            'organizationBookingRequest', 
            'availableAdvisers', 
            'isOverdue', 
            'daysPending'
        ));
    }

    /**
     * Reassign the organization's adviser (affects all future requests).
     */
    public function reassignAdviser(Request $request, OrganizationBookingRequest $organizationBookingRequest)
    {
        $request->validate([
            'adviser_id' => 'required|exists:users,id',
        ]);

        $newAdviser = User::findOrFail($request->adviser_id);
        
        // Ensure the new adviser has the correct role
        if ($newAdviser->role !== 'adviser') {
            return redirect()->back()->with('error', 'Selected user is not an adviser.');
        }

        $organization = $organizationBookingRequest->organization;
        $oldAdviser = $organization->adviser;

        // Update organization adviser
        $organization->update(['adviser_id' => $newAdviser->id]);

        // Reset notification timestamp for this specific request
        $organizationBookingRequest->update([
            'adviser_notified_at' => null,
            'staff_reminded_at' => null,
        ]);

        // Send notification to new adviser
        $notificationService = app(\App\Services\OrganizationBookingNotificationService::class);
        $notificationService->notifyAdviserOfNewRequest($organizationBookingRequest);

        $message = "Organization adviser changed from " . 
                  ($oldAdviser ? $oldAdviser->full_name ?? $oldAdviser->name : 'None') . 
                  " to " . ($newAdviser->full_name ?? $newAdviser->name) . ". " .
                  "The new adviser has been notified about this request.";

        return redirect()->back()
            ->with('status', 'adviser-reassigned')
            ->with('message', $message);
    }

    /**
     * Generate organization booking reports and statistics.
     */
    public function reports(Request $request)
    {
        $period = $request->get('period', 'month'); // day, week, month, year

        $startDate = match($period) {
            'day' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        $endDate = match($period) {
            'day' => now()->endOfDay(),
            'week' => now()->endOfWeek(),
            'month' => now()->endOfMonth(),
            'year' => now()->endOfYear(),
            default => now()->endOfMonth(),
        };

        // Get requests within period
        $requests = OrganizationBookingRequest::with(['organization', 'requestor'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        // Calculate statistics
        $stats = [
            'total_requests' => $requests->count(),
            'approved_requests' => $requests->where('status', 'approved')->count(),
            'rejected_requests' => $requests->where('status', 'rejected')->count(),
            'pending_requests' => $requests->where('status', 'pending')->count(),
            'average_response_time' => $this->calculateAverageResponseTime($requests),
        ];

        // Requests by organization
        $requestsByOrganization = $requests->groupBy('organization.org_name')
            ->map(function ($orgRequests) {
                return [
                    'total' => $orgRequests->count(),
                    'approved' => $orgRequests->where('status', 'approved')->count(),
                    'rejected' => $orgRequests->where('status', 'rejected')->count(),
                    'pending' => $orgRequests->where('status', 'pending')->count(),
                ];
            });

        // Requests by status over time
        $requestsTrend = $this->getRequestsTrend($period, $startDate, $endDate);

        // Top requestors
        $topRequestors = $requests->groupBy('requestor.email')
            ->map(function ($userRequests) {
                $user = $userRequests->first()->requestor;
                return [
                    'name' => $user->full_name ?? $user->name,
                    'email' => $user->email,
                    'count' => $userRequests->count(),
                    'approved' => $userRequests->where('status', 'approved')->count(),
                ];
            })
            ->sortByDesc('count')
            ->take(10);

        return view('admin.organization-bookings.reports', compact(
            'stats',
            'requestsByOrganization',
            'requestsTrend',
            'topRequestors',
            'period',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Calculate average response time for requests.
     */
    private function calculateAverageResponseTime($requests)
    {
        $respondedRequests = $requests->whereNotNull('adviser_responded_at');
        
        if ($respondedRequests->isEmpty()) {
            return 0;
        }

        $totalHours = $respondedRequests->sum(function ($request) {
            return $request->adviser_notified_at && $request->adviser_responded_at 
                ? $request->adviser_notified_at->diffInHours($request->adviser_responded_at)
                : 0;
        });

        return round($totalHours / $respondedRequests->count(), 1);
    }

    /**
     * Get requests trend data for charts.
     */
    private function getRequestsTrend($period, $startDate, $endDate)
    {
        $format = match($period) {
            'day' => '%Y-%m-%d %H:00:00',
            'week' => '%Y-%m-%d',
            'month' => '%Y-%m-%d',
            'year' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        return OrganizationBookingRequest::select([
                DB::raw("DATE_FORMAT(created_at, '$format') as date"),
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved"),
                DB::raw("SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected"),
                DB::raw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending"),
            ])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }
}