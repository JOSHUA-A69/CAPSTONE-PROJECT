<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Reservation Details
            </h2>
            <a href="{{ route('priest.reservations.index') }}" class="text-sm text-blue-600 hover:underline">
                ← Back to My Assignments
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Status Messages -->
            @if(session('status') === 'reservation-confirmed')
                <div class="mb-6 p-4 bg-green-100 dark:bg-green-900/50 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 rounded">
                    ✓ {{ session('message', 'Availability confirmed successfully!') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 bg-red-100 dark:bg-red-900/50 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-300 rounded">
                    ⚠ {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column: Reservation Details -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Main Details Card -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <div class="flex justify-between items-start mb-4">
                                <h3 class="text-2xl font-bold">{{ $reservation->activity_name ?? $reservation->service->service_name }}</h3>

                                <!-- Status Badge -->
                                @php
                                    $statusColors = [
                                        'pending_priest_confirmation' => ['bg' => 'bg-yellow-100 dark:bg-yellow-900/50', 'text' => 'text-yellow-800 dark:text-yellow-200', 'label' => 'Awaiting Your Confirmation'],
                                        'admin_approved' => ['bg' => 'bg-yellow-100 dark:bg-yellow-900/50', 'text' => 'text-yellow-800 dark:text-yellow-200', 'label' => 'Awaiting Your Confirmation'],
                                        'approved' => ['bg' => 'bg-green-100 dark:bg-green-900/50', 'text' => 'text-green-800 dark:text-green-200', 'label' => 'Confirmed'],
                                        'pending_priest_reassignment' => ['bg' => 'bg-red-100 dark:bg-red-900/50', 'text' => 'text-red-800 dark:text-red-200', 'label' => 'Declined'],
                                        'completed' => ['bg' => 'bg-gray-100 dark:bg-gray-900/50', 'text' => 'text-gray-800 dark:text-gray-200', 'label' => 'Completed'],
                                        'cancelled' => ['bg' => 'bg-red-100 dark:bg-red-900/50', 'text' => 'text-red-800 dark:text-red-200', 'label' => 'Cancelled'],
                                    ];
                                    $statusConfig = $statusColors[$reservation->status] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => ucfirst($reservation->status)];
                                @endphp

                                <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                                    {{ $statusConfig['label'] }}
                                </span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                                <div>
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Service Type</label>
                                    <p class="mt-1 text-lg font-semibold">{{ $reservation->service->service_name }}</p>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Venue</label>
                                    <p class="mt-1 text-lg font-semibold">
                                        @if($reservation->custom_venue_name)
                                            📍 {{ $reservation->custom_venue_name }} <span class="text-xs text-gray-500">(Custom Location)</span>
                                        @else
                                            {{ $reservation->venue->name }}
                                        @endif
                                    </p>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Date & Time</label>
                                    <p class="mt-1 text-lg font-semibold">
                                        {{ $reservation->schedule_date->format('F d, Y') }}
                                    </p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ $reservation->schedule_date->format('l, g:i A') }}
                                    </p>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Expected Participants</label>
                                    <p class="mt-1 text-lg font-semibold">{{ $reservation->participants_count ?? 'N/A' }}</p>
                                </div>
                            </div>

                            @if($reservation->theme)
                            <div class="mt-6">
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Theme</label>
                                <p class="mt-1 text-gray-700 dark:text-gray-300">{{ $reservation->theme }}</p>
                            </div>
                            @endif

                            @if($reservation->details)
                            <div class="mt-6">
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Additional Details</label>
                                <p class="mt-1 text-gray-700 dark:text-gray-300">{{ $reservation->details }}</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Ministry Volunteers -->
                    @if($reservation->commentator || $reservation->servers || $reservation->readers || $reservation->choir || $reservation->psalmist || $reservation->prayer_leader)
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-semibold mb-4">Ministry Volunteers</h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @if($reservation->commentator)
                                <div>
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Commentator</label>
                                    <p class="mt-1">{{ $reservation->commentator }}</p>
                                </div>
                                @endif

                                @if($reservation->servers)
                                <div>
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Servers</label>
                                    <p class="mt-1">{{ $reservation->servers }}</p>
                                </div>
                                @endif

                                @if($reservation->readers)
                                <div>
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Readers</label>
                                    <p class="mt-1">{{ $reservation->readers }}</p>
                                </div>
                                @endif

                                @if($reservation->choir)
                                <div>
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Choir</label>
                                    <p class="mt-1">{{ $reservation->choir }}</p>
                                </div>
                                @endif

                                @if($reservation->psalmist)
                                <div>
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Psalmist</label>
                                    <p class="mt-1">{{ $reservation->psalmist }}</p>
                                </div>
                                @endif

                                @if($reservation->prayer_leader)
                                <div>
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Prayer Leader</label>
                                    <p class="mt-1">{{ $reservation->prayer_leader }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Requestor Information -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-semibold mb-4">Requestor Information</h3>

                            <div class="space-y-3">
                                <div>
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</label>
                                    <p class="mt-1 font-semibold">{{ $reservation->user->full_name }}</p>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</label>
                                    <p class="mt-1">
                                        <a href="mailto:{{ $reservation->user->email }}" class="text-blue-600 hover:underline">
                                            {{ $reservation->user->email }}
                                        </a>
                                    </p>
                                </div>

                                @if($reservation->user->phone)
                                <div>
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone</label>
                                    <p class="mt-1">
                                        <a href="tel:{{ $reservation->user->phone }}" class="text-blue-600 hover:underline">
                                            {{ $reservation->user->phone }}
                                        </a>
                                    </p>
                                </div>
                                @endif

                                @if($reservation->organization)
                                <div>
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Organization</label>
                                    <p class="mt-1">{{ $reservation->organization->org_name }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Actions -->
                <div class="space-y-6">

                    <!-- Confirmation Actions -->
                    @if(in_array($reservation->status, ['pending_priest_confirmation', 'admin_approved']) && $reservation->priest_confirmation !== 'confirmed')
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-semibold mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Confirmation Required
                            </h3>

                            @if($reservation->status === 'admin_approved')
                            <div class="mb-4 p-3 bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded">
                                <p class="text-sm text-purple-800 dark:text-purple-300 font-medium">
                                    🔔 You have been reassigned to this service by the administrator.
                                </p>
                            </div>
                            @endif

                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                                Please confirm your availability for this service. If you are not available, you can decline and another priest will be assigned.
                            </p>

                            <!-- Confirm Button -->
                            <form method="POST" action="{{ route('priest.reservations.confirm', $reservation->reservation_id) }}" class="mb-3">
                                @csrf
                                <button type="submit"
                                        onclick="return confirm('Confirm your availability for this service?')"
                                        class="w-full inline-flex items-center justify-center px-4 py-3 bg-green-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Confirm Availability
                                </button>
                            </form>

                            <!-- Decline Button -->
                            <button onclick="showDeclineModal()"
                                    class="w-full inline-flex items-center justify-center px-4 py-3 bg-red-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Decline Assignment
                            </button>
                        </div>
                    </div>
                    @elseif($reservation->priest_confirmation === 'confirmed')
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <div class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded">
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-green-600 dark:text-green-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <div>
                                        <h4 class="text-sm font-semibold text-green-900 dark:text-green-200">
                                            Availability Confirmed
                                        </h4>
                                        <p class="text-sm text-green-800 dark:text-green-300">
                                            Confirmed on {{ $reservation->priest_confirmed_at->format('M d, Y') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- History Timeline -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-semibold mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Activity History
                            </h3>

                            @if($reservation->history->isEmpty())
                                <p class="text-gray-500 text-sm">No activity recorded yet.</p>
                            @else
                                <div class="space-y-4">
                                    @foreach($reservation->history->sortByDesc('created_at') as $h)
                                    <div class="flex">
                                        <div class="flex-shrink-0 w-2 bg-blue-500 rounded-full mr-4"></div>
                                        <div class="flex-1 pb-4">
                                            <p class="text-sm font-semibold">{{ ucfirst(str_replace('_', ' ', $h->action)) }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                by {{ $h->performedBy?->full_name ?? 'System' }}
                                                • {{ $h->created_at->format('M d, Y h:i A') }}
                                            </p>
                                            @if($h->remarks)
                                            <p class="text-sm text-gray-700 dark:text-gray-300 mt-2 p-2 bg-gray-50 dark:bg-gray-700 rounded">
                                                "{{ $h->remarks }}"
                                            </p>
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Decline Modal -->
    <div id="declineModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-50 items-center justify-center p-4" style="display: none;">
        <div class="bg-white dark:bg-gray-800 rounded-lg max-w-md w-full p-6">
            <div class="flex justify-between items-start mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Decline Assignment</h3>
                <button onclick="hideDeclineModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('priest.reservations.decline', $reservation->reservation_id) }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Please provide a reason for declining <span class="text-red-500">*</span>
                    </label>
                    <textarea name="reason"
                              rows="4"
                              required
                              class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                              placeholder="e.g., Schedule conflict, Prior commitment, etc."></textarea>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button"
                            onclick="hideDeclineModal()"
                            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                        Decline Assignment
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        function showDeclineModal() {
            document.getElementById('declineModal').style.display = 'flex';
        }
        function hideDeclineModal() {
            document.getElementById('declineModal').style.display = 'none';
        }
    </script>

</x-app-layout>
