<?php

namespace App\Http\Controllers\Requestor;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrganizationBookingRequest as BookingFormRequest;
use App\Models\Organization;
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
        $this->middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':requestor,admin,staff']);
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of the user's organization booking requests.
     */
    public function index()
    {
        $requests = OrganizationBookingRequest::with(['organization', 'organization.adviser'])
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
        
        // Combine separate date + time inputs into a single datetime when applicable
        if (isset($data['requested_date'])) {
            $time = $request->input('requested_time');
            if ($time) {
                $data['requested_date'] = \Carbon\Carbon::parse($data['requested_date'].' '.$time, config('app.timezone'));
            } else {
                $data['requested_date'] = \Carbon\Carbon::parse($data['requested_date'], config('app.timezone'));
            }
        }
        unset($data['requested_time']);

        $bookingRequest = OrganizationBookingRequest::create($data);

        // Send notification to organization adviser
        $this->notificationService->notifyAdviserOfNewRequest($bookingRequest);

        return Redirect::route('requestor.organization-bookings.index')
            ->with('status', 'organization-booking-submitted')
            ->with('message', 'Your organization booking request has been submitted successfully. The organization adviser has been notified and will review your request.');
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

        $organizationBookingRequest->load(['organization', 'organization.adviser', 'approvedBy', 'rejectedBy']);

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

        return view('requestor.organization-bookings.edit', compact('organizationBookingRequest', 'organizations'));
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
        $organizationBookingRequest->update($data);

        // If organization changed, re-notify the new adviser
        if ($organizationBookingRequest->wasChanged('organization_id')) {
            $organizationBookingRequest->update(['adviser_notified_at' => null]);
            $this->notificationService->notifyAdviserOfNewRequest($organizationBookingRequest);
        }

        return Redirect::route('requestor.organization-bookings.show', $organizationBookingRequest)
            ->with('status', 'organization-booking-updated')
            ->with('message', 'Your organization booking request has been updated successfully.');
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
        
        // Support both create-style (date + time) and edit-style (datetime-local) inputs
        if (isset($data['requested_date'])) {
            $time = $request->input('requested_time');
            if ($time) {
                $data['requested_date'] = \Carbon\Carbon::parse($data['requested_date'].' '.$time, config('app.timezone'));
            } else {
                $data['requested_date'] = \Carbon\Carbon::parse($data['requested_date'], config('app.timezone'));
            }
        }
        unset($data['requested_time']);

        return Redirect::route('requestor.organization-bookings.index')
            ->with('status', 'organization-booking-deleted')
            ->with('message', 'Your organization booking request has been deleted successfully.');
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