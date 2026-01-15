@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header & Actions -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 px-4 sm:px-0">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">My Reservations</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">View and manage your spiritual activity requests</p>
                
                @if(isset($statusFilter))
                    @php
                        $filterColors = [
                            'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                            'approved' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                            'upcoming' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400'
                        ];
                        $colorClass = $filterColors[$statusFilter] ?? 'bg-gray-100 text-gray-800';
                    @endphp
                    <div class="mt-3 inline-flex items-center gap-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $colorClass }}">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd"/>
                            </svg>
                            Filtered: {{ ucfirst($statusFilter) }}
                        </span>
                        <a href="{{ route('requestor.reservations.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 border-b border-transparent hover:border-blue-500 transition-colors">
                            Clear filter
                        </a>
                    </div>
                @endif
            </div>

            <a href="{{ route('requestor.reservations.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New Reservation
            </a>
        </div>

        <!-- Alerts -->
        @if(session('success'))
            <div class="mb-6 mx-4 sm:mx-0 bg-green-50 dark:bg-green-900/30 border-l-4 border-green-500 p-4 rounded-r-lg shadow-sm">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800 dark:text-green-200">
                            {{ session('success') }}
                        </p>
                        @if(session('message'))
                            <p class="mt-1 text-sm text-green-700 dark:text-green-300">
                                {{ session('message') }}
                            </p>
                        @endif
                    </div>
                    <div class="ml-auto pl-3">
                        <div class="-mx-1.5 -my-1.5">
                            <button type="button" onclick="this.closest('.mb-6').remove()" class="inline-flex rounded-md p-1.5 text-green-500 hover:bg-green-100 dark:hover:bg-green-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <span class="sr-only">Dismiss</span>
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 mx-4 sm:mx-0 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 p-4 rounded-r-lg shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800 dark:text-red-200">
                            {{ $errors->first() }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Reservations Table -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 dark:border-gray-700">
            @if($reservations->isEmpty())
                <div class="flex flex-col items-center justify-center py-16 px-4 text-center">
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-full p-4 mb-4">
                        <svg class="w-12 h-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">No reservations found</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-sm">
                        You haven't made any spiritual activity reservations yet, or no reservations match your current filter.
                    </p>
                    <a href="{{ route('requestor.reservations.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">
                        Create Reservation
                    </a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Service info</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Schedule</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Assigned To</th>
                                <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($reservations as $r)
                            @php
                                $daysUntilEvent = now()->diffInDays($r->schedule_date, false);
                                $canCancel = $daysUntilEvent >= 7 &&
                                            in_array($r->status, ['pending', 'adviser_approved', 'admin_approved', 'approved']) &&
                                            !$r->cancellation_reason;
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">
                                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                            </svg>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                                {{ $r->service->service_name ?? 'Service' }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                @if($r->venue)
                                                    {{ $r->venue->name }}
                                                @elseif($r->custom_venue_name)
                                                    {{ $r->custom_venue_name }} (Custom)
                                                @else
                                                    No venue designated
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-white font-medium">
                                        {{ optional($r->schedule_date)->format('M d, Y') }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ optional($r->schedule_date)->format('h:i A') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($r->status === 'approved' || $r->status === 'confirmed')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400 border border-green-200 dark:border-green-800">
                                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></span>
                                            Approved
                                        </span>
                                    @elseif($r->status === 'admin_approved')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400 border border-yellow-200 dark:border-yellow-800">
                                            <span class="w-1.5 h-1.5 bg-yellow-500 rounded-full mr-1.5 animate-pulse"></span>
                                            Wait for Final
                                        </span>
                                    @elseif($r->status === 'adviser_approved')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400 border border-yellow-200 dark:border-yellow-800">
                                            <span class="w-1.5 h-1.5 bg-yellow-500 rounded-full mr-1.5 animate-pulse"></span>
                                            Wait for Priest
                                        </span>
                                    @elseif($r->status === 'pending')
                                        @php
                                            $approvedCount = $r->organizations ? $r->organizations->where('pivot.approval_status', 'approved')->count() : 0;
                                            $totalOrgs = $r->organizations ? $r->organizations->count() : 0;
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400 border border-orange-200 dark:border-orange-800">
                                            <span class="w-1.5 h-1.5 bg-orange-500 rounded-full mr-1.5 animate-pulse"></span>
                                            @if($totalOrgs > 1)
                                                Adviser ({{ $approvedCount }}/{{ $totalOrgs }})
                                            @else
                                                Pending Adviser
                                            @endif
                                        </span>
                                    @elseif($r->status === 'completed')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 border border-blue-200 dark:border-blue-800">
                                            Completed
                                        </span>
                                    @elseif($r->status === 'cancelled')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400 border border-red-200 dark:border-red-800">
                                            Cancelled
                                        </span>
                                    @elseif($r->status === 'rejected')
                                        <div class="flex flex-col items-start gap-1">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400 border border-red-200 dark:border-red-800">
                                                Rejected
                                            </span>
                                            @if($r->rejectedBy)
                                                <span class="text-[10px] text-red-600 dark:text-red-400 pl-1">
                                                    by {{ $r->rejectedBy->first_name }}
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600">
                                            {{ ucwords(str_replace('_', ' ', $r->status)) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    @if($r->priest_selection_type === 'external' && $r->external_priest_name)
                                        <div class="flex flex-col">
                                            <span class="font-medium text-gray-900 dark:text-white">{{ $r->external_priest_name }}</span>
                                            <span class="text-xs text-gray-500 italic">External Priest</span>
                                        </div>
                                    @elseif($r->officiant)
                                        <span class="font-medium text-gray-900 dark:text-white block">Fr. {{ $r->officiant->first_name }} {{ $r->officiant->last_name }}</span>
                                    @elseif($r->priests && $r->priests->isNotEmpty())
                                        <div class="flex flex-col space-y-0.5">
                                            @foreach($r->priests as $priest)
                                                <span class="font-medium text-gray-900 dark:text-white">Fr. {{ $priest->first_name }} {{ $priest->last_name }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-gray-400 dark:text-gray-600 italic">Unassigned</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('requestor.reservations.show', $r->reservation_id) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/20 px-3 py-1.5 rounded-lg transition-colors">
                                            Details
                                        </a>
                                        
                                        @php
                                            $canConfirmInline = $r->status === 'adviser_approved'
                                                && $r->contacted_at
                                                && !$r->requestor_confirmed_at
                                                && !empty($r->requestor_confirmation_token);
                                        @endphp
                                        
                                        @if($canConfirmInline)
                                            <a href="{{ route('requestor.reservations.show-confirmation', ['reservation_id' => $r->reservation_id, 'token' => $r->requestor_confirmation_token]) }}" class="text-emerald-600 hover:text-emerald-900 dark:text-emerald-400 dark:hover:text-emerald-300 bg-emerald-50 dark:bg-emerald-900/20 px-3 py-1.5 rounded-lg transition-colors">
                                                Confirm
                                            </a>
                                        @endif

                                        @if($canCancel)
                                            <button
                                                onclick='showCancelModal({{ $r->reservation_id }}, @json($r->service->service_name), @json(optional($r->schedule_date)->format("F d, Y h:i A")))'
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 bg-red-50 dark:bg-red-900/20 px-3 py-1.5 rounded-lg transition-colors">
                                                Cancel
                                            </button>
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
        
        <!-- Pagination -->
        <div class="mt-6">
            {{ $reservations->links() }}
        </div>
    </div>
</div>

<!-- Cancel Reservation Modal -->
<div id="cancelModal" class="hidden fixed inset-0 z-[100] items-center justify-center overflow-auto bg-black/50 backdrop-blur-sm transition-opacity" role="dialog" aria-modal="true">
    <div class="relative w-full max-w-lg mx-4 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl transform transition-all p-6">
        <!-- Close Button -->
        <button onclick="hideCancelModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>

        <!-- Header -->
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
            Cancel Reservation
        </h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
            Are you sure you want to cancel this reservation?
        </p>

        <!-- Warning Info -->
        <div class="flex items-start gap-3 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl mb-6">
            <svg class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div>
                <p class="text-sm font-semibold text-red-800 dark:text-red-200">
                    This action cannot be undone.
                </p>
                <p class="text-sm text-red-700 dark:text-red-300 mt-1">
                    Notifications will be sent to the adviser, priests, and staff involved.
                </p>
            </div>
        </div>

        <!-- Details -->
        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4 mb-6">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="block text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wide">Service</span>
                    <span id="cancelServiceName" class="font-medium text-gray-900 dark:text-white"></span>
                </div>
                <div>
                    <span class="block text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wide">Schedule</span>
                    <span id="cancelSchedule" class="font-medium text-gray-900 dark:text-white">MAX</span>
                </div>
            </div>
        </div>

        <form id="cancelForm" method="POST" action="">
            @csrf
            <div class="mb-6">
                <label for="cancellation_reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Reason for Cancellation <span class="text-red-500">*</span>
                </label>
                <textarea
                    id="cancellation_reason"
                    name="reason"
                    rows="3"
                    required
                    minlength="10"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-red-500 focus:ring-red-500 transition-shadow"
                    placeholder="Please explain why you are cancelling..."></textarea>
                @error('reason')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3 justify-end">
                <button
                    type="button"
                    onclick="hideCancelModal()"
                    class="px-5 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 font-medium rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors focus:ring-2 focus:ring-gray-300">
                    Keep Reservation
                </button>
                <button
                    type="submit"
                    class="px-5 py-2.5 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 focus:ring-4 focus:ring-red-500/30 transition-all shadow-lg shadow-red-500/30 flex items-center">
                    Confirm Cancellation
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showCancelModal(reservationId, serviceName, schedule) {
    document.getElementById('cancelServiceName').textContent = serviceName;
    document.getElementById('cancelSchedule').textContent = schedule;
    document.getElementById('cancelForm').action = `/requestor/reservations/${reservationId}/cancel`;
    
    const modal = document.getElementById('cancelModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex'); // Changed to flex for centering
    document.body.style.overflow = 'hidden'; // Prevent scrolling
}

function hideCancelModal() {
    const modal = document.getElementById('cancelModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.getElementById('cancellation_reason').value = '';
    document.body.style.overflow = ''; // Restore scrolling
}

// Close modal when clicking outside
document.getElementById('cancelModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        hideCancelModal();
    }
});
</script>
@endsection
