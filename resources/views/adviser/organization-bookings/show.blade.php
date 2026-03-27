@extends('layouts.app')

@section('title', 'Booking Request Details')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <!-- Header -->

    <!-- Success/Error Messages -->
    @if(session('status') === 'request-approved')
        <div class="mb-6 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-xl p-4">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <p class="text-green-700 dark:text-green-300 font-medium">{{ session('message') ?? 'Request has been approved successfully!' }}</p>
            </div>
        </div>
    @endif

    @if(session('status') === 'request-rejected')
        <div class="mb-6 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl p-4">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <p class="text-red-700 dark:text-red-300 font-medium">{{ session('message') ?? 'Request has been rejected.' }}</p>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content - Left Side -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Organization Information Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        Organization Information
                    </h2>
                </div>
                <div class="p-6">
                    @php
                        $adviserOrganizationIds = auth()->user()->organizations->pluck('org_id')->toArray();
                        $linkedOrganizations = $organizationBookingRequest->organizations ?? collect();
                    @endphp

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                Organization{{ $linkedOrganizations->count() > 1 ? 's' : '' }}
                            </label>

                            @if($linkedOrganizations->isNotEmpty())
                                <div class="space-y-2">
                                    @foreach($linkedOrganizations as $org)
                                        @php
                                            $isAdviserOrganization = in_array($org->org_id, $adviserOrganizationIds, true);
                                        @endphp
                                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-3 bg-gray-50 dark:bg-gray-700/40">
                                            <div class="flex items-start justify-between gap-3">
                                                <p class="text-gray-900 dark:text-white font-medium">{{ $org->org_name }}</p>
                                                @if($isAdviserOrganization)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-teal-100 text-teal-800 dark:bg-teal-900/40 dark:text-teal-200">
                                                        Your Organization
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                                                Requested Servers: <span class="font-semibold">{{ (int) ($org->pivot->server_quantity ?? 0) }}</span>
                                            </p>
                                            @if($org->adviser)
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                    Adviser: {{ $org->adviser->full_name ?? $org->adviser->name }}
                                                </p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-900 dark:text-white font-medium">{{ $organizationBookingRequest->organization->org_name }}</p>
                            @endif
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Requestor</label>
                            <p class="text-gray-900 dark:text-white font-medium">{{ $organizationBookingRequest->requestor->full_name }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $organizationBookingRequest->requestor->email }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity Details Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                        Activity Details
                    </h2>
                </div>
                <div class="p-6 space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Activity Name</label>
                        <p class="text-gray-900 dark:text-white font-medium text-lg">{{ $organizationBookingRequest->activity_name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Purpose</label>
                        <p class="text-gray-900 dark:text-white">{{ $organizationBookingRequest->purpose ?? 'No purpose specified' }}</p>
                    </div>

                    @if($organizationBookingRequest->activity_details)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Additional Details</label>
                        <p class="text-gray-900 dark:text-white">{{ $organizationBookingRequest->activity_details }}</p>
                    </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Requested Date & Time</label>
                            <p class="text-gray-900 dark:text-white font-medium flex items-center">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ $organizationBookingRequest->requested_date ? \Carbon\Carbon::parse($organizationBookingRequest->requested_date)->format('M j, Y \a\t g:i A') : 'Not specified' }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Requested Venue</label>
                            <p class="text-gray-900 dark:text-white font-medium flex items-center">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                {{ $organizationBookingRequest->requested_venue ?? 'Not specified' }}
                            </p>
                        </div>
                        @if($organizationBookingRequest->estimated_participants)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Expected Participants</label>
                            <p class="text-gray-900 dark:text-white font-medium flex items-center">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                {{ $organizationBookingRequest->estimated_participants }} people
                            </p>
                        </div>
                        @endif
                    </div>

                    @if($organizationBookingRequest->special_requirements)
                    <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Special Requirements</label>
                        <p class="text-gray-900 dark:text-white">{{ $organizationBookingRequest->special_requirements }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Adviser Notes Card (if any) -->
            @if($organizationBookingRequest->adviser_notes)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Adviser Notes
                    </h2>
                </div>
                <div class="p-6">
                    <p class="text-gray-900 dark:text-white">{{ $organizationBookingRequest->adviser_notes }}</p>
                    @if($organizationBookingRequest->approved_at)
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                            Added on {{ $organizationBookingRequest->approved_at->format('F j, Y \a\t g:i A') }}
                        </p>
                    @elseif($organizationBookingRequest->rejected_at)
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                            Added on {{ $organizationBookingRequest->rejected_at->format('F j, Y \a\t g:i A') }}
                        </p>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar - Right Side -->
        <div class="space-y-6">
            <!-- Status Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Status
                    </h2>
                </div>
                <div class="p-6">
                    @php
                        $isCancelledByRequestor = $organizationBookingRequest->status === 'cancelled'
                            || str_contains(strtolower((string) $organizationBookingRequest->rejection_reason), 'cancelled by requestor');
                    @endphp
                    <!-- Status Badge - Compact Design -->
                    <div class="flex justify-center mb-6">
                        @if($organizationBookingRequest->status === 'pending')
                            <span class="inline-flex items-center px-4 py-2 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300 text-sm font-semibold rounded-full">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Pending Review
                                @if($organizationBookingRequest->is_overdue)
                                    <span class="ml-2 px-1.5 py-0.5 bg-red-500 text-white text-xs rounded">Overdue</span>
                                @endif
                            </span>
                        @elseif($organizationBookingRequest->status === 'approved')
                            <span class="inline-flex items-center px-4 py-2 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 text-sm font-semibold rounded-full">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Approved
                            </span>
                        @elseif($isCancelledByRequestor)
                            <span class="inline-flex items-center px-4 py-2 bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-300 text-sm font-semibold rounded-full">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Cancelled by Requestor
                            </span>
                        @elseif($organizationBookingRequest->status === 'rejected')
                            <span class="inline-flex items-center px-6 py-2 bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300 text-base font-bold rounded-lg shadow-md border border-red-300 dark:border-red-700">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2" />
                                </svg>
                                Rejected
                            </span>
                        @endif
                    </div>

                    @if($isCancelledByRequestor)
                        <div class="mb-4 rounded-lg border border-orange-200 dark:border-orange-800 bg-orange-50 dark:bg-orange-900/20 p-3">
                            <p class="text-sm font-medium text-orange-800 dark:text-orange-300">
                                This booking is cancelled by requestor.
                            </p>
                        </div>
                    @endif

                    <!-- Action Buttons for Pending -->
                    @if($organizationBookingRequest->status === 'pending')
                        <div class="space-y-3">
                                <button type="button" onclick="openApproveModal()"
                                    class="w-full inline-flex items-center justify-center px-4 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl transition-all duration-200 shadow-md hover:shadow-lg">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Approve Request
                            </button>
                            <button type="button" onclick="openRejectModal()"
                                    class="w-full inline-flex items-center justify-center px-4 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-xl transition-all duration-200 shadow-md hover:shadow-lg">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Reject Request
                            </button>
                        </div>
                    @endif

                    <!-- Processed By -->
                    @if($organizationBookingRequest->approved_by)
                        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Processed By</label>
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-full flex items-center justify-center mr-3 shadow-sm">
                                    <span class="text-sm font-medium text-white">
                                        {{ substr($organizationBookingRequest->approvedBy?->full_name ?? 'U', 0, 2) }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-gray-900 dark:text-white font-medium">{{ $organizationBookingRequest->approvedBy?->full_name ?? 'Unknown' }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Adviser</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Timeline Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Timeline
                    </h2>
                </div>
                <div class="p-6">
                    <div class="relative">
                        <!-- Timeline Line -->
                        <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-700"></div>

                        <!-- Timeline Items -->
                        <div class="space-y-6">
                            <!-- Submitted -->
                            <div class="relative flex items-start">
                                <div class="absolute left-0 w-8 h-8 bg-blue-100 dark:bg-blue-900/40 rounded-full flex items-center justify-center border-2 border-white dark:border-gray-800">
                                    <svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-12">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white">Request Submitted</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                        {{ $organizationBookingRequest->created_at->format('M j, Y g:i A') }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        by {{ $organizationBookingRequest->requestor->full_name }}
                                    </p>
                                </div>
                            </div>

                            <!-- Adviser Notified -->
                            @if($organizationBookingRequest->adviser_notified_at)
                            <div class="relative flex items-start">
                                <div class="absolute left-0 w-8 h-8 bg-indigo-100 dark:bg-indigo-900/40 rounded-full flex items-center justify-center border-2 border-white dark:border-gray-800">
                                    <svg class="w-4 h-4 text-indigo-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/>
                                    </svg>
                                </div>
                                <div class="ml-12">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white">Adviser Notified</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                        {{ $organizationBookingRequest->adviser_notified_at->format('M j, Y g:i A') }}
                                    </p>
                                </div>
                            </div>
                            @endif

                            <!-- Approved/Rejected -->
                            @if($organizationBookingRequest->status === 'approved' && $organizationBookingRequest->approved_at)
                            <div class="relative flex items-start">
                                <div class="absolute left-0 w-8 h-8 bg-green-100 dark:bg-green-900/40 rounded-full flex items-center justify-center border-2 border-white dark:border-gray-800">
                                    <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-12">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white">Request Approved</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                        {{ $organizationBookingRequest->approved_at->format('M j, Y g:i A') }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        by {{ $organizationBookingRequest->approvedBy?->full_name ?? 'System' }}
                                    </p>
                                </div>
                            </div>
                            @elseif($organizationBookingRequest->status === 'rejected' && $organizationBookingRequest->rejected_at)
                            <div class="relative flex items-start">
                                <div class="absolute left-0 w-8 h-8 bg-red-100 dark:bg-red-900/40 rounded-full flex items-center justify-center border-2 border-white dark:border-gray-800">
                                    <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-12">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white">Request Rejected</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                        {{ $organizationBookingRequest->rejected_at->format('M j, Y g:i A') }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        by {{ $organizationBookingRequest->approvedBy?->full_name ?? 'System' }}
                                    </p>
                                </div>
                            </div>
                            @elseif($isCancelledByRequestor)
                            <div class="relative flex items-start">
                                <div class="absolute left-0 w-8 h-8 bg-orange-100 dark:bg-orange-900/40 rounded-full flex items-center justify-center border-2 border-white dark:border-gray-800">
                                    <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </div>
                                <div class="ml-12">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white">Cancelled by Requestor</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                        {{ $organizationBookingRequest->updated_at->format('M j, Y g:i A') }}
                                    </p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submission Info Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Submission Info
                    </h2>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Date Submitted</label>
                        <p class="text-gray-900 dark:text-white">{{ $organizationBookingRequest->created_at->format('F j, Y \a\t g:i A') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Last Updated</label>
                        <p class="text-gray-900 dark:text-white">{{ $organizationBookingRequest->updated_at->format('F j, Y \a\t g:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div id="approveModal" class="fixed inset-0 z-[9999] hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75" onclick="closeApproveModal()"></div>
        <div class="relative inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-gray-800 shadow-2xl rounded-2xl border border-gray-200 dark:border-gray-600">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                    <svg class="w-6 h-6 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Approve Booking Request
                </h3>
                <button onclick="closeApproveModal()" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form action="{{ route('adviser.organization-bookings.approve', $organizationBookingRequest) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Approval Notes (Optional)
                    </label>
                    <textarea name="comments" rows="4"
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white"
                              placeholder="Add any notes or conditions for approval..."></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeApproveModal()"
                            class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                        Approve Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="fixed inset-0 z-[9999] hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75" onclick="closeRejectModal()"></div>
        <div class="relative inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-gray-800 shadow-2xl rounded-2xl border border-gray-200 dark:border-gray-600">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                    <svg class="w-6 h-6 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Reject Booking Request
                </h3>
                <button onclick="closeRejectModal()" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form action="{{ route('adviser.organization-bookings.reject', $organizationBookingRequest) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Reason for Rejection <span class="text-red-500">*</span>
                    </label>
                    <textarea name="reason" rows="4" required
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:text-white"
                              placeholder="Please provide a clear reason for rejecting this request..."></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeRejectModal()"
                            class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors">
                        Reject Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function openApproveModal() {
    document.getElementById('approveModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    document.body.classList.add('modal-open');
}

function closeApproveModal() {
    document.getElementById('approveModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    document.body.classList.remove('modal-open');
}

function openRejectModal() {
    document.getElementById('rejectModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    document.body.classList.add('modal-open');
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    document.body.classList.remove('modal-open');
}

// Close modals on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeApproveModal();
        closeRejectModal();
    }
});

// Auto-open modal based on URL action parameter
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const action = urlParams.get('action');

    if (action === 'reject') {
        openRejectModal();
    } else if (action === 'approve') {
        openApproveModal();
    }
});
</script>
<style>
.modal-open {
    overflow: hidden !important;
}
</style>
@endpush
