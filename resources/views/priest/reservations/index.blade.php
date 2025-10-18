<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            My Assigned Services
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Filter Tabs -->
            <div class="mb-6 flex flex-wrap gap-2 justify-between items-center">
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('priest.reservations.index') }}"
                       class="px-4 py-2 rounded-lg {{ !request('status') && !request('time') ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300' }}">
                        All
                    </a>
                    <a href="{{ route('priest.reservations.index', ['status' => 'pending_priest_confirmation']) }}"
                       class="px-4 py-2 rounded-lg {{ request('status') === 'pending_priest_confirmation' ? 'bg-yellow-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300' }}">
                        Pending Confirmation ({{ $pendingConfirmationCount }})
                    </a>
                    <a href="{{ route('priest.reservations.index', ['time' => 'upcoming']) }}"
                       class="px-4 py-2 rounded-lg {{ request('time') === 'upcoming' ? 'bg-green-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300' }}">
                        Upcoming ({{ $upcomingCount }})
                    </a>
                    <a href="{{ route('priest.reservations.index', ['time' => 'past']) }}"
                       class="px-4 py-2 rounded-lg {{ request('time') === 'past' ? 'bg-gray-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300' }}">
                        Past Services
                    </a>
                </div>

                <!-- Declined Services Link -->
                <a href="{{ route('priest.reservations.declined') }}"
                   class="px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white transition-colors">
                    📋 Declined Services
                </a>
            </div>

            <!-- Reservations List -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    @if($reservations->isEmpty())
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No reservations</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
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
                                <div class="border dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h3 class="text-lg font-semibold">
                                            {{ $reservation->activity_name ?? $reservation->service->service_name }}
                                        </h3>

                                        <!-- Status Badge -->
                                        @php
                                            $statusConfig = [
                                                'pending_priest_confirmation' => ['bg' => 'bg-yellow-100 dark:bg-yellow-900/50', 'text' => 'text-yellow-800 dark:text-yellow-200', 'label' => 'Awaiting Confirmation'],
                                                'admin_approved' => ['bg' => 'bg-purple-100 dark:bg-purple-900/50', 'text' => 'text-purple-800 dark:text-purple-200', 'label' => 'New Assignment'],
                                                'approved' => ['bg' => 'bg-green-100 dark:bg-green-900/50', 'text' => 'text-green-800 dark:text-green-200', 'label' => 'Confirmed'],
                                                'completed' => ['bg' => 'bg-gray-100 dark:bg-gray-900/50', 'text' => 'text-gray-800 dark:text-gray-200', 'label' => 'Completed'],
                                            ];
                                            $config = $statusConfig[$reservation->status] ?? ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'label' => ucfirst($reservation->status)];
                                        @endphp

                                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $config['bg'] }} {{ $config['text'] }}">
                                            {{ $config['label'] }}
                                        </span>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm text-gray-600 dark:text-gray-400 mb-3">
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            {{ $reservation->schedule_date->format('M d, Y - g:i A') }}
                                        </div>

                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            @if($reservation->custom_venue_name)
                                                📍 {{ $reservation->custom_venue_name }}
                                            @else
                                                {{ $reservation->venue->name }}
                                            @endif
                                        </div>

                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            </svg>
                                            {{ $reservation->organization->org_name ?? 'Individual' }}
                                        </div>
                                    </div>

                                    @if($reservation->theme)
                                    <p class="mb-3 text-sm text-gray-600 dark:text-gray-400 italic">
                                        "{{ $reservation->theme }}"
                                    </p>
                                    @endif

                                    <!-- Action Buttons -->
                                    <div class="flex gap-2 pt-3 border-t dark:border-gray-700">
                                        <a href="{{ route('priest.reservations.show', $reservation->reservation_id) }}"
                                           class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors text-center">
                                            View Full Details
                                        </a>

                                        @if($reservation->status === 'pending_priest_confirmation')
                                            <form method="POST" action="{{ route('priest.reservations.confirm', $reservation->reservation_id) }}" class="flex-1">
                                                @csrf
                                                <button type="submit"
                                                        onclick="return confirm('Are you sure you want to confirm your availability for this service?')"
                                                        class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                                                    ✓ Confirm Availability
                                                </button>
                                            </form>

                                            <button onclick="showDeclineModal{{ $reservation->reservation_id }}()"
                                                    class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">
                                                ✗ Decline
                                            </button>
                                        @endif
                                    </div>

                                    @if($reservation->status === 'pending_priest_confirmation')
                                        <!-- Decline Modal -->
                                        <div id="declineModal{{ $reservation->reservation_id }}" style="display: none; position: fixed; inset: 0; background-color: rgba(0,0,0,0.5); z-index: 50; align-items: center; justify-content: center;">
                                            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4" onclick="event.stopPropagation()">
                                                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Decline Assignment</h3>
                                                <form method="POST" action="{{ route('priest.reservations.decline', $reservation->reservation_id) }}">
                                                    @csrf
                                                    <div class="mb-4">
                                                        <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Reason for declining</label>
                                                        <textarea name="reason" required rows="3"
                                                                  class="w-full px-3 py-2 border dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-gray-100"
                                                                  placeholder="e.g., Schedule conflict, already committed to another event..."></textarea>
                                                    </div>
                                                    <div class="flex gap-2">
                                                        <button type="button"
                                                                onclick="hideDeclineModal{{ $reservation->reservation_id }}()"
                                                                class="flex-1 px-4 py-2 bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500 text-gray-800 dark:text-gray-100 rounded-lg">
                                                            Cancel
                                                        </button>
                                                        <button type="submit"
                                                                class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg">
                                                            Decline Assignment
                                                        </button>
                                                    </div>
                                                </form>
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
