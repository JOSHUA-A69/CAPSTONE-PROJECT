<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Service Assignment Details
            </h2>
            <a href="{{ route('admin.services.index') }}" class="text-sm text-blue-600 hover:underline">
                ← Back to My Services
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Status Messages -->
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-100 dark:bg-green-900/50 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 rounded">
                    ✓ {{ session('success') }}
                </div>
            @endif

            @if(session('info'))
                <div class="mb-6 p-4 bg-blue-100 dark:bg-blue-900/50 border border-blue-400 dark:border-blue-700 text-blue-700 dark:text-blue-300 rounded">
                    ℹ {{ session('info') }}
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
                                        'confirmed' => ['bg' => 'bg-green-100 dark:bg-green-900/50', 'text' => 'text-green-800 dark:text-green-200', 'label' => 'Confirmed'],
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
                                        {{ date('l, g:i A', strtotime($reservation->schedule_time)) }}
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

                            @if($reservation->purpose)
                            <div class="mt-6">
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Purpose</label>
                                <p class="mt-1 text-gray-700 dark:text-gray-300">{{ $reservation->purpose }}</p>
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

                    <!-- Requestor Information -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-semibold mb-4">Requestor Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Requested by</label>
                                    <p class="mt-1">{{ $reservation->user->full_name }}</p>
                                    <p class="text-sm text-gray-500">{{ $reservation->user->email }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Contact Person</label>
                                    <p class="mt-1">{{ $reservation->contact_person }}</p>
                                    <p class="text-sm text-gray-500">{{ $reservation->contact_number }}</p>
                                </div>
                                @if($reservation->organization)
                                <div>
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Organization</label>
                                    <p class="mt-1">{{ $reservation->organization->org_name }}</p>
                                </div>
                                @endif
                            </div>
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

                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                                Please confirm your availability for this service. If you are not available, you can decline and assign another priest.
                            </p>

                            <!-- Confirm Button -->
                            <form method="POST" action="{{ route('admin.services.confirm', $reservation->reservation_id) }}" class="mb-3">
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

                            <!-- Decline & Reassign Button -->
                            <button onclick="showDeclineModal()"
                                    class="w-full inline-flex items-center justify-center px-4 py-3 bg-red-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Decline & Reassign
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
                                            You Confirmed This Service
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

                    <!-- Admin Controls -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-semibold mb-4">Admin Actions</h3>
                            <div class="space-y-2">
                                <a href="{{ route('admin.reservations.show', $reservation->reservation_id) }}"
                                   class="w-full inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    View Full Reservation Details
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Activity History -->
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
                                    @foreach($reservation->history->sortByDesc('created_at') as $event)
                                        <div class="border-l-2 border-gray-300 dark:border-gray-600 pl-4">
                                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                {{ $event->action }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $event->created_at->format('M d, Y g:i A') }}
                                            </p>
                                            @if($event->performedBy)
                                                <p class="text-xs text-gray-600 dark:text-gray-400">
                                                    by {{ $event->performedBy->full_name }}
                                                </p>
                                            @endif
                                            @if($event->details)
                                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                                    {{ $event->details }}
                                                </p>
                                            @endif
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

    <!-- Decline & Reassign Modal -->
    <div id="declineModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                    Decline Service & Assign Another Priest
                </h3>

                <form method="POST" action="{{ route('admin.services.decline', $reservation->reservation_id) }}">
                    @csrf

                    <div class="mb-4">
                        <label for="new_priest_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Select New Priest <span class="text-red-600">*</span>
                        </label>
                        <select name="new_priest_id" id="new_priest_id" required
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">-- Select a Priest --</option>
                            @php
                                $priests = \App\Models\User::where('role', 'priest')
                                    ->where('id', '!=', auth()->id())
                                    ->orderBy('first_name')
                                    ->get();
                            @endphp
                            @foreach($priests as $priest)
                                <option value="{{ $priest->id }}">{{ $priest->full_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Reason for Declining (Optional)
                        </label>
                        <textarea name="reason" id="reason" rows="3"
                                  class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                  placeholder="e.g., Schedule conflict, Prior commitment"></textarea>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="hideDeclineModal()"
                                class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-md hover:bg-gray-400 dark:hover:bg-gray-500">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                            Decline & Reassign
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showDeclineModal() {
            document.getElementById('declineModal').classList.remove('hidden');
        }

        function hideDeclineModal() {
            document.getElementById('declineModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('declineModal')?.addEventListener('click', function(event) {
            if (event.target === this) {
                hideDeclineModal();
            }
        });
    </script>
</x-app-layout>
