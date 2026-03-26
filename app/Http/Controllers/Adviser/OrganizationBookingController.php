<?php

namespace App\Http\Controllers\Adviser;

use App\Http\Controllers\Controller;
use App\Models\OrganizationBookingRequest;
use App\Models\OrganizationBookingCancellation;
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

        $query = OrganizationBookingRequest::with(['requestor', 'organization', 'organizations'])
            ->where(function ($q) use ($adviserOrganizations) {
                $q->whereIn('organization_id', $adviserOrganizations)
                  ->orWhereHas('organizations', function ($subQ) use ($adviserOrganizations) {
                      $subQ->whereIn('organization_id', $adviserOrganizations);
                  });
            });

        // Apply status filter if provided
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $requests = $query->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')")
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Get summary statistics (counts)
        $counts = [
            'all' => OrganizationBookingRequest::where(function ($q) use ($adviserOrganizations) {
                $q->whereIn('organization_id', $adviserOrganizations)
                  ->orWhereHas('organizations', function ($subQ) use ($adviserOrganizations) {
                      $subQ->whereIn('organization_id', $adviserOrganizations);
                  });
            })->count(),
            'pending' => OrganizationBookingRequest::where(function ($q) use ($adviserOrganizations) {
                $q->whereIn('organization_id', $adviserOrganizations)
                  ->orWhereHas('organizations', function ($subQ) use ($adviserOrganizations) {
                      $subQ->whereIn('organization_id', $adviserOrganizations);
                  });
            })->pending()->count(),
            'approved' => OrganizationBookingRequest::where(function ($q) use ($adviserOrganizations) {
                $q->whereIn('organization_id', $adviserOrganizations)
                  ->orWhereHas('organizations', function ($subQ) use ($adviserOrganizations) {
                      $subQ->whereIn('organization_id', $adviserOrganizations);
                  });
            })->approved()->count(),
            'rejected' => OrganizationBookingRequest::where(function ($q) use ($adviserOrganizations) {
                $q->whereIn('organization_id', $adviserOrganizations)
                  ->orWhereHas('organizations', function ($subQ) use ($adviserOrganizations) {
                      $subQ->whereIn('organization_id', $adviserOrganizations);
                  });
            })->rejected()->count(),
        ];

        // Get pending cancellation requests count
        $pendingCancellationCount = OrganizationBookingCancellation::whereHas('bookingRequest', function ($q) use ($adviserOrganizations) {
            $q->whereIn('organization_id', $adviserOrganizations);
        })->pending()->count();

        return view('adviser.organization-bookings.index', compact('requests', 'counts', 'pendingCancellationCount'));
    }

    /**
     * Display the specified organization booking request.
     */
    public function show(OrganizationBookingRequest $organizationBookingRequest)
    {
        // Ensure adviser can only view requests for their organizations
        $adviserOrganizations = Auth::user()->organizations->pluck('org_id');

        $isAuthorized = $adviserOrganizations->contains($organizationBookingRequest->organization_id) ||
            $organizationBookingRequest->organizations->whereIn('org_id', $adviserOrganizations)->isNotEmpty();

        if (!$isAuthorized) {
            abort(403, 'You are not authorized to view this request.');
        }

        $organizationBookingRequest->load([
            'requestor',
            'organization',
            'organizations',
            'organizations.adviser',
            'approvedBy',
            'rejectedBy',
            'cancellationRequests',
            'pendingCancellation'
        ]);

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
     * Display pending cancellation requests.
     */
    public function cancellationRequests()
    {
        $adviserOrganizations = Auth::user()->organizations->pluck('org_id');

        $cancellationRequests = OrganizationBookingCancellation::with(['bookingRequest', 'bookingRequest.requestor', 'bookingRequest.organization', 'requestedBy'])
            ->whereHas('bookingRequest', function ($q) use ($adviserOrganizations) {
                $q->whereIn('organization_id', $adviserOrganizations);
            })
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')")
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('adviser.organization-bookings.cancellation-requests', compact('cancellationRequests'));
    }

    /**
     * Approve a cancellation request.
     */
    public function approveCancellation(Request $request, OrganizationBookingCancellation $cancellation)
    {
        $adviserOrganizations = Auth::user()->organizations->pluck('org_id');

        if (!$adviserOrganizations->contains($cancellation->bookingRequest->organization_id)) {
            abort(403, 'You are not authorized to approve this cancellation.');
        }

        if ($cancellation->status !== 'pending') {
            return Redirect::back()->with('error', 'This cancellation request has already been processed.');
        }

        $request->validate([
            'response' => 'nullable|string|max:1000',
        ]);

        $cancellation->approve(Auth::user(), $request->input('response'));

        // Notify requestor of cancellation approval
        $this->notificationService->notifyRequestorOfCancellationApproval($cancellation);

        return Redirect::route('adviser.organization-bookings.cancellation-requests')
            ->with('status', 'cancellation-approved')
            ->with('message', 'The cancellation request has been approved. The booking has been cancelled and the requestor has been notified.');
    }

    /**
     * Reject a cancellation request.
     */
    public function rejectCancellation(Request $request, OrganizationBookingCancellation $cancellation)
    {
        $adviserOrganizations = Auth::user()->organizations->pluck('org_id');

        if (!$adviserOrganizations->contains($cancellation->bookingRequest->organization_id)) {
            abort(403, 'You are not authorized to reject this cancellation.');
        }

        if ($cancellation->status !== 'pending') {
            return Redirect::back()->with('error', 'This cancellation request has already been processed.');
        }

        $request->validate([
            'response' => 'required|string|max:1000',
        ]);

        $cancellation->reject(Auth::user(), $request->input('response'));

        // Notify requestor of cancellation rejection
        $this->notificationService->notifyRequestorOfCancellationRejection($cancellation);

        return Redirect::route('adviser.organization-bookings.cancellation-requests')
            ->with('status', 'cancellation-rejected')
            ->with('message', 'The cancellation request has been rejected. The requestor has been notified.');
    }

    /**
     * Get organization booking requests for calendar display.
     */
    public function calendar()
    {
        // Get organizations where this adviser is assigned
        $adviserOrganizations = Auth::user()->organizations->pluck('org_id');

        $approvedRequests = OrganizationBookingRequest::with(['requestor', 'organization', 'organizations'])
            ->where(function ($q) use ($adviserOrganizations) {
                $q->whereIn('organization_id', $adviserOrganizations)
                  ->orWhereHas('organizations', function ($subQ) use ($adviserOrganizations) {
                      $subQ->whereIn('organization_id', $adviserOrganizations);
                  });
            })
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

        $q = OrganizationBookingRequest::with(['requestor', 'organization', 'organizations'])
            ->where(function ($query) use ($adviserOrganizations) {
                $query->whereIn('organization_id', $adviserOrganizations)
                      ->orWhereHas('organizations', function ($subQ) use ($adviserOrganizations) {
                          $subQ->whereIn('organization_id', $adviserOrganizations);
                      });
            });

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
            // Build the time display based on available fields
            $timeDisplay = '';
            if ($r->time_in && $r->time_out) {
                $timeDisplay = \Carbon\Carbon::createFromFormat('H:i:s', $r->time_in)->format('g:i A') . ' - ' .
                              \Carbon\Carbon::createFromFormat('H:i:s', $r->time_out)->format('g:i A');
            } elseif ($r->requested_date) {
                $timeDisplay = $r->requested_date->format('g:i A');
            }

            $events[] = [
                'id' => $r->id,
                'title' => $r->activity_name . ($timeDisplay ? " ({$timeDisplay})" : ''),
                // Emit start as local naive datetime to avoid UTC shifts
                'start' => optional($r->requested_date)->setTimezone(config('app.timezone'))->format('Y-m-d\TH:i:s'),
                'allDay' => false,
                'extendedProps' => [
                    'id' => $r->id,
                    'status' => $r->status,
                    'is_overdue' => method_exists($r, 'getIsOverdueAttribute') ? $r->is_overdue : false,
                    'organization' => optional($r->organization)->org_name,
                    'all_organizations' => $r->all_organization_names ?? optional($r->organization)->org_name,
                    'requestor' => optional($r->requestor)->full_name ?? optional($r->requestor)->name,
                    'participants' => $r->estimated_participants,
                    'servers_needed' => $r->servers_needed,
                    'venue' => $r->requested_venue,
                    'purpose' => $r->purpose,
                    'time_in' => $r->time_in,
                    'time_out' => $r->time_out,
                    'time_display' => $timeDisplay,
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

        $pendingRequests = OrganizationBookingRequest::with(['requestor', 'organization', 'organizations'])
            ->where(function ($q) use ($adviserOrganizations) {
                $q->whereIn('organization_id', $adviserOrganizations)
                  ->orWhereHas('organizations', function ($subQ) use ($adviserOrganizations) {
                      $subQ->whereIn('organization_id', $adviserOrganizations);
                  });
            })
            ->pending()
            ->orderBy('created_at')
            ->limit(5)
            ->get();

        $overdueCount = OrganizationBookingRequest::where(function ($q) use ($adviserOrganizations) {
            $q->whereIn('organization_id', $adviserOrganizations)
              ->orWhereHas('organizations', function ($subQ) use ($adviserOrganizations) {
                  $subQ->whereIn('organization_id', $adviserOrganizations);
              });
        })->needingReminder()->count();

        $pendingCancellationCount = OrganizationBookingCancellation::whereHas('bookingRequest', function ($q) use ($adviserOrganizations) {
            $q->whereIn('organization_id', $adviserOrganizations);
        })->pending()->count();

        return response()->json([
            'pending_requests' => $pendingRequests,
            'overdue_count' => $overdueCount,
            'pending_cancellation_count' => $pendingCancellationCount,
        ]);
    }
}
