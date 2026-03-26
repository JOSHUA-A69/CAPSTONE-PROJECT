<?php

namespace App\Http\Controllers\Requestor;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrganizationBookingRequest as BookingFormRequest;
use App\Models\Organization;
use App\Models\OrganizationBookingRequest;
use App\Models\OrganizationBookingCancellation;
use App\Services\OrganizationBookingNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class OrganizationBookingController extends Controller
{
    protected $notificationService;

    public function __construct(OrganizationBookingNotificationService $notificationService)
    {
        $this->middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':requestor,admin,staff']);
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of the user's organization booking requests.
     */
    public function index()
    {
        $requests = OrganizationBookingRequest::with(['organization', 'organization.adviser', 'organizations'])
            ->where('requestor_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('requestor.organization-bookings.index', compact('requests'));
    }

    /**
     * Show the form for creating a new organization booking request.
     */
    public function create()
    {
        $organizations = Organization::with('adviser')
            ->whereNotNull('adviser_id')
            ->orderBy('org_name')
            ->get();

        return view('requestor.organization-bookings.create', compact('organizations'));
    }

    /**
     * Store a newly created organization booking request in storage.
     */
    public function store(BookingFormRequest $request)
    {
        $data = $request->validated();
        $data['requestor_id'] = Auth::id();
        $data['submitted_at'] = now();
        $data['cancellation_restricted'] = true;

        // Start session timeout (30 minutes)
        $data['session_started_at'] = now();
        $data['session_expires_at'] = now()->addMinutes(30);
        $data['session_expired'] = false;

        // Combine date with time_in to create requested_date
        if (isset($data['requested_date'])) {
            $timeIn = $request->input('time_in');
            if ($timeIn) {
                $data['requested_date'] = \Carbon\Carbon::parse($data['requested_date'].' '.$timeIn, config('app.timezone'));
            } else {
                $data['requested_date'] = \Carbon\Carbon::parse($data['requested_date'], config('app.timezone'));
            }
        }

        // Remove separate time fields since they're stored independently
        unset($data['requested_time']);

        // Handle organization_ids if multiple organizations selected
        $organizationIds = $request->input('organization_ids', []);
        $primaryOrgId = $request->input('organization_id');

        // If no primary org set but organization_ids exist, use first one
        if (empty($primaryOrgId) && !empty($organizationIds)) {
            $primaryOrgId = $organizationIds[0];
        }

        // Ensure primary org is in data for backward compatibility
        $data['organization_id'] = $primaryOrgId;

        // Remove organization_ids from data as it's stored in pivot table
        unset($data['organization_ids']);

        DB::beginTransaction();
        try {
            $bookingRequest = OrganizationBookingRequest::create($data);

            // Attach multiple organizations if provided
            if (!empty($organizationIds)) {
                $orgData = [];
                foreach ($organizationIds as $orgId) {
                    $orgData[$orgId] = [
                        'is_primary' => ($orgId == $primaryOrgId),
                        'notified' => false,
                        'approval_status' => 'pending',
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
                $bookingRequest->organizations()->attach($orgData);
            }

            DB::commit();

            // Send notification to organization adviser(s)
            $this->notificationService->notifyAdviserOfNewRequest($bookingRequest);

            return Redirect::route('requestor.organization-bookings.index')
                ->with('status', 'organization-booking-submitted')
                ->with('message', 'Your organization booking request has been submitted successfully. The organization adviser(s) have been notified and will review your request.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'An error occurred while submitting your request. Please try again.');
        }
    }

    /**
     * Display the specified organization booking request.
     */
    public function show(OrganizationBookingRequest $organizationBookingRequest)
    {
        // Ensure user can only view their own requests
        if ($organizationBookingRequest->requestor_id !== Auth::id() &&
            !in_array(Auth::user()->role, ['admin', 'staff'])) {
            abort(403, 'Unauthorized to view this request.');
        }

        $organizationBookingRequest->load([
            'organization',
            'organization.adviser',
            'organizations',
            'organizations.adviser',
            'approvedBy',
            'rejectedBy',
            'cancellationRequests',
            'pendingCancellation'
        ]);

        return view('requestor.organization-bookings.show', compact('organizationBookingRequest'));
    }

    /**
     * Show the form for editing the specified organization booking request.
     * Only allows editing if status is 'pending'
     */
    public function edit(OrganizationBookingRequest $organizationBookingRequest)
    {
        // Ensure user can only edit their own requests
        if ($organizationBookingRequest->requestor_id !== Auth::id()) {
            abort(403, 'Unauthorized to edit this request.');
        }

        // Only allow editing if status is pending
        if ($organizationBookingRequest->status !== 'pending') {
            return Redirect::route('requestor.organization-bookings.show', $organizationBookingRequest)
                ->with('error', 'You can only edit requests that are still pending review.');
        }

        $organizations = Organization::with('adviser')
            ->whereNotNull('adviser_id')
            ->orderBy('org_name')
            ->get();

        // Get currently attached organization IDs
        $selectedOrgIds = $organizationBookingRequest->organizations->pluck('org_id')->toArray();

        return view('requestor.organization-bookings.edit', compact('organizationBookingRequest', 'organizations', 'selectedOrgIds'));
    }

    /**
     * Update the specified organization booking request in storage.
     */
    public function update(BookingFormRequest $request, OrganizationBookingRequest $organizationBookingRequest)
    {
        // Ensure user can only update their own requests
        if ($organizationBookingRequest->requestor_id !== Auth::id()) {
            abort(403, 'Unauthorized to update this request.');
        }

        // Only allow updating if status is pending
        if ($organizationBookingRequest->status !== 'pending') {
            return Redirect::route('requestor.organization-bookings.show', $organizationBookingRequest)
                ->with('error', 'You can only edit requests that are still pending review.');
        }

        $data = $request->validated();

        // Handle date/time combination
        if (isset($data['requested_date'])) {
            $timeIn = $request->input('time_in');
            if ($timeIn) {
                $data['requested_date'] = \Carbon\Carbon::parse($data['requested_date'].' '.$timeIn, config('app.timezone'));
            } else {
                $data['requested_date'] = \Carbon\Carbon::parse($data['requested_date'], config('app.timezone'));
            }
        }
        unset($data['requested_time']);

        // Handle organization_ids
        $organizationIds = $request->input('organization_ids', []);
        $primaryOrgId = $request->input('organization_id');

        if (empty($primaryOrgId) && !empty($organizationIds)) {
            $primaryOrgId = $organizationIds[0];
        }

        $data['organization_id'] = $primaryOrgId;
        unset($data['organization_ids']);

        DB::beginTransaction();
        try {
            $organizationBookingRequest->update($data);

            // Sync organizations
            if (!empty($organizationIds)) {
                $orgData = [];
                foreach ($organizationIds as $orgId) {
                    $orgData[$orgId] = [
                        'is_primary' => ($orgId == $primaryOrgId),
                        'notified' => false,
                        'approval_status' => 'pending',
                        'updated_at' => now()
                    ];
                }
                $organizationBookingRequest->organizations()->sync($orgData);
            }

            DB::commit();

            // If organization changed, re-notify the new adviser
            if ($organizationBookingRequest->wasChanged('organization_id')) {
                $organizationBookingRequest->update(['adviser_notified_at' => null]);
                $this->notificationService->notifyAdviserOfNewRequest($organizationBookingRequest);
            }

            return Redirect::route('requestor.organization-bookings.show', $organizationBookingRequest)
                ->with('status', 'organization-booking-updated')
                ->with('message', 'Your organization booking request has been updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'An error occurred while updating your request.');
        }
    }

    /**
     * Remove the specified organization booking request from storage.
     * Only allows deletion if status is 'pending'
     */
    public function destroy(OrganizationBookingRequest $organizationBookingRequest)
    {
        // Ensure user can only delete their own requests
        if ($organizationBookingRequest->requestor_id !== Auth::id()) {
            abort(403, 'Unauthorized to delete this request.');
        }

        // Only allow deletion if status is pending
        if ($organizationBookingRequest->status !== 'pending') {
            return Redirect::route('requestor.organization-bookings.index')
                ->with('error', 'You can only delete requests that are still pending review.');
        }

        $organizationBookingRequest->delete();

        return Redirect::route('requestor.organization-bookings.index')
            ->with('status', 'organization-booking-deleted')
            ->with('message', 'Your organization booking request has been deleted successfully.');
    }

    /**
     * Request cancellation for an approved organization booking
     * Requires adviser approval for confirmed/approved bookings
     */
    public function requestCancellation(Request $request, OrganizationBookingRequest $organizationBookingRequest)
    {
        // Ensure user can only request cancellation for their own requests
        if ($organizationBookingRequest->requestor_id !== Auth::id()) {
            abort(403, 'Unauthorized to cancel this request.');
        }

        // Check if cancellation is allowed
        if (!$organizationBookingRequest->canRequestCancellation()) {
            return back()->with('error', 'This booking cannot be cancelled.');
        }

        // Check for pending cancellation
        if ($organizationBookingRequest->hasPendingCancellation()) {
            return back()->with('error', 'A cancellation request is already pending for this booking.');
        }

        $request->validate([
            'reason' => 'required|string|max:1000'
        ]);

        // If booking requires adviser approval for cancellation
        if ($organizationBookingRequest->requiresCancellationApproval()) {
            // Create cancellation request
            OrganizationBookingCancellation::create([
                'booking_request_id' => $organizationBookingRequest->id,
                'requested_by' => Auth::id(),
                'reason' => $request->reason,
                'status' => 'pending'
            ]);

            // Notify adviser about cancellation request
            $this->notificationService->notifyAdviserOfCancellationRequest($organizationBookingRequest);

            return back()->with('status', 'cancellation-requested')
                ->with('message', 'Cancellation request has been submitted. The adviser will review and approve or reject your request.');
        } else {
            // Direct cancellation for pending bookings
            $organizationBookingRequest->update([
                'status' => 'cancelled'
            ]);

            return Redirect::route('requestor.organization-bookings.index')
                ->with('status', 'booking-cancelled')
                ->with('message', 'Your organization booking has been cancelled successfully.');
        }
    }

    /**
     * Extend the session for an organization booking
     */
    public function extendSession(OrganizationBookingRequest $organizationBookingRequest)
    {
        // Ensure user can only extend their own requests
        if ($organizationBookingRequest->requestor_id !== Auth::id()) {
            abort(403, 'Unauthorized.');
        }

        if ($organizationBookingRequest->hasActiveSession()) {
            $organizationBookingRequest->extendSession(15);
            return response()->json([
                'success' => true,
                'remaining_seconds' => $organizationBookingRequest->remaining_session_time
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Session has already expired'
        ], 400);
    }

    /**
     * Get organization details for AJAX requests
     */
    public function getOrganizationDetails(Organization $organization)
    {
        $organization->load('adviser');

        return response()->json([
            'org_name' => $organization->org_name,
            'org_desc' => $organization->org_desc,
            'adviser' => $organization->adviser ? [
                'name' => $organization->adviser->full_name ?? $organization->adviser->name,
                'email' => $organization->adviser->email,
            ] : null,
        ]);
    }
}
