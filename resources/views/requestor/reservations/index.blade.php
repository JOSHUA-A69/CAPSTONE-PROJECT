@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-12">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-heading">My Reservations</h1>
            <p class="text-sm text-muted mt-1">View and manage all your spiritual activity requests</p>
        </div>
        <a href="{{ route('requestor.reservations.create') }}" class="btn-primary btn-mobile">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            New Reservation
        </a>
    </div>

    <!-- Enhanced Success Message -->
    @if(session('success'))
        <div class="mb-6 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border-l-4 border-green-500 rounded-lg shadow-md overflow-hidden animate-fade-in">
            <div class="p-4 sm:p-5">
                <div class="flex items-start gap-4">
                    <!-- Success Icon -->
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-green-500 rounded-full flex items-center justify-center animate-scale-in">
                            <svg class="w-6 h-6 sm:w-7 sm:h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    </div>

                    <!-- Success Message Content -->
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg sm:text-xl font-bold text-green-800 dark:text-green-300 mb-1">
                            {{ session('success') }}
                        </h3>
                        @if(session('message'))
                            <p class="text-sm sm:text-base text-green-700 dark:text-green-400 leading-relaxed">
                                {{ session('message') }}
                            </p>
                        @endif
                    </div>

                    <!-- Close Button -->
                    <button type="button" onclick="this.closest('div[class*=&quot;bg-gradient&quot;]').remove()" class="flex-shrink-0 text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 transition-colors focus:outline-none focus:ring-2 focus:ring-green-500 rounded p-1" aria-label="Dismiss notification">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Keep existing status message for backward compatibility -->
    @if(session('status'))
        <div class="badge-success flex items-center gap-2 mb-6 p-4">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            {{ session('status') }}
        </div>
    @endif

    <div class="card">
        <div class="overflow-x-auto">
            @if($reservations->isEmpty())
            <div class="p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-muted text-lg mb-4">You haven't created any reservations yet.</p>
                <a href="{{ route('requestor.reservations.create') }}" class="btn-primary inline-flex">
                    Create Your First Reservation
                </a>
            </div>
            @else
            <div class="table-responsive">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" role="table" aria-label="My reservations">
                <caption class="sr-only">List of all your spiritual activity reservations</caption>
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Service</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Venue</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Schedule</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Purpose</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($reservations as $r)
                    @php
                        // Calculate if cancellation is allowed (7 days before)
                        $daysUntilEvent = now()->diffInDays($r->schedule_date, false);
                        $canCancel = $daysUntilEvent >= 7 &&
                                    in_array($r->status, ['pending', 'adviser_approved', 'admin_approved', 'approved']) &&
                                    !$r->cancellation_reason;
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        <td class="px-4 py-3 text-sm text-body">{{ $r->service->service_name ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-body">
                            @if($r->venue)
                                {{ $r->venue->name }}
                            @elseif($r->custom_venue_name)
                                <span class="text-body">{{ $r->custom_venue_name }}</span>
                                <span class="badge-info ml-1">Custom</span>
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-body">{{ optional($r->schedule_date)->format('M d, Y h:i A') }}</td>
                        <td class="px-4 py-3">
                            @if($r->status === 'approved' || $r->status === 'confirmed')
                                <span class="badge-success">{{ ucwords(str_replace('_', ' ', $r->status)) }}</span>
                            @elseif($r->status === 'cancelled' || $r->status === 'rejected')
                                <span class="badge-danger">{{ ucwords(str_replace('_', ' ', $r->status)) }}</span>
                            @else
                                <span class="badge-warning">{{ ucwords(str_replace('_', ' ', $r->status)) }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-muted">{{ \Illuminate\Support\Str::limit($r->purpose ?? '—', 60) }}</td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('requestor.reservations.show', $r->reservation_id) }}" class="btn-ghost btn-sm">
                                    View Details
                                </a>
                                @if($canCancel)
                                    <button
                                        onclick="showCancelModal({{ $r->reservation_id }}, '{{ $r->service->service_name }}', '{{ $r->schedule_date->format('F d, Y h:i A') }}')"
                                        class="btn-danger btn-sm">
                                        Cancel
                                    </button>
                                @elseif($r->status === 'cancelled')
                                    <span class="text-xs text-muted">Cancelled</span>
                                @elseif($daysUntilEvent < 7 && $daysUntilEvent >= 0)
                                    <span class="text-xs text-muted" title="Cannot cancel within 7 days of event">
                                        Too late
                                    </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
            @endif
        </div>
    </div>

    <div class="mt-4">
        {{ $reservations->links() }}
    </div>
</div>

<!-- Cancel Reservation Modal -->
<div id="cancelModal" class="hidden fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border border-gray-200 dark:border-gray-700 w-full max-w-md shadow-2xl rounded-lg bg-white dark:bg-gray-800">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-heading">
                    Cancel Reservation
                </h3>
                <button onclick="hideCancelModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="flex items-start gap-3 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg mb-4">
                <svg class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div>
                    <p class="text-sm font-medium text-red-800 dark:text-red-200">
                        ⚠️ Warning: This action cannot be undone
                    </p>
                    <p class="text-sm text-red-700 dark:text-red-300 mt-1">
                        All involved parties (adviser, priest, admin staff) will be notified.
                    </p>
                </div>
            </div>

            <div class="mb-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <p class="text-sm text-body">
                    <strong>Service:</strong> <span id="cancelServiceName"></span><br>
                    <strong>Schedule:</strong> <span id="cancelSchedule"></span>
                </p>
            </div>

            <form id="cancelForm" method="POST" action="">
                @csrf
                <div class="mb-4">
                    <label for="cancellation_reason" class="form-label">
                        Reason for Cancellation <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        id="cancellation_reason"
                        name="reason"
                        rows="4"
                        required
                        class="form-input mt-1"
                        placeholder="Please provide a detailed reason for cancelling this reservation..."></textarea>
                    <p class="form-helper mt-2">
                        This reason will be shared with all parties involved.
                    </p>
                </div>

                <div class="flex gap-3 justify-end">
                    <button
                        type="button"
                        onclick="hideCancelModal()"
                        class="btn-secondary">
                        Keep Reservation
                    </button>
                    <button
                        type="submit"
                        class="btn-danger">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
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
