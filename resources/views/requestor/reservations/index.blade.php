@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold">My Reservations</h1>
        <a href="{{ route('requestor.reservations.create') }}" class="btn btn-primary">New Reservation</a>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="bg-white shadow rounded">
        <div class="overflow-x-auto">
            @if($reservations->isEmpty())
            <div class="p-6 text-center text-gray-600">
                No reservations yet.
            </div>
            @else
            <table class="min-w-full divide-y">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">Service</th>
                        <th class="px-4 py-2 text-left">Venue</th>
                        <th class="px-4 py-2 text-left">Schedule</th>
                        <th class="px-4 py-2 text-left">Status</th>
                        <th class="px-4 py-2 text-left">Purpose</th>
                        <th class="px-4 py-2 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y">
                    @foreach($reservations as $r)
                    @php
                        // Calculate if cancellation is allowed (7 days before)
                        $daysUntilEvent = now()->diffInDays($r->schedule_date, false);
                        $canCancel = $daysUntilEvent >= 7 && 
                                    in_array($r->status, ['pending', 'adviser_approved', 'admin_approved', 'approved']) &&
                                    !$r->cancellation_reason;
                    @endphp
                    <tr>
                        <td class="px-4 py-2">{{ $r->service->service_name ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $r->venue->name ?? '—' }}</td>
                        <td class="px-4 py-2">{{ optional($r->schedule_date)->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-2">
                            <span class="px-2 py-1 text-xs rounded-full
                                @if($r->status === 'approved') bg-green-100 text-green-800
                                @elseif($r->status === 'cancelled') bg-red-100 text-red-800
                                @elseif($r->status === 'rejected') bg-red-100 text-red-800
                                @else bg-yellow-100 text-yellow-800
                                @endif">
                                {{ ucwords(str_replace('_', ' ', $r->status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-2">{{ \Illuminate\Support\Str::limit($r->purpose ?? '—', 80) }}</td>
                        <td class="px-4 py-2">
                            @if($canCancel)
                                <button 
                                    onclick="showCancelModal({{ $r->reservation_id }}, '{{ $r->service->service_name }}', '{{ $r->schedule_date->format('F d, Y h:i A') }}')"
                                    class="text-sm px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700">
                                    Cancel
                                </button>
                            @elseif($r->status === 'cancelled')
                                <span class="text-sm text-gray-500">Cancelled</span>
                            @elseif($daysUntilEvent < 7 && $daysUntilEvent >= 0)
                                <span class="text-sm text-gray-400" title="Cannot cancel within 7 days of event">
                                    Too late
                                </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>

    <div class="mt-4">
        {{ $reservations->links() }}
    </div>
</div>

<!-- Cancel Reservation Modal -->
<div id="cancelModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Cancel Reservation
                </h3>
                <button onclick="hideCancelModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md p-4 mb-4">
                <p class="text-sm text-red-800 dark:text-red-300">
                    <strong>⚠️ Warning:</strong> Cancelling this reservation will notify all involved parties including your adviser, the priest, and the admin staff.
                </p>
            </div>

            <div class="mb-4">
                <p class="text-sm text-gray-700 dark:text-gray-300 mb-2">
                    <strong>Service:</strong> <span id="cancelServiceName"></span><br>
                    <strong>Schedule:</strong> <span id="cancelSchedule"></span>
                </p>
            </div>

            <form id="cancelForm" method="POST" action="">
                @csrf
                <div class="mb-4">
                    <label for="cancellation_reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Reason for Cancellation <span class="text-red-500">*</span>
                    </label>
                    <textarea 
                        id="cancellation_reason" 
                        name="reason" 
                        rows="4"
                        required
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-gray-100"
                        placeholder="Please provide a detailed reason for cancelling this reservation..."></textarea>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        This reason will be shared with all parties involved.
                    </p>
                </div>

                <div class="flex gap-3 justify-end">
                    <button 
                        type="button"
                        onclick="hideCancelModal()"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                        Keep Reservation
                    </button>
                    <button 
                        type="submit"
                        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                        Confirm Cancellation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showCancelModal(reservationId, serviceName, schedule) {
    document.getElementById('cancelServiceName').textContent = serviceName;
    document.getElementById('cancelSchedule').textContent = schedule;
    document.getElementById('cancelForm').action = `/requestor/reservations/${reservationId}/cancel`;
    document.getElementById('cancelModal').classList.remove('hidden');
}

function hideCancelModal() {
    document.getElementById('cancelModal').classList.add('hidden');
    document.getElementById('cancellation_reason').value = '';
}

// Close modal when clicking outside
document.getElementById('cancelModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        hideCancelModal();
    }
});
</script>
@endsection
