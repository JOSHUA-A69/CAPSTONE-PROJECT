@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6">
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('adviser.reservations.index') }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition-colors">
            ← Back to Reservations
        </a>
    </div>

    <!-- Success/Error Messages -->
    @if(session('status'))
        <div class="mb-4 p-4 rounded {{ session('status') === 'reservation-approved' ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200' : 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200' }}">
            {{ session('message') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 rounded bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200">
            {{ session('error') }}
        </div>
    @endif

    <!-- Reservation Details Card -->
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white px-6 py-4">
            <h1 class="text-2xl font-bold">Reservation Details</h1>
            <p class="text-blue-100 mt-1">Request ID: #{{ $reservation->reservation_id }}</p>
        </div>

        <!-- Content -->
        <div class="p-6">
            <!-- Status Badge -->
            <div class="mb-6">
                <span class="px-4 py-2 rounded-full text-sm font-semibold
                    {{ $reservation->status === 'pending' ? 'bg-yellow-100 dark:bg-yellow-900/40 text-yellow-800 dark:text-yellow-200' : '' }}
                    {{ $reservation->status === 'adviser_approved' ? 'bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200' : '' }}
                    {{ $reservation->status === 'rejected' ? 'bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-200' : '' }}
                    {{ $reservation->status === 'approved' ? 'bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-200' : '' }}">
                    {{ ucfirst(str_replace('_', ' ', $reservation->status)) }}
                </span>
            </div>

            <!-- Requestor Information -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 border-b dark:border-gray-700 pb-2">Requestor Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Name</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $reservation->user->first_name }} {{ $reservation->user->last_name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Email</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $reservation->user->email }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Phone</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $reservation->user->phone ?? 'Not provided' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Organization</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $reservation->organization->org_name ?? 'None' }}</p>
                    </div>
                </div>
            </div>

            <!-- Service Details -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 border-b dark:border-gray-700 pb-2">Service Details</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Service Type</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $reservation->service->service_name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Schedule</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ optional($reservation->schedule_date)->format('F d, Y h:i A') ?? 'Not scheduled' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Venue</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $reservation->custom_venue_name ?? optional($reservation->venue)->name ?? 'Not specified' }}</p>
                    </div>
                    @if($reservation->custom_venue_name)
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Custom Venue Details</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $reservation->custom_venue_name }}</p>
                    </div>
                    @endif

                    @php
                        $assignedPriests = isset($reservation->priests) ? $reservation->priests : collect();
                    @endphp
                    
                    @if($assignedPriests->count() > 0)
                        <div class="md:col-span-2">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Assigned Priests</p>
                            <ul class="list-disc list-inside">
                                @foreach($assignedPriests as $p)
                                    <li class="font-medium text-gray-900 dark:text-white">{{ $p->full_name }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @elseif($reservation->priest_selection_type === 'external' && $reservation->external_priest_name)
                        <div class="md:col-span-2">
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">External Priest <span class="text-xs px-2 py-0.5 rounded bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-200 ml-2">External</span></p>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $reservation->external_priest_name }}</p>
                            @if($reservation->external_priest_contact)
                                <p class="text-sm text-gray-500 mt-1">{{ $reservation->external_priest_contact }}</p>
                            @endif
                        </div>
                    @elseif($reservation->officiant)
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Assigned Priest</p>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $reservation->officiant->full_name }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Additional Information -->
            @if($reservation->purpose || $reservation->remarks)
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 border-b dark:border-gray-700 pb-2">Additional Information</h2>
                @if($reservation->purpose)
                <div class="mb-3">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Purpose</p>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $reservation->purpose }}</p>
                </div>
                @endif
                @if($reservation->remarks)
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Remarks</p>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $reservation->remarks }}</p>
                </div>
                @endif
            </div>
            @endif

            <!-- Ministry Information (if applicable) -->
            @if($reservation->ministry_id || $reservation->is_outsider)
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 border-b dark:border-gray-700 pb-2">Ministry Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if($reservation->ministry_id)
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Ministry</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $reservation->ministry_id }}</p>
                    </div>
                    @endif
                    @if($reservation->is_outsider)
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Requestor Type</p>
                        <p class="font-medium text-gray-900 dark:text-white">Outside Organization</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Action Buttons -->
            @if(in_array($reservation->status, ['adviser_approved', 'approved']))
                @php
                    // Calculate days until the mass
                    // Round up to the nearest whole number (e.g., 5.8 days becomes 6 days)
                    $daysUntilMass = (int) ceil(now()->diffInDays($reservation->schedule_date, false));
                    $canCancel = $daysUntilMass >= 6; // Can only cancel if 6 or more days before
                @endphp
                
                <div class="border-t pt-6 mt-6">
                    <div class="max-w-4xl mx-auto space-y-6">
                        @if($canCancel)
                            <!-- Cancel Approval Form -->
                            <form method="POST" action="{{ route('adviser.reservations.cancel-approval', $reservation->reservation_id) }}" class="bg-white dark:bg-gray-800 rounded-lg border border-orange-200 dark:border-orange-700 p-6">
                                @csrf
                                <div class="mb-4">
                                    <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                        <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                        Cancel Approval - Reason Required
                                    </label>
                                    <textarea name="reason" rows="3" required class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent dark:bg-gray-700 dark:text-white resize-none" placeholder="Explain why you need to cancel this approval..."></textarea>
                                </div>
                                <div class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg p-3 mb-4">
                                    <p class="text-sm text-orange-700 dark:text-orange-300">
                                        <strong>Warning:</strong> Cancelling approval will revert status to "Pending Adviser" and notify staff and the requestor. This action should only be used if circumstances have changed.
                                    </p>
                                </div>
                                <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200" onclick="return confirm('Are you sure you want to cancel this approval? This will notify staff and the requestor.')">
                                    Cancel Approval
                                </button>
                            </form>
                        @else
                            <!-- Time Restriction Message -->
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 p-6">
                                <div class="flex items-center gap-3 mb-3">
                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Approval Cannot Be Cancelled</h3>
                                </div>
                                <p class="text-gray-600 dark:text-gray-300 mb-2">
                                    This reservation is scheduled for <strong>{{ $reservation->schedule_date->format('F d, Y \\a\\t g:i A') }}</strong>
                                </p>
                                <p class="text-gray-600 dark:text-gray-300">
                                    You can only cancel approvals for masses scheduled <strong>6 or more days</strong> in advance. 
                                    This mass is only <strong>{{ $daysUntilMass }}</strong> day(s) away.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            @elseif($reservation->status === 'pending')
            <div class="border-t pt-6 mt-6">
                @php
                    $currentUser = auth()->user();
                    $currentAdviserOrgs = $currentUser->organizations->pluck('org_id');
                    // Find the relevant organization pivot for this adviser
                    $relevantOrg = $reservation->organizations->whereIn('org_id', $currentAdviserOrgs)->first();
                    $hasApproved = $relevantOrg && $relevantOrg->pivot->approval_status === 'approved';
                @endphp

                @if($hasApproved)
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6 text-center">
                        <svg class="w-12 h-12 mx-auto text-blue-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <h3 class="text-lg font-bold text-blue-700 dark:text-blue-300">You have approved this reservation</h3>
                        <p class="text-blue-600 dark:text-blue-400 mt-1">Waiting for other advisers to approve.</p>
                    </div>
                @else
                <div class="max-w-4xl mx-auto space-y-6">
                    <!-- Approve Form -->
                    <form method="POST" action="{{ route('adviser.reservations.approve', $reservation->reservation_id) }}" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                        @csrf
                        <div class="mb-4">
                            <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Remarks (Optional)
                            </label>
                            <textarea name="remarks" rows="3" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent dark:bg-gray-700 dark:text-white resize-none" placeholder="Add any comments or notes about this approval..."></textarea>
                        </div>
                        <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200">
                            Approve Reservation
                        </button>
                    </form>

                    <!-- Reject Section -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                        <div class="mb-4">
                            <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                Reject This Request
                            </label>
                            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3 mb-4">
                                <p class="text-sm text-red-700 dark:text-red-300">
                                    Clicking below will open a form to provide a reason for rejection. This action requires explanation.
                                </p>
                            </div>
                        </div>
                        <a id="reject"></a>
                        <button onclick="showRejectModal()" class="w-full bg-red-500 hover:bg-red-600 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200">
                            Reject Reservation
                        </button>
                    </div>
                </div>
                @endif
            </div>
            @else
            <div class="border-t dark:border-gray-700 pt-6 mt-6">
                <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded text-sm text-gray-600 dark:text-gray-300">
                    This reservation is <strong>{{ ucfirst(str_replace('_',' ', $reservation->status)) }}</strong>. Adviser actions are no longer available.
                </div>
            </div>
            @endif

            <!-- History Timeline -->
            @if($reservation->history && $reservation->history->count() > 0)
            <div class="border-t dark:border-gray-700 pt-6 mt-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Reservation History</h2>
                <div class="space-y-3">
                    @foreach($reservation->history()->orderBy('performed_at', 'desc')->get() as $history)
                    <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded">
                        <div class="flex-shrink-0 w-2 h-2 mt-2 rounded-full bg-blue-600 dark:bg-blue-400"></div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-900 dark:text-white">{{ ucfirst(str_replace('_', ' ', $history->action)) }}</p>
                            @if($history->remarks)
                            <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">{{ $history->remarks }}</p>
                            @endif
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                {{ optional($history->performedBy)->first_name }} {{ optional($history->performedBy)->last_name }}
                                • {{ optional($history->performed_at)->format('M d, Y h:i A') }}
                            </p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden z-[100] items-center justify-center">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-lg mx-4">
        <div class="px-6 py-4 border-b dark:border-gray-700">
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Reject Reservation</h3>
        </div>
        <form method="POST" action="{{ route('adviser.reservations.reject', $reservation->reservation_id) }}">
            @csrf
            <div class="px-6 py-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Reason for Rejection <span class="text-red-600">*</span></label>
                <textarea name="reason" rows="4" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white" placeholder="Please provide a clear reason for rejecting this reservation..."></textarea>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">This reason will be sent to the requestor.</p>
            </div>
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 flex gap-3">
                <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-md transition duration-200">
                    Reject
                </button>
                <button type="button" onclick="hideRejectModal()" class="flex-1 bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500 text-gray-800 dark:text-white font-semibold py-2 px-4 rounded-md transition duration-200">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
@if(request()->has('notification_read'))
// Dispatch notification update event since notification was marked as read
window.addEventListener('DOMContentLoaded', function() {
    if (window.dispatchEvent) {
        window.dispatchEvent(new Event('notification-update'));
    }
});
@endif

function showRejectModal() {
    const m = document.getElementById('rejectModal');
    m.classList.remove('hidden');
    m.classList.add('flex');
}

function hideRejectModal() {
    const m = document.getElementById('rejectModal');
    m.classList.add('hidden');
    m.classList.remove('flex');
}

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        hideRejectModal();
    }
});

// Close modal on outside click
document.getElementById('rejectModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        hideRejectModal();
    }
});
</script>
@endsection
