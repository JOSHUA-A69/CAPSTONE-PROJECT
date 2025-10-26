<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-heading text-xl text-gray-800 dark:text-gray-200">
                My Assigned Services
            </h2>
            <p class="text-muted text-sm mt-1">Manage your service assignments and confirmations</p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Filter Tabs -->
            <div class="mb-6 flex flex-wrap gap-2 justify-between items-center">
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('priest.reservations.index') }}"
                       class="px-4 py-2 rounded-lg transition-colors duration-150 {{ !request('status') && !request('time') ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                        All
                    </a>
                    <a href="{{ route('priest.reservations.index', ['status' => 'pending_priest_confirmation']) }}"
                       class="px-4 py-2 rounded-lg transition-colors duration-150 {{ request('status') === 'pending_priest_confirmation' ? 'bg-yellow-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                        <span class="flex items-center gap-1">
                            Pending Confirmation
                            @if($pendingConfirmationCount > 0)
                                <span class="badge-warning ml-1">{{ $pendingConfirmationCount }}</span>
                            @endif
                        </span>
                    </a>
                    <a href="{{ route('priest.reservations.index', ['time' => 'upcoming']) }}"
                       class="px-4 py-2 rounded-lg transition-colors duration-150 {{ request('time') === 'upcoming' ? 'bg-green-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                        <span class="flex items-center gap-1">
                            Upcoming
                            @if($upcomingCount > 0)
                                <span class="badge-success ml-1">{{ $upcomingCount }}</span>
                            @endif
                        </span>
                    </a>
                    <a href="{{ route('priest.reservations.index', ['time' => 'past']) }}"
                       class="px-4 py-2 rounded-lg transition-colors duration-150 {{ request('time') === 'past' ? 'bg-gray-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                        Past Services
                    </a>
                </div>

                <!-- Declined Services Link -->
                <a href="{{ route('priest.reservations.declined') }}"
                   class="btn-danger">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Declined Services
                </a>
            </div>

            <!-- Reservations List -->
            <div class="card">
                <div class="card-body">

                    @if($reservations->isEmpty())
                        <div class="text-center py-12">
                            <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <h3 class="text-heading">No reservations</h3>
                            <p class="text-muted mt-2">
                                @if(request('status') === 'pending_priest_confirmation')
                                    You have no pending confirmations at this time.
                                @elseif(request('time') === 'upcoming')
                                    You have no upcoming services scheduled.
                                @elseif(request('time') === 'past')
                                    You have no past service records.
                                @else
                                    You have no assigned services yet.
                                @endif
                            </p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($reservations as $reservation)
                                <div class="card-hover border dark:border-gray-700 rounded-lg p-5">
                                    <div class="flex items-center gap-3 mb-3">
                                        <h3 class="text-heading text-lg font-semibold flex-1">
                                            {{ $reservation->activity_name ?? $reservation->service->service_name }}
                                        </h3>

                                        <!-- Status Badge -->
                                        @if($reservation->status === 'pending_priest_confirmation')
                                            <span class="badge-warning">Awaiting Confirmation</span>
                                        @elseif($reservation->status === 'admin_approved')
                                            <span class="badge-info">New Assignment</span>
                                        @elseif($reservation->status === 'approved')
                                            <span class="badge-success">Confirmed</span>
                                        @elseif($reservation->status === 'completed')
                                            <span class="badge-secondary">Completed</span>
                                        @else
                                            <span class="badge-secondary">{{ ucfirst($reservation->status) }}</span>
                                        @endif
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm text-gray-600 dark:text-gray-400 mb-3">
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            <span class="text-body">{{ $reservation->schedule_date->format('M d, Y - g:i A') }}</span>
                                        </div>

                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            @if($reservation->custom_venue_name)
                                                <span class="text-body">{{ $reservation->custom_venue_name }}</span>
                                                <span class="badge-info ml-2">Custom</span>
                                            @else
                                                <span class="text-body">{{ $reservation->venue->name }}</span>
                                            @endif
                                        </div>

                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            </svg>
                                            <span class="text-body">{{ $reservation->organization->org_name ?? 'Individual' }}</span>
                                        </div>
                                    </div>

                                    @if($reservation->theme)
                                    <p class="mb-3 text-sm text-gray-600 dark:text-gray-400 italic bg-gray-50 dark:bg-gray-700 p-2 rounded">
                                        "{{ $reservation->theme }}"
                                    </p>
                                    @endif

                                    <!-- Action Buttons -->
                                    <div class="flex flex-col sm:flex-row gap-2 pt-3 border-t dark:border-gray-700">
                                        <a href="{{ route('priest.reservations.show', $reservation->reservation_id) }}"
                                           class="btn-primary flex-1 justify-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            View Full Details
                                        </a>

                                        @if(in_array($reservation->status, ['pending_priest_confirmation', 'admin_approved']) &&
                                            (!$reservation->priest_confirmation || $reservation->priest_confirmation === 'pending'))
                                            <!-- Action Buttons -->
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-4">
                                                <form method="POST" action="{{ route('priest.reservations.confirm', $reservation->reservation_id) }}">
                                                    @csrf
                                                    <button type="submit"
                                                            onclick="return confirm('Are you sure you want to confirm your availability for this service?')"
                                                            class="w-full px-4 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-all shadow-md hover:shadow-lg transform hover:scale-105 flex items-center justify-center">
                                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                        Confirm Availability
                                                    </button>
                                                </form>

                                                <button onclick="showDeclineModal{{ $reservation->reservation_id }}()"
                                                        class="w-full px-4 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-all shadow-md hover:shadow-lg transform hover:scale-105 flex items-center justify-center">
                                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                    Decline
                                                </button>
                                            </div>
                                        @endif
                                    </div>

                                    @if(in_array($reservation->status, ['pending_priest_confirmation', 'admin_approved']) &&
                                        (!$reservation->priest_confirmation || $reservation->priest_confirmation === 'pending'))
                                        <!-- Decline Modal -->
                                        <div id="declineModal{{ $reservation->reservation_id }}"
                                             class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 items-center justify-center p-4"
                                             onclick="hideDeclineModal{{ $reservation->reservation_id }}()">
                                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-md w-full transform transition-all"
                                                 onclick="event.stopPropagation()">
                                                <!-- Modal Header -->
                                                <div class="bg-red-50 dark:bg-red-900/20 px-6 py-4 border-b border-red-100 dark:border-red-800 rounded-t-xl">
                                                    <div class="flex items-center justify-between">
                                                        <h3 class="text-lg font-semibold text-red-900 dark:text-red-100 flex items-center">
                                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                                            </svg>
                                                            Decline Assignment
                                                        </h3>
                                                        <button type="button"
                                                                onclick="hideDeclineModal{{ $reservation->reservation_id }}()"
                                                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>

                                                <!-- Modal Body -->
                                                <form method="POST" action="{{ route('priest.reservations.decline', ['reservation_id' => $reservation->reservation_id]) }}" class="p-6">
                                                    @csrf
                                                    <div class="mb-6">
                                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                            Reason for declining <span class="text-red-500">*</span>
                                                        </label>
                                                        <textarea name="reason"
                                                                  required
                                                                  rows="4"
                                                                  class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white transition-all"
                                                                  placeholder="Please provide a reason for declining (e.g., schedule conflict, prior commitment, health reasons...)"></textarea>
                                                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                                            This information will be shared with the admin for reassignment.
                                                        </p>
                                                    </div>

                                                    <!-- Action Buttons -->
                                                    <div class="flex gap-3">
                                                        <button type="button"
                                                                onclick="hideDeclineModal{{ $reservation->reservation_id }}()"
                                                                class="flex-1 px-4 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg font-medium transition-colors">
                                                            Cancel
                                                        </button>
                                                        <button type="submit"
                                                                class="flex-1 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors shadow-lg hover:shadow-xl transform hover:scale-105">
                                                            Decline Assignment
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <script>
                                            function showDeclineModal{{ $reservation->reservation_id }}() {
                                                const modal = document.getElementById('declineModal{{ $reservation->reservation_id }}');
                                                modal.classList.remove('hidden');
                                                modal.classList.add('flex');
                                                document.body.style.overflow = 'hidden';
                                            }
                                            function hideDeclineModal{{ $reservation->reservation_id }}() {
                                                const modal = document.getElementById('declineModal{{ $reservation->reservation_id }}');
                                                modal.classList.add('hidden');
                                                modal.classList.remove('flex');
                                                document.body.style.overflow = 'auto';
                                            }
                                        </script>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            {{ $reservations->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
