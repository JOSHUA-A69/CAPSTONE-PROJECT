@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <a href="{{ route('requestor.organization-bookings.index') }}" 
               class="btn-secondary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Requests
            </a>
            @if($organizationBookingRequest->status === 'pending')
                <a href="{{ route('requestor.organization-bookings.edit', $organizationBookingRequest) }}" 
                   class="btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit Request
                </a>
            @endif
        </div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-4">Organization Booking Request</h1>
        <p class="mt-1 text-gray-600 dark:text-gray-400">Request #{{ $organizationBookingRequest->id }}</p>
    </div>

    <!-- Status Messages -->
    @if (session('status'))
        <div class="mb-6 p-4 rounded-lg bg-green-50 dark:bg-green-900/50 border border-green-200 dark:border-green-800">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="text-green-800 dark:text-green-200 font-medium">
                    {{ session('message', 'Action completed successfully.') }}
                </span>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 p-4 rounded-lg bg-red-50 dark:bg-red-900/50 border border-red-200 dark:border-red-800">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span class="text-red-800 dark:text-red-200 font-medium">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Status Banner -->
    <div class="mb-6 p-4 rounded-xl border-l-4
        @if($organizationBookingRequest->status === 'approved') border-green-500 bg-green-50 dark:bg-green-900/20
        @elseif($organizationBookingRequest->status === 'rejected') border-red-500 bg-red-50 dark:bg-red-900/20
        @else border-yellow-500 bg-yellow-50 dark:bg-yellow-900/20
        @endif">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                @if($organizationBookingRequest->status === 'approved')
                    <svg class="w-6 h-6 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <span class="font-semibold text-green-800 dark:text-green-200">Approved</span>
                        @if($organizationBookingRequest->approvedBy)
                            <p class="text-sm text-green-600 dark:text-green-300">
                                by {{ $organizationBookingRequest->approvedBy->full_name ?? $organizationBookingRequest->approvedBy->name }}
                                on {{ $organizationBookingRequest->approved_at?->format('M j, Y \a\t g:i A') }}
                            </p>
                        @endif
                    </div>
                @elseif($organizationBookingRequest->status === 'rejected')
                    <svg class="w-6 h-6 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <span class="font-semibold text-red-800 dark:text-red-200">Rejected</span>
                        @if($organizationBookingRequest->rejectedBy)
                            <p class="text-sm text-red-600 dark:text-red-300">
                                by {{ $organizationBookingRequest->rejectedBy->full_name ?? $organizationBookingRequest->rejectedBy->name }}
                                on {{ $organizationBookingRequest->rejected_at?->format('M j, Y \a\t g:i A') }}
                            </p>
                        @endif
                    </div>
                @else
                    <svg class="w-6 h-6 text-yellow-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <span class="font-semibold text-yellow-800 dark:text-yellow-200">Pending Review</span>
                        <p class="text-sm text-yellow-600 dark:text-yellow-300">
                            Awaiting adviser approval
                        </p>
                    </div>
                @endif
            </div>

            @if($organizationBookingRequest->status === 'rejected')
                <a href="{{ route('requestor.organization-bookings.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-lg shadow-sm transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Book Another Date
                </a>
            @endif
        </div>

        @if($organizationBookingRequest->status === 'rejected' && $organizationBookingRequest->rejection_reason)
            <div class="mt-3 pt-3 border-t border-red-200 dark:border-red-700">
                <p class="text-sm font-medium text-red-800 dark:text-red-200">Rejection Reason:</p>
                <p class="text-sm text-red-600 dark:text-red-300 mt-1">{{ $organizationBookingRequest->rejection_reason }}</p>
            </div>
        @endif
    </div>

    <!-- Request Details -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Request Details
            </h2>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Activity Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Activity Name</label>
                    <p class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ $organizationBookingRequest->activity_name }}</p>
                </div>

                <!-- Organization -->
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Organization</label>
                    <p class="text-base text-gray-900 dark:text-gray-100">{{ $organizationBookingRequest->organization->org_name }}</p>
                    @if($organizationBookingRequest->organization->adviser)
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Adviser: {{ $organizationBookingRequest->organization->adviser->full_name ?? $organizationBookingRequest->organization->adviser->name }}
                        </p>
                    @endif
                </div>

                <!-- Requested Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Requested Date & Time</label>
                    <p class="text-base font-semibold text-indigo-600 dark:text-indigo-400">
                        {{ $organizationBookingRequest->requested_date->format('F j, Y') }}
                    </p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $organizationBookingRequest->requested_date->format('g:i A') }}
                    </p>
                </div>

                <!-- Submitted Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Submitted On</label>
                    <p class="text-base text-gray-900 dark:text-gray-100">{{ $organizationBookingRequest->created_at->format('F j, Y') }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $organizationBookingRequest->created_at->diffForHumans() }}</p>
                </div>

                @if($organizationBookingRequest->requested_venue)
                <!-- Requested Venue -->
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Preferred Venue</label>
                    <p class="text-base text-gray-900 dark:text-gray-100">{{ $organizationBookingRequest->requested_venue }}</p>
                </div>
                @endif

                @if($organizationBookingRequest->estimated_participants)
                <!-- Estimated Participants -->
                <div>
                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Estimated Participants</label>
                    <p class="text-base text-gray-900 dark:text-gray-100">{{ number_format($organizationBookingRequest->estimated_participants) }} people</p>
                </div>
                @endif
            </div>

            <!-- Purpose -->
            <div class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-700">
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Purpose</label>
                <p class="text-base text-gray-900 dark:text-gray-100 whitespace-pre-line">{{ $organizationBookingRequest->purpose }}</p>
            </div>

            @if($organizationBookingRequest->special_requirements)
            <!-- Special Requirements -->
            <div class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-700">
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Special Requirements</label>
                <p class="text-base text-gray-900 dark:text-gray-100 whitespace-pre-line">{{ $organizationBookingRequest->special_requirements }}</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
