@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6">
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('adviser.reservations.index') }}" class="text-blue-600 hover:underline">
            ← Back to Reservations
        </a>
    </div>

    <!-- Success/Error Messages -->
    @if(session('status'))
        <div class="mb-4 p-4 rounded {{ session('status') === 'reservation-approved' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
            {{ session('message') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 rounded bg-red-100 text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <!-- Reservation Details Card -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
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
                    {{ $reservation->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                    {{ $reservation->status === 'adviser_approved' ? 'bg-green-100 text-green-800' : '' }}
                    {{ $reservation->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}
                    {{ $reservation->status === 'approved' ? 'bg-blue-100 text-blue-800' : '' }}">
                    {{ ucfirst(str_replace('_', ' ', $reservation->status)) }}
                </span>
            </div>

            <!-- Requestor Information -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-3 border-b pb-2">Requestor Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Name</p>
                        <p class="font-medium">{{ $reservation->user->first_name }} {{ $reservation->user->last_name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Email</p>
                        <p class="font-medium">{{ $reservation->user->email }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Phone</p>
                        <p class="font-medium">{{ $reservation->user->phone ?? 'Not provided' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Organization</p>
                        <p class="font-medium">{{ $reservation->organization->org_name ?? 'None' }}</p>
                    </div>
                </div>
            </div>

            <!-- Service Details -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-3 border-b pb-2">Service Details</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Service Type</p>
                        <p class="font-medium">{{ $reservation->service->service_name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Schedule</p>
                        <p class="font-medium">{{ optional($reservation->schedule_date)->format('F d, Y h:i A') ?? 'Not scheduled' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Venue</p>
                        <p class="font-medium">{{ $reservation->venue->venue_name ?? $reservation->custom_venue_name ?? 'Not specified' }}</p>
                    </div>
                    @if($reservation->custom_venue_name)
                    <div>
                        <p class="text-sm text-gray-600">Custom Venue Details</p>
                        <p class="font-medium">{{ $reservation->custom_venue_name }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Additional Information -->
            @if($reservation->purpose || $reservation->remarks)
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-3 border-b pb-2">Additional Information</h2>
                @if($reservation->purpose)
                <div class="mb-3">
                    <p class="text-sm text-gray-600">Purpose</p>
                    <p class="font-medium">{{ $reservation->purpose }}</p>
                </div>
                @endif
                @if($reservation->remarks)
                <div>
                    <p class="text-sm text-gray-600">Remarks</p>
                    <p class="font-medium">{{ $reservation->remarks }}</p>
                </div>
                @endif
            </div>
            @endif

            <!-- Ministry Information (if applicable) -->
            @if($reservation->ministry_id || $reservation->is_outsider)
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-3 border-b pb-2">Ministry Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if($reservation->ministry_id)
                    <div>
                        <p class="text-sm text-gray-600">Ministry</p>
                        <p class="font-medium">{{ $reservation->ministry_id }}</p>
                    </div>
                    @endif
                    @if($reservation->is_outsider)
                    <div>
                        <p class="text-sm text-gray-600">Requestor Type</p>
                        <p class="font-medium">Outside Organization</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Action Buttons (show if not rejected/cancelled) -->
            @if(!in_array($reservation->status, ['rejected', 'cancelled']))
            <div class="border-t pt-6 mt-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Adviser Actions</h2>
                <div class="flex flex-col sm:flex-row gap-3">
                    <!-- Approve Form -->
                    <form method="POST" action="{{ route('adviser.reservations.approve', $reservation->reservation_id) }}" class="flex-1">
                        @csrf
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Remarks (Optional)</label>
                            <textarea name="remarks" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="Add any comments..."></textarea>
                        </div>
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-md transition duration-200">
                            ✓ Approve Reservation
                        </button>
                    </form>

                    <!-- Reject Button -->
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">&nbsp;</label>
                        <a id="reject"></a>
                        <button onclick="showRejectModal()" class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-md transition duration-200 mt-8">
                            ✗ Reject Reservation
                        </button>
                    </div>
                </div>
            </div>
            @else
            <div class="border-t pt-6 mt-6">
                <div class="p-4 bg-gray-50 rounded text-sm text-gray-600">
                    This reservation is <strong>{{ ucfirst(str_replace('_',' ', $reservation->status)) }}</strong>. Adviser actions are no longer available.
                </div>
            </div>
            @endif

            <!-- History Timeline -->
            @if($reservation->history && $reservation->history->count() > 0)
            <div class="border-t pt-6 mt-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Reservation History</h2>
                <div class="space-y-3">
                    @foreach($reservation->history()->orderBy('performed_at', 'desc')->get() as $history)
                    <div class="flex items-start gap-3 p-3 bg-gray-50 rounded">
                        <div class="flex-shrink-0 w-2 h-2 mt-2 rounded-full bg-blue-600"></div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $history->action)) }}</p>
                            @if($history->remarks)
                            <p class="text-sm text-gray-600 mt-1">{{ $history->remarks }}</p>
                            @endif
                            <p class="text-xs text-gray-500 mt-1">
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
<div id="rejectModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
        <div class="px-6 py-4 border-b">
            <h3 class="text-xl font-semibold text-gray-900">Reject Reservation</h3>
        </div>
        <form method="POST" action="{{ route('adviser.reservations.reject', $reservation->reservation_id) }}">
            @csrf
            <div class="px-6 py-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Reason for Rejection <span class="text-red-600">*</span></label>
                <textarea name="reason" rows="4" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="Please provide a clear reason for rejecting this reservation..."></textarea>
                <p class="text-xs text-gray-500 mt-1">This reason will be sent to the requestor.</p>
            </div>
            <div class="px-6 py-4 bg-gray-50 flex gap-3">
                <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-md transition duration-200">
                    Reject
                </button>
                <button type="button" onclick="hideRejectModal()" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-4 rounded-md transition duration-200">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showRejectModal() {
    document.getElementById('rejectModal').classList.remove('hidden');
}

function hideRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
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
