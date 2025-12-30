<?php

namespace App\Http\Controllers\Adviser;

use App\Http\Controllers\Controller;
use App\Models\OrganizationBookingRequest;
use App\Services\OrganizationBookingNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class OrganizationBookingController extends Controller
{
    protected $notificationService;

    public function __construct(OrganizationBookingNotificationService $notificationService)
    {
        $this->middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':adviser']);
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of organization booking requests for this adviser's organizations.
     */
    public function index(Request $request)
    {
        // Get organizations where this adviser is assigned
        $adviserOrganizations = Auth::user()->organizations->pluck('org_id');

        $query = OrganizationBookingRequest::with(['requestor', 'organization'])
            ->whereIn('organization_id', $adviserOrganizations);

        // Apply status filter if provided
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $requests = $query->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')")
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Get summary statistics (counts)
        $counts = [
            'all' => OrganizationBookingRequest::whereIn('organization_id', $adviserOrganizations)->count(),
            'pending' => OrganizationBookingRequest::whereIn('organization_id', $adviserOrganizations)->pending()->count(),
            'approved' => OrganizationBookingRequest::whereIn('organization_id', $adviserOrganizations)->approved()->count(),
            'rejected' => OrganizationBookingRequest::whereIn('organization_id', $adviserOrganizations)->rejected()->count(),
        ];

        return view('adviser.organization-bookings.index', compact('requests', 'counts'));
    }

    /**
     * Display the specified organization booking request.
     */
    public function show(OrganizationBookingRequest $organizationBookingRequest)
    {
        // Ensure adviser can only view requests for their organizations
        $adviserOrganizations = Auth::user()->organizations->pluck('org_id');
        
        if (!$adviserOrganizations->contains($organizationBookingRequest->organization_id)) {
            abort(403, 'You are not authorized to view this request.');
        }

        $organizationBookingRequest->load(['requestor', 'organization', 'approvedBy', 'rejectedBy']);

        return view('adviser.organization-bookings.show', compact('organizationBookingRequest'));
    }

    /**
     * Approve an organization booking request.
     */
    public function approve(Request $request, OrganizationBookingRequest $organizationBookingRequest)
    {
        // Validate adviser authorization
        $adviserOrganizations = Auth::user()->organizations->pluck('org_id');
        
        if (!$adviserOrganizations->contains($organizationBookingRequest->organization_id)) {
            abort(403, 'You are not authorized to approve this request.');
        }

        // Only allow approval of pending requests
        if ($organizationBookingRequest->status !== 'pending') {
            return Redirect::back()->with('error', 'Only pending requests can be approved.');
        }

        $request->validate([
            'comments' => 'nullable|string|max:1000',
        ]);

        $comments = $request->input('comments');
        
        // Approve the request
        $organizationBookingRequest->approve(Auth::id(), $comments);

        // Send notification to requestor
        $this->notificationService->notifyRequestorOfApproval($organizationBookingRequest, $comments);

        return Redirect::route('adviser.organization-bookings.show', $organizationBookingRequest)
            ->with('status', 'request-approved')
            ->with('message', 'The booking request has been approved successfully. The requestor has been notified.');
    }

    /**
     * Reject an organization booking request.
     */
    public function reject(Request $request, OrganizationBookingRequest $organizationBookingRequest)
    {
        // Validate adviser authorization
        $adviserOrganizations = Auth::user()->organizations->pluck('org_id');
        
        if (!$adviserOrganizations->contains($organizationBookingRequest->organization_id)) {
            abort(403, 'You are not authorized to reject this request.');
        }

        // Only allow rejection of pending requests
        if ($organizationBookingRequest->status !== 'pending') {
            return Redirect::back()->with('error', 'Only pending requests can be rejected.');
        }

        $request->validate([
            'reason' => 'required|string|max:1000',
            'comments' => 'nullable|string|max:1000',
        ]);

        $reason = $request->input('reason');
        $comments = $request->input('comments');
        
        // Reject the request
        $organizationBookingRequest->reject(Auth::id(), $reason, $comments);

        // Send notification to requestor
        $this->notificationService->notifyRequestorOfRejection($organizationBookingRequest, $reason, $comments);

        return Redirect::route('adviser.organization-bookings.show', $organizationBookingRequest)
            ->with('status', 'request-rejected')
            ->with('message', 'The booking request has been rejected. The requestor has been notified with the reason.');
    }

    /**
     * Get organization booking requests for calendar display.
     */
    public function calendar()
    {
        // Get organizations where this adviser is assigned
        $adviserOrganizations = Auth::user()->organizations->pluck('org_id');

        $approvedRequests = OrganizationBookingRequest::with(['requestor', 'organization'])
            ->whereIn('organization_id', $adviserOrganizations)
            ->where('status', 'approved')
            ->whereDate('requested_date', '>=', now()->toDateString())
            ->orderBy('requested_date')
            ->get();

        return view('adviser.organization-bookings.calendar', compact('approvedRequests'));
    }

    /**
     * Get organization booking requests data for AJAX calendar.
     */
    public function calendarData(Request $request)
    {
        // Get organizations where this adviser is assigned
        $adviserOrganizations = Auth::user()->organizations->pluck('org_id');

        $filter = (string) $request->query('filter', '');
        $startParam = $request->query('start');
        $endParam = $request->query('end');

        $q = OrganizationBookingRequest::with(['requestor', 'organization'])
            ->whereIn('organization_id', $adviserOrganizations);

        // Status filter
        if (in_array($filter, ['pending','approved','rejected'])) {
            $q->where('status', $filter);
        } elseif ($filter === 'past') {
            $q->where('status', 'approved')->whereDate('requested_date', '<', now()->toDateString());
        }

        // Date range (FullCalendar passes ISO date strings)
        if ($startParam && $endParam) {
            $q->whereBetween('requested_date', [$startParam, $endParam]);
        }

        $requests = $q->orderBy('requested_date')->get();

        $events = [];
        foreach ($requests as $r) {
            $events[] = [
                'id' => $r->id,
                'title' => $r->activity_name,
                // Use stored datetime as-is (no timezone mutation)
                'start' => optional($r->requested_date)->toIso8601String(),
                'extendedProps' => [
                    'id' => $r->id,
                    'status' => $r->status,
                    'is_overdue' => method_exists($r, 'getIsOverdueAttribute') ? $r->is_overdue : false,
                    'organization' => optional($r->organization)->org_name,
                    'requestor' => optional($r->requestor)->full_name ?? optional($r->requestor)->name,
                    'participants' => $r->estimated_participants,
                    'venue' => $r->requested_venue,
                    'purpose' => $r->purpose,
                ],
            ];
        }

        return response()->json($events);
    }

    /**
     * Get summary of pending requests needing attention.
     */
    public function summary()
    {
        $adviserOrganizations = Auth::user()->organizations->pluck('org_id');

        $pendingRequests = OrganizationBookingRequest::with(['requestor', 'organization'])
            ->whereIn('organization_id', $adviserOrganizations)
            ->pending()
            ->orderBy('created_at')
            ->limit(5)
            ->get();

        $overdueCount = OrganizationBookingRequest::whereIn('organization_id', $adviserOrganizations)
            ->needingReminder()
            ->count();

        return response()->json([
            'pending_requests' => $pendingRequests,
            'overdue_count' => $overdueCount,
        ]);
    }
}