@extends('layouts.app')

@section('content')
<div class="py-6 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl sm:text-3xl font-bold text-heading">
                    Reservation #{{ $reservation->reservation_id }}
                </h2>
                <p class="text-sm text-muted mt-1">
                    Submitted on {{ $reservation->created_at->format('F d, Y') }}
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                @php
                    // Only allow editing if reservation is in certain statuses
                    $editableStatuses = ['pending', 'adviser_approved', 'pending_priest_confirmation'];
                    $canEdit = in_array($reservation->status, $editableStatuses);
                @endphp

                @if($canEdit)
                    <a href="{{ route('requestor.reservations.edit', $reservation->reservation_id) }}" class="btn-secondary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit Reservation
                    </a>
                @endif

                <a href="{{ route('requestor.reservations.index') }}" class="btn-ghost">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to My Reservations
                </a>
            </div>
        </div>

        <!-- Success/Info Messages -->
        @if (session('status'))
            <div class="badge-success flex items-center gap-3 mb-6 p-4">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <p class="text-sm">{{ session('message') ?? 'Action completed successfully' }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="badge-danger flex items-center gap-3 mb-6 p-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                <p class="text-sm">{{ session('error') }}</p>
            </div>
        @endif

        @if (session('info'))
            <div class="badge-info flex items-center gap-3 mb-6 p-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-sm">{{ session('info') }}</p>
            </div>
        @endif

        <!-- Status Banner -->
        <!-- Status Banner -->
        <div class="card mb-6 border-l-4
            @if($reservation->status === 'confirmed' || $reservation->status === 'completed') border-green-500
            @elseif($reservation->status === 'cancelled' || $reservation->status === 'rejected') border-red-500
            @else border-yellow-500
            @endif">
            <div class="card-body">
                <div class="flex items-center gap-3">
                    @if($reservation->status === 'confirmed' || $reservation->status === 'completed')
                        <span class="badge-success">{{ ucfirst(str_replace('_', ' ', $reservation->status)) }}</span>
                    @elseif($reservation->status === 'cancelled' || $reservation->status === 'rejected')
                        <span class="badge-danger">{{ ucfirst(str_replace('_', ' ', $reservation->status)) }}</span>
                    @else
                        <span class="badge-warning">{{ ucfirst(str_replace('_', ' ', $reservation->status)) }}</span>
                    @endif

                    <span class="text-sm text-muted">
                        @if($reservation->status === 'pending')
                            Waiting for organization adviser approval
                        @elseif($reservation->status === 'adviser_approved' && !$reservation->contacted_at)
                            Approved by adviser - Waiting for CREaM staff contact
                        @elseif($reservation->status === 'adviser_approved' && $reservation->contacted_at && !$reservation->requestor_confirmed_at)
                            CREaM staff has contacted you - Please confirm your reservation
                        @elseif($reservation->status === 'adviser_approved' && $reservation->requestor_confirmed_at)
                            You have confirmed - Waiting for staff approval
                        @elseif($reservation->status === 'pending_priest_assignment')
                            Approved - Waiting for priest assignment
                        @elseif($reservation->status === 'pending_priest_confirmation')
                            Priest assigned - Waiting for priest confirmation
                        @elseif($reservation->status === 'confirmed')
                            Confirmed - Event scheduled
                        @elseif($reservation->status === 'completed')
                            Event completed successfully
                        @elseif($reservation->status === 'cancelled')
                            Reservation cancelled
                        @elseif($reservation->status === 'rejected')
                            Reservation not available
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6">
            <!-- Reservation Details Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Reservation Details
                    </h3>
                </div>

                <div class="card-body">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Left Column -->
                        <div class="space-y-4">
                            @if($reservation->activity_name)
                            <div>
                                <label class="form-label">Activity Name</label>
                                <p class="mt-1 text-base font-semibold text-heading">{{ $reservation->activity_name }}</p>
                            </div>
                            @endif

                            <div>
                                <label class="form-label">Service Type</label>
                                <p class="mt-1 text-base text-body">{{ $reservation->service->service_name }}</p>
                            </div>

                            <div>
                                <label class="form-label">Venue</label>
                                <p class="mt-1 text-base text-body">
                                    @if($reservation->custom_venue_name)
                                        📍 {{ $reservation->custom_venue_name }}
                                        <span class="badge-info ml-2">Custom Location</span>
                                    @else
                                        {{ $reservation->venue->name }}
                                    @endif
                                </p>
                            </div>

                            <div>
                                <label class="form-label">Schedule</label>
                                <p class="mt-1 text-base font-semibold text-indigo-600 dark:text-indigo-400">
                                    {{ $reservation->schedule_date->format('F d, Y') }}<br>
                                    <span class="text-sm">{{ $reservation->schedule_date->format('g:i A') }}</span>
                                </p>
                            </div>

                            @if($reservation->participants_count)
                            <div>
                                <label class="form-label">Expected Participants</label>
                                <p class="mt-1 text-base text-body">{{ $reservation->participants_count }} people</p>
                            </div>
                            @endif
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-4">
                            @if($reservation->theme)
                            <div>
                                <label class="form-label">Theme</label>
                                <p class="mt-1 text-base text-body">{{ $reservation->theme }}</p>
                            </div>
                            @endif

                            @if($reservation->organization)
                            <div>
                                <label class="form-label">Organization</label>
                                <p class="mt-1 text-base text-body">{{ $reservation->organization->org_name }}</p>
                            </div>
                            @endif

                            @if($reservation->purpose)
                            <div>
                                <label class="form-label">Purpose</label>
                                <p class="mt-1 text-base text-body">{{ $reservation->purpose }}</p>
                            </div>
                            @endif

                            @if($reservation->details)
                            <div>
                                <label class="form-label">Additional Details</label>
                                <p class="mt-1 text-base text-body">{{ $reservation->details }}</p>
                            </div>
                            @endif

                            @if($reservation->officiant)
                            <div>
                                <label class="form-label">Assigned Priest</label>
                                <p class="mt-1 text-base font-semibold text-heading">{{ $reservation->officiant->full_name }}</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Ministry Volunteers Section -->
                    @if($reservation->commentator || $reservation->servers || $reservation->readers || $reservation->choir || $reservation->psalmist || $reservation->prayer_leader)
                    <div class="mt-6 pt-6 border-t">
                        <h4 class="text-md font-semibold text-heading mb-4">Ministry Volunteers</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @if($reservation->commentator)
                            <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">
                                <p class="form-label uppercase mb-1">Commentator</p>
                                <p class="text-sm text-body">{{ $reservation->commentator }}</p>
                            </div>
                            @endif

                            @if($reservation->servers)
                            <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">
                                <p class="form-label uppercase mb-1">Servers</p>
                                <p class="text-sm text-body">{{ $reservation->servers }}</p>
                            </div>
                            @endif

                            @if($reservation->readers)
                            <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">
                                <p class="form-label uppercase mb-1">Readers</p>
                                <p class="text-sm text-body">{{ $reservation->readers }}</p>
                            </div>
                            @endif

                            @if($reservation->choir)
                            <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">
                                <p class="form-label uppercase mb-1">Choir</p>
                                <p class="text-sm text-body">{{ $reservation->choir }}</p>
                            </div>
                            @endif

                            @if($reservation->psalmist)
                            <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">
                                <p class="form-label uppercase mb-1">Psalmist</p>
                                <p class="text-sm text-body">{{ $reservation->psalmist }}</p>
                            </div>
                            @endif

                            @if($reservation->prayer_leader)
                            <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">
                                <p class="form-label uppercase mb-1">Leader for Prayer of the Faithful</p>
                                <p class="text-sm text-body">{{ $reservation->prayer_leader }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Actions Card (if applicable) -->
            @php
                $daysUntilEvent = $reservation->schedule_date ? now()->diffInDays($reservation->schedule_date, false) : -9999;
                $canCancel = $daysUntilEvent >= 7
                    && in_array($reservation->status, ['pending', 'adviser_approved', 'admin_approved', 'pending_priest_confirmation', 'approved'])
                    && !$reservation->cancellation_reason;
            @endphp
            @if($reservation->status !== 'cancelled' && $reservation->status !== 'rejected' && $reservation->status !== 'completed')
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold">Actions</h3>
                </div>
                <div class="card-body">
                    @if($canCancel)
                    <button onclick="document.getElementById('cancelForm').classList.toggle('hidden')" class="btn-danger">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Cancel Reservation
                    </button>

                    <form id="cancelForm" method="POST" action="{{ route('requestor.reservations.cancel', $reservation->reservation_id) }}" class="hidden mt-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                        @csrf
                        <label class="form-label mb-2">Reason for Cancellation</label>
                        <textarea name="reason" rows="3" required class="form-input" placeholder="Please provide a reason..."></textarea>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <button type="submit" class="btn-danger">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Confirm Cancellation
                            </button>
                            <button type="button" onclick="document.getElementById('cancelForm').classList.add('hidden')" class="btn-secondary">Cancel</button>
                        </div>
                    </form>
                    @elseif($daysUntilEvent < 7 && $daysUntilEvent >= 0)
                        <div class="mt-2 text-sm text-muted">Cannot cancel within 7 days of the event.</div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
