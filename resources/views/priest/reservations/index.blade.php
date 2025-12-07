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

            <!-- Success Messages -->
            @if(session('status') === 'reservation-confirmed')
                <div class="mb-6 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 p-4">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <h3 class="text-sm font-semibold text-green-900 dark:text-green-200 mb-1">
                                ✓ Availability Confirmed Successfully!
                            </h3>
                            <p class="text-sm text-green-800 dark:text-green-300">
                                {{ session('message', 'Your availability has been confirmed. The requestor and administrators have been notified.') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('status') === 'reservation-declined')
                <div class="mb-6 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 p-4">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <h3 class="text-sm font-semibold text-blue-900 dark:text-blue-200 mb-1">
                                Assignment Declined Successfully
                            </h3>
                            <p class="text-sm text-blue-800 dark:text-blue-300">
                                {{ session('message', 'Your decline notification has been sent. Administrators have been notified to assign another priest.') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-4">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <h3 class="text-sm font-semibold text-red-900 dark:text-red-200 mb-1">
                                Error
                            </h3>
                            <p class="text-sm text-red-800 dark:text-red-300">
                                {{ session('error') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

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
                    <a href="{{ route('priest.reservations.declined') }}"
                       class="px-4 py-2 rounded-lg transition-colors duration-150 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <span class="flex items-center gap-1">
                            Declined Services
                            @if($declinedCount > 0)
                                <span class="badge-secondary ml-1">{{ $declinedCount }}</span>
                            @endif
                        </span>
                    </a>
                </div>
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
                                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 hover:shadow-lg transition-all duration-200 hover:border-indigo-300 dark:hover:border-indigo-700">
                                    <div class="flex items-center gap-3 mb-4">
                                        <h3 class="text-gray-900 dark:text-white text-lg font-semibold flex-1">
                                            {{ $reservation->activity_name ?? $reservation->service->service_name }}
                                        </h3>

                                        <!-- Status Badge -->
                                        @if($reservation->status === 'pending')
                                            <span class="badge-warning">Awaiting Adviser</span>
                                        @elseif($reservation->status === 'adviser_approved')
                                            <span class="badge-warning">Awaiting Your Confirmation</span>
                                        @elseif($reservation->status === 'pending_priest_confirmation')
                                            <span class="badge-warning">Awaiting Confirmation</span>
                                        @elseif($reservation->status === 'admin_approved')
                                            <span class="badge-info">Awaiting Admin</span>
                                        @elseif($reservation->status === 'approved')
                                            <span class="badge-success">Approved by Admin</span>
                                        @elseif($reservation->status === 'confirmed')
                                            <span class="badge-success">Confirmed</span>
                                        @elseif($reservation->status === 'completed')
                                            <span class="badge-secondary">Completed</span>
                                        @else
                                            <span class="badge-secondary">{{ ucfirst($reservation->status) }}</span>
                                        @endif
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm mb-4">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-5 h-5 text-indigo-600 dark:text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            <span class="text-gray-700 dark:text-gray-300">{{ $reservation->schedule_date->format('M d, Y - g:i A') }}</span>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <svg class="w-5 h-5 text-indigo-600 dark:text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            @if($reservation->custom_venue_name)
                                                <span class="text-gray-700 dark:text-gray-300">{{ $reservation->custom_venue_name }}</span>
                                                <span class="badge-info ml-2">Custom</span>
                                            @else
                                                <span class="text-gray-700 dark:text-gray-300">{{ $reservation->venue->name }}</span>
                                            @endif
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <svg class="w-5 h-5 text-indigo-600 dark:text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            </svg>
                                            <span class="text-gray-700 dark:text-gray-300">{{ $reservation->organization->org_name ?? 'Individual' }}</span>
                                        </div>
                                    </div>

                                    @if($reservation->theme)
                                    <p class="mb-4 text-sm text-gray-700 dark:text-gray-300 italic bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg border-l-4 border-indigo-500">
                                        "{{ $reservation->theme }}"
                                    </p>
                                    @endif

                                    <!-- Action Buttons -->
                                    @if(in_array($reservation->status, ['adviser_approved', 'admin_approved']) &&
                                        $reservation->priest_confirmation !== 'confirmed' &&
                                        $reservation->priest_confirmation !== 'declined')
                                        <!-- Pending Confirmation Actions -->
                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-4 border-t dark:border-gray-700">
                                            <a href="{{ route('priest.reservations.show', $reservation) }}" class="btn-secondary text-center">
                                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                View Full Details
                                            </a>

                                            <form action="{{ route('priest.reservations.confirm', $reservation->reservation_id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" onclick="return confirm(`Are you sure you want to CONFIRM this reservation?\n\nService: {{ addslashes($reservation->activity_name ?? $reservation->service->service_name) }}\nDate: {{ $reservation->schedule_date->format('M d, Y - g:i A') }}\nVenue: {{ addslashes($reservation->custom_venue_name ?? $reservation->venue->name) }}\n\nBy confirming, you are committing to officiate this service. The requestor and administrators will be notified of your confirmation.`)" class="btn-primary w-full">
                                                    <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                    Confirm Availability
                                                </button>
                                            </form>

                                            <button type="button" onclick="toggleDeclinePanel('{{ $reservation->reservation_id }}')"
                                                    class="btn-danger">
                                                <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                                Decline
                                            </button>
                                        </div>
                                    @else
                                        <!-- View Details Only -->
                                        <div class="pt-4 border-t dark:border-gray-700">
                                            <a href="{{ route('priest.reservations.show', $reservation) }}" class="btn-secondary block text-center">
                                                <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                View Full Details
                                            </a>
                                        </div>
                                    @endif

                                    @if(in_array($reservation->status, ['adviser_approved', 'admin_approved']) &&
                                        $reservation->priest_confirmation !== 'confirmed' &&
                                        $reservation->priest_confirmation !== 'declined')
                                        <!-- Inline Decline Panel (more reliable than overlay modal) -->
                                        <div id="declinePanel-{{ $reservation->reservation_id }}" class="hidden mt-4 p-4 border border-red-200 dark:border-red-800 rounded-lg bg-red-50 dark:bg-red-900/10">
                              <form method="POST" action="{{ route('priest.reservations.decline', $reservation->reservation_id) }}"
                                  onsubmit="return confirm(`Are you sure you want to DECLINE this reservation?\n\nService: {{ addslashes($reservation->service->service_name) }}\nDate: {{ $reservation->schedule_date->format('M d, Y - g:i A') }}`)">
                                                @csrf
                                                <label class="block text-sm font-medium text-red-900 dark:text-red-200 mb-2">
                                                    Reason for declining <span class="text-red-600">*</span>
                                                </label>
                                                <textarea name="reason" rows="3" required
                                                          class="w-full px-3 py-2 border border-red-300 dark:border-red-700 rounded-md focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-800 dark:text-gray-100"
                                                          placeholder="e.g., Schedule conflict, prior commitment, health reasons..."></textarea>
                                                <div class="mt-3 flex gap-2 justify-end">
                                                    <button type="button" onclick="toggleDeclinePanel('{{ $reservation->reservation_id }}')" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md">Cancel</button>
                                                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md">Submit Decline</button>
                                                </div>
                                            </form>
                                        </div>
                                        <script>
                                            window.toggleDeclinePanel = function(id) {
                                                const panel = document.getElementById('declinePanel-' + id);
                                                if(panel){ panel.classList.toggle('hidden'); }
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
