<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-800 dark:text-gray-200 tracking-tight">
                    My Assigned Services
                </h2>
                <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1 max-w-lg">
                    Manage your service assignments and confirmations
                </p>
            </div>
            <!-- Optional: Mobile menu button or additional actions can go here -->
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

            <!-- Filter Tabs - Enhanced Mobile Responsive Design -->
            <div class="mb-6">
                <!-- Mobile: Stacked layout with full-width buttons -->
                <div class="block sm:hidden">
                    <div class="grid grid-cols-2 gap-2 mb-3">
                        <a href="{{ route('priest.reservations.index') }}"
                           class="flex items-center justify-center px-3 py-2.5 text-xs font-semibold rounded-xl transition-all duration-200 shadow-sm {{ !request('status') && !request('time') ? 'bg-indigo-600 text-white shadow-indigo-200' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 hover:border-indigo-300' }}">
                            All
                        </a>
                        <a href="{{ route('priest.reservations.index', ['status' => 'pending_priest_confirmation']) }}"
                           class="flex items-center justify-center px-3 py-2.5 text-xs font-semibold rounded-xl transition-all duration-200 shadow-sm {{ request('status') === 'pending_priest_confirmation' ? 'bg-yellow-600 text-white shadow-yellow-200' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-yellow-50 dark:hover:bg-yellow-900/20 hover:border-yellow-300' }}">
                            <span class="flex items-center gap-1.5">
                                Pending
                                @if($pendingConfirmationCount > 0)
                                    <span class="inline-flex items-center justify-center w-4 h-4 text-[10px] font-bold text-white bg-red-500 rounded-full">{{ $pendingConfirmationCount }}</span>
                                @endif
                            </span>
                        </a>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <a href="{{ route('priest.reservations.index', ['time' => 'upcoming']) }}"
                           class="flex items-center justify-center px-3 py-2.5 text-xs font-semibold rounded-xl transition-all duration-200 shadow-sm {{ request('time') === 'upcoming' ? 'bg-green-600 text-white shadow-green-200' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-green-50 dark:hover:bg-green-900/20 hover:border-green-300' }}">
                            <span class="flex items-center gap-1">
                                Upcoming
                                @if($upcomingCount > 0)
                                    <span class="inline-flex items-center justify-center w-4 h-4 text-[10px] font-bold text-white bg-green-500 rounded-full">{{ $upcomingCount }}</span>
                                @endif
                            </span>
                        </a>
                        <a href="{{ route('priest.reservations.index', ['time' => 'past']) }}"
                           class="flex items-center justify-center px-3 py-2.5 text-xs font-semibold rounded-xl transition-all duration-200 shadow-sm {{ request('time') === 'past' ? 'bg-gray-600 text-white shadow-gray-200' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300' }}">
                            Past
                        </a>
                        <a href="{{ route('priest.reservations.declined') }}"
                           class="flex items-center justify-center px-3 py-2.5 text-xs font-semibold rounded-xl transition-all duration-200 shadow-sm bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-red-50 dark:hover:bg-red-900/20 hover:border-red-300">
                            <span class="flex items-center gap-1">
                                Declined
                                @if($declinedCount > 0)
                                    <span class="inline-flex items-center justify-center w-4 h-4 text-[10px] font-bold text-white bg-red-500 rounded-full">{{ $declinedCount }}</span>
                                @endif
                            </span>
                        </a>
                    </div>
                </div>

                <!-- Desktop: Horizontal layout -->
                <div class="hidden sm:flex flex-wrap gap-3 items-center">
                    <a href="{{ route('priest.reservations.index') }}"
                       class="px-5 py-2.5 text-sm font-semibold rounded-xl transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-[1.02] {{ !request('status') && !request('time') ? 'bg-indigo-600 text-white shadow-indigo-200' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 hover:border-indigo-300' }}">
                        All
                    </a>
                    <a href="{{ route('priest.reservations.index', ['status' => 'pending_priest_confirmation']) }}"
                       class="px-5 py-2.5 text-sm font-semibold rounded-xl transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-[1.02] {{ request('status') === 'pending_priest_confirmation' ? 'bg-yellow-600 text-white shadow-yellow-200' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-yellow-50 dark:hover:bg-yellow-900/20 hover:border-yellow-300' }}">
                        <span class="flex items-center gap-2">
                            Pending Confirmation
                            @if($pendingConfirmationCount > 0)
                                <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold text-white bg-red-500 rounded-full">{{ $pendingConfirmationCount }}</span>
                            @endif
                        </span>
                    </a>
                    <a href="{{ route('priest.reservations.index', ['time' => 'upcoming']) }}"
                       class="px-5 py-2.5 text-sm font-semibold rounded-xl transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-[1.02] {{ request('time') === 'upcoming' ? 'bg-green-600 text-white shadow-green-200' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-green-50 dark:hover:bg-green-900/20 hover:border-green-300' }}">
                        <span class="flex items-center gap-2">
                            Upcoming
                            @if($upcomingCount > 0)
                                <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold text-white bg-green-500 rounded-full">{{ $upcomingCount }}</span>
                            @endif
                        </span>
                    </a>
                    <a href="{{ route('priest.reservations.index', ['time' => 'past']) }}"
                       class="px-5 py-2.5 text-sm font-semibold rounded-xl transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-[1.02] {{ request('time') === 'past' ? 'bg-gray-600 text-white shadow-gray-200' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300' }}">
                        Past Services
                    </a>
                    <a href="{{ route('priest.reservations.declined') }}"
                       class="px-5 py-2.5 text-sm font-semibold rounded-xl transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-[1.02] bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-red-50 dark:hover:bg-red-900/20 hover:border-red-300">
                        <span class="flex items-center gap-2">
                            Declined Services
                            @if($declinedCount > 0)
                                <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold text-white bg-red-500 rounded-full">{{ $declinedCount }}</span>
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

                                        <div class="flex items-start gap-2">
                                            <svg class="w-5 h-5 text-indigo-600 dark:text-white flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            </svg>
                                            <div class="text-gray-700 dark:text-gray-300">
                                                @if(($reservation->organizations ?? collect())->isNotEmpty())
                                                    <span>{{ $reservation->all_organization_names }}</span>
                                                @else
                                                    <span>{{ $reservation->organization->org_name ?? 'Individual' }}</span>
                                                @endif
                                            </div>
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
                              <form id="declineForm-{{ $reservation->reservation_id }}" method="POST" action="{{ route('priest.reservations.decline', $reservation->reservation_id) }}"
                                  onsubmit="showDeclineConfirmation(event, '{{ $reservation->reservation_id }}', '{{ addslashes($reservation->service->service_name) }}', '{{ $reservation->schedule_date->format('M d, Y - g:i A') }}')">
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

<!-- Decline Confirmation Modal -->
<div id="declineConfirmModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-[110] flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-2xl w-full max-w-md transform transition-all">
        <div class="px-6 py-5 border-b dark:border-gray-700">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Confirm Decline</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">This action cannot be undone</p>
                </div>
            </div>
        </div>
        <div class="px-6 py-5">
            <p class="text-gray-700 dark:text-gray-300 mb-4">Are you sure you want to <strong>DECLINE</strong> this reservation?</p>
            <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg mb-4 space-y-2">
                <div>
                    <p class="text-xs text-gray-600 dark:text-gray-400 font-medium">Service</p>
                    <p id="declineService" class="text-sm font-semibold text-gray-900 dark:text-white"></p>
                </div>
                <div>
                    <p class="text-xs text-gray-600 dark:text-gray-400 font-medium">Date & Time</p>
                    <p id="declineDate" class="text-sm font-semibold text-gray-900 dark:text-white"></p>
                </div>
            </div>
            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-3">
                <p class="text-sm text-amber-800 dark:text-amber-200">
                    <strong>Important:</strong> Administrators and the requestor will be immediately notified of your decline with your reason.
                </p>
            </div>
        </div>
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 rounded-b-lg flex gap-3">
            <button type="button" onclick="confirmDecline()" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 px-4 rounded-md transition duration-200 flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Yes, Decline
            </button>
            <button type="button" onclick="hideDeclineConfirmation()" class="flex-1 bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500 text-gray-800 dark:text-white font-semibold py-2.5 px-4 rounded-md transition duration-200">
                Cancel
            </button>
        </div>
    </div>
</div>

<script>
let currentDeclineReservationId = null;

function showDeclineConfirmation(event, reservationId, serviceName, dateTime) {
    event.preventDefault();
    currentDeclineReservationId = reservationId;

    // Update modal with reservation details
    document.getElementById('declineService').textContent = serviceName;
    document.getElementById('declineDate').textContent = dateTime;

    // Show the modal
    const modal = document.getElementById('declineConfirmModal');
    modal.classList.remove('hidden');
}

function hideDeclineConfirmation() {
    document.getElementById('declineConfirmModal').classList.add('hidden');
    currentDeclineReservationId = null;
}

function confirmDecline() {
    if (currentDeclineReservationId) {
        const form = document.getElementById('declineForm-' + currentDeclineReservationId);
        if (form) {
            form.submit();
        }
    }
}

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        hideDeclineConfirmation();
    }
});

// Close modal on outside click
document.getElementById('declineConfirmModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        hideDeclineConfirmation();
    }
});
</script>
