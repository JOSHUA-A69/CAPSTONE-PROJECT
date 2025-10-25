<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <h2 class="text-heading text-xl text-gray-800 dark:text-gray-200">
                Reservation Details
            </h2>
            <a href="{{ route('priest.reservations.index') }}" class="btn-ghost">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to My Assignments
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Status Messages -->
            @if(session('status') === 'reservation-confirmed')
                <div class="mb-6">
                    <span class="badge-success">
                        <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        {{ session('message', 'Availability confirmed successfully!') }}
                    </span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6">
                    <span class="badge-danger">
                        <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        {{ session('error') }}
                    </span>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column: Reservation Details -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Main Details Card -->
                    <div class="card">
                        <div class="card-body">
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4 mb-6">
                                <h3 class="text-heading text-2xl font-bold">{{ $reservation->activity_name ?? $reservation->service->service_name }}</h3>

                                <!-- Status Badge -->
                                @if(in_array($reservation->status, ['pending_priest_confirmation', 'admin_approved']))
                                    <span class="badge-warning whitespace-nowrap">Awaiting Your Confirmation</span>
                                @elseif($reservation->status === 'approved')
                                    <span class="badge-success whitespace-nowrap">Confirmed</span>
                                @elseif($reservation->status === 'pending_priest_reassignment')
                                    <span class="badge-danger whitespace-nowrap">Declined</span>
                                @elseif($reservation->status === 'completed')
                                    <span class="badge-secondary whitespace-nowrap">Completed</span>
                                @elseif($reservation->status === 'cancelled')
                                    <span class="badge-danger whitespace-nowrap">Cancelled</span>
                                @else
                                    <span class="badge-secondary whitespace-nowrap">{{ ucfirst($reservation->status) }}</span>
                                @endif
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                                <div>
                                    <label class="form-label">Service Type</label>
                                    <p class="text-heading text-lg">{{ $reservation->service->service_name }}</p>
                                </div>

                                <div>
                                    <label class="form-label">Venue</label>
                                    <p class="text-heading text-lg">
                                        @if($reservation->custom_venue_name)
                                            {{ $reservation->custom_venue_name }}
                                            <span class="badge-info ml-2">Custom Location</span>
                                        @else
                                            {{ $reservation->venue->name }}
                                        @endif
                                    </p>
                                </div>

                                <div>
                                    <label class="form-label">Date & Time</label>
                                    <p class="text-heading text-lg text-indigo-600">
                                        {{ $reservation->schedule_date->format('F d, Y') }}
                                    </p>
                                    <p class="text-muted text-sm">
                                        {{ $reservation->schedule_date->format('l, g:i A') }}
                                    </p>
                                </div>

                                <div>
                                    <label class="form-label">Expected Participants</label>
                                    <p class="text-heading text-lg">{{ $reservation->participants_count ?? 'N/A' }}</p>
                                </div>
                            </div>

                            @if($reservation->theme)
                            <div class="mt-6">
                                <label class="form-label">Theme</label>
                                <p class="text-body mt-1">{{ $reservation->theme }}</p>
                            </div>
                            @endif

                            @if($reservation->details)
                            <div class="mt-6">
                                <label class="form-label">Additional Details</label>
                                <p class="text-body mt-1">{{ $reservation->details }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Ministry Volunteers -->
                    @if($reservation->commentator || $reservation->servers || $reservation->readers || $reservation->choir || $reservation->psalmist || $reservation->prayer_leader)
                    <div class="card">
                        <div class="card-header">
                            <h3>Ministry Volunteers</h3>
                        </div>
                        <div class="card-body">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @if($reservation->commentator)
                                <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">
                                    <label class="form-label">Commentator</label>
                                    <p class="text-body">{{ $reservation->commentator }}</p>
                                </div>
                                @endif

                                @if($reservation->servers)
                                <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">
                                    <label class="form-label">Servers</label>
                                    <p class="text-body">{{ $reservation->servers }}</p>
                                </div>
                                @endif

                                @if($reservation->readers)
                                <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">
                                    <label class="form-label">Readers</label>
                                    <p class="text-body">{{ $reservation->readers }}</p>
                                </div>
                                @endif

                                @if($reservation->choir)
                                <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">
                                    <label class="form-label">Choir</label>
                                    <p class="text-body">{{ $reservation->choir }}</p>
                                </div>
                                @endif

                                @if($reservation->psalmist)
                                <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">
                                    <label class="form-label">Psalmist</label>
                                    <p class="text-body">{{ $reservation->psalmist }}</p>
                                </div>
                                @endif

                                @if($reservation->prayer_leader)
                                <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">
                                    <label class="form-label">Prayer Leader</label>
                                    <p class="text-body">{{ $reservation->prayer_leader }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Requestor Information -->
                    <div class="card">
                        <div class="card-header">
                            <h3>Requestor Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="space-y-4">
                                <div>
                                    <label class="form-label">Name</label>
                                    <p class="text-heading">{{ $reservation->user->full_name }}</p>
                                </div>

                                <div>
                                    <label class="form-label">Email</label>
                                    <p class="mt-1">
                                        <a href="mailto:{{ $reservation->user->email }}" class="text-indigo-600 hover:text-indigo-900 transition-colors duration-150">
                                            {{ $reservation->user->email }}
                                        </a>
                                    </p>
                                </div>

                                @if($reservation->user->phone)
                                <div>
                                    <label class="form-label">Phone</label>
                                    <p class="mt-1">
                                        <a href="tel:{{ $reservation->user->phone }}" class="text-indigo-600 hover:text-indigo-900 transition-colors duration-150">
                                            {{ $reservation->user->phone }}
                                        </a>
                                    </p>
                                </div>
                                @endif

                                @if($reservation->organization)
                                <div>
                                    <label class="form-label">Organization</label>
                                    <p class="text-body">{{ $reservation->organization->org_name }}</p>
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
                    <div class="card">
                        <div class="card-header">
                            <h3 class="flex items-center">
                                <svg class="w-5 h-5 mr-2 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Confirmation Required
                            </h3>
                        </div>
                        <div class="card-body">
                            @if($reservation->status === 'admin_approved')
                            <div class="mb-4 p-3 bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg">
                                <p class="text-sm text-purple-800 dark:text-purple-300 font-medium">
                                    🔔 You have been reassigned to this service by the administrator.
                                </p>
                            </div>
                            @endif

                            <p class="text-muted text-sm mb-6">
                                Please confirm your availability for this service. If you are not available, you can decline and another priest will be assigned.
                            </p>

                            <!-- Confirm Button -->
                            <form method="POST" action="{{ route('priest.reservations.confirm', $reservation->reservation_id) }}" class="mb-3">
                                @csrf
                                <button type="submit"
                                        onclick="return confirm('Confirm your availability for this service?')"
                                        class="btn-success w-full justify-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Confirm Availability
                                </button>
                            </form>

                            <!-- Decline Button -->
                            <button onclick="showDeclineModal()"
                                    class="btn-danger w-full justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Decline Assignment
                            </button>
                        </div>
                    </div>
                    @elseif($reservation->priest_confirmation === 'confirmed')
                    <div class="card">
                        <div class="card-body">
                            <div class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg mb-4">
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

                            <!-- Cancel Confirmation Option -->
                            @if($reservation->status === 'approved' && $reservation->schedule_date->isFuture())
                            <div class="mt-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                                <p class="text-sm text-gray-700 dark:text-gray-300 mb-3">
                                    <strong>Need to cancel?</strong> If you can no longer attend this service, you can cancel your confirmation and the administrator will be notified to assign another priest.
                                </p>
                                <button onclick="showCancelConfirmationModal()"
                                        class="btn-danger w-full justify-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    Cancel My Confirmation
                                </button>
                            </div>
                            @endif
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

    <!-- Cancel Confirmation Modal -->
    <div id="cancelConfirmationModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-50 items-center justify-center p-4" style="display: none;">
        <div class="bg-white dark:bg-gray-800 rounded-lg max-w-md w-full p-6">
            <div class="flex justify-between items-start mb-4">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Cancel Confirmed Reservation</h3>
                </div>
                <button onclick="hideCancelConfirmationModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="mb-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded">
                <p class="text-sm text-red-800 dark:text-red-300">
                    ⚠️ <strong>Warning:</strong> You have already confirmed your availability for this service. Cancelling now will require the administrator to find another priest urgently.
                </p>
            </div>

            <form method="POST" action="{{ route('priest.reservations.decline', $reservation->reservation_id) }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Please provide a reason for cancellation <span class="text-red-500">*</span>
                    </label>
                    <textarea name="reason"
                              rows="4"
                              required
                              class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                              placeholder="e.g., Emergency, Health issue, Unavoidable conflict, etc."></textarea>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button"
                            onclick="hideCancelConfirmationModal()"
                            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600">
                        Keep My Confirmation
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                        Yes, Cancel Confirmation
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
        function showCancelConfirmationModal() {
            document.getElementById('cancelConfirmationModal').style.display = 'flex';
        }
        function hideCancelConfirmationModal() {
            document.getElementById('cancelConfirmationModal').style.display = 'none';
        }
    </script>

</x-app-layout>
