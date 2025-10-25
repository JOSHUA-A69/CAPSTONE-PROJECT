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

                                        @if($reservation->status === 'pending_priest_confirmation')
                                            <form method="POST" action="{{ route('priest.reservations.confirm', $reservation->reservation_id) }}" class="flex-1">
                                                @csrf
                                                <button type="submit"
                                                        onclick="return confirm('Are you sure you want to confirm your availability for this service?')"
                                                        class="btn-success w-full justify-center">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                    Confirm Availability
                                                </button>
                                            </form>

                                            <button onclick="showDeclineModal{{ $reservation->reservation_id }}()"
                                                    class="btn-danger flex-1 justify-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                                Decline
                                            </button>
                                        @endif
                                    </div>

                                    @if($reservation->status === 'pending_priest_confirmation')
                                        <!-- Decline Modal -->
                                        <div id="declineModal{{ $reservation->reservation_id }}" style="display: none; position: fixed; inset: 0; background-color: rgba(0,0,0,0.5); z-index: 50; align-items: center; justify-content: center;">
                                            <div class="card max-w-md w-full mx-4" onclick="event.stopPropagation()">
                                                <div class="card-header">
                                                    <h3>Decline Assignment</h3>
                                                </div>
                                                <div class="card-body">
                                                    <form method="POST" action="{{ route('priest.reservations.decline', $reservation->reservation_id) }}">
                                                        @csrf
                                                        <div class="mb-4">
                                                            <label class="form-label">Reason for declining</label>
                                                            <textarea name="reason" required rows="3"
                                                                      class="form-input"
                                                                      placeholder="e.g., Schedule conflict, already committed to another event..."></textarea>
                                                        </div>
                                                        <div class="flex gap-2">
                                                            <button type="button"
                                                                    onclick="hideDeclineModal{{ $reservation->reservation_id }}()"
                                                                    class="btn-secondary flex-1">
                                                                Cancel
                                                            </button>
                                                            <button type="submit"
                                                                    class="btn-danger flex-1">
                                                                Decline Assignment
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <script>
                                            function showDeclineModal{{ $reservation->reservation_id }}() {
                                                document.getElementById('declineModal{{ $reservation->reservation_id }}').style.display = 'flex';
                                            }
                                            function hideDeclineModal{{ $reservation->reservation_id }}() {
                                                document.getElementById('declineModal{{ $reservation->reservation_id }}').style.display = 'none';
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
