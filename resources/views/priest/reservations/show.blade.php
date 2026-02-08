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
                                @if(in_array($reservation->status, ['adviser_approved', 'admin_approved']) && $reservation->priest_confirmation !== 'confirmed')
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
                                    <p class="text-heading">{{ $reservation->user?->full_name ?? 'Unknown User' }}</p>
                                </div>

                                <div>
                                    <label class="form-label">Email</label>
                                    <p class="mt-1">
                                        @if($reservation->user?->email)
                                        <a href="mailto:{{ $reservation->user->email }}" class="text-indigo-600 hover:text-indigo-900 transition-colors duration-150">
                                            {{ $reservation->user->email }}
                                        </a>
                                        @else
                                        <span class="text-muted">N/A</span>
                                        @endif
                                    </p>
                                </div>

                                @if($reservation->user?->phone)
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

                    <!-- History Timeline -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Activity History
                                </h3>
                                @if(!$reservation->history->where('archived_at', null)->isEmpty())
                                <button onclick="clearAllHistory()" class="text-xs px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-md transition-colors font-medium">
                                    Clear All
                                </button>
                                @endif
                            </div>

                            @php
                                $activeHistory = $reservation->history->where('archived_at', null)->sortByDesc('created_at');
                            @endphp

                            @if($activeHistory->isEmpty())
                                <p class="text-gray-500 text-sm">No activity recorded yet.</p>
                            @else
                                <div class="space-y-3">
                                    @foreach($activeHistory as $h)
                                    <div class="history-item bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow" id="history-{{ $h->history_id }}">
                                        <div class="flex justify-between items-start gap-4">
                                            <div class="flex flex-1">
                                                <div class="flex-shrink-0 w-2 bg-blue-500 rounded-full mr-4"></div>
                                                <div class="flex-1">
                                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ ucfirst(str_replace('_', ' ', $h->action)) }}</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                        by {{ $h->performedBy?->full_name ?? 'System' }}
                                                        • {{ $h->created_at->format('M d, Y h:i A') }}
                                                    </p>
                                                    @if($h->remarks)
                                                    <p class="text-sm text-gray-700 dark:text-gray-300 mt-2 p-2 bg-gray-50 dark:bg-gray-600 rounded">
                                                        "{{ $h->remarks }}"
                                                    </p>
                                                    @endif
                                                </div>
                                            </div>
                                            <button onclick="archiveHistory({{ $h->history_id }})" class="flex-shrink-0 text-gray-400 hover:text-red-500 transition-colors" title="Archive this item">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
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

                @if(isset($availablePriests) && $availablePriests->count() > 0)
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Optional: Suggest a replacement priest
                    </label>
                    <select name="replacement_priest_id"
                            class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">-- No replacement (notify admin to reassign) --</option>
                        @foreach($availablePriests as $p)
                            <option value="{{ $p->id }}">{{ $p->first_name }} {{ $p->last_name }}</option>
                        @endforeach
                    </select>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                        We’ll notify the selected priest immediately and mark this service awaiting their confirmation.
                    </p>
                </div>
                @endif

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

                @if(isset($availablePriests) && $availablePriests->count() > 0)
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Optional: Assign an available replacement now
                    </label>
                    <select name="replacement_priest_id"
                            class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">-- No replacement (notify admin to reassign) --</option>
                        @foreach($availablePriests as $p)
                            <option value="{{ $p->id }}">{{ $p->first_name }} {{ $p->last_name }}</option>
                        @endforeach
                    </select>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                        We’ll notify the selected priest immediately and mark this service awaiting their confirmation.
                    </p>
                </div>
                @endif

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
        @if(request()->has('notification_read'))
        // Dispatch notification update event since notification was marked as read
        window.addEventListener('DOMContentLoaded', function() {
            if (window.dispatchEvent) {
                window.dispatchEvent(new Event('notification-update'));
            }
        });
        @endif

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

        // Archive single history item
        function archiveHistory(historyId) {
            if (!confirm('Archive this activity history item?')) return;

            fetch(`/priest/history/${historyId}/archive`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const historyItem = document.getElementById(`history-${historyId}`);
                    historyItem.style.transition = 'opacity 0.3s, transform 0.3s';
                    historyItem.style.opacity = '0';
                    historyItem.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        historyItem.remove();
                        // Check if no more history items and reload page
                        const remainingItems = document.querySelectorAll('.history-item');
                        if (remainingItems.length === 0) {
                            location.reload();
                        }
                    }, 300);
                } else {
                    alert('Failed to archive history item');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        }

        // Clear all history items
        function clearAllHistory() {
            if (!confirm('Archive all activity history items for this reservation?')) return;

            const reservationId = {{ $reservation->reservation_id }};

            fetch(`/priest/history/reservation/${reservationId}/clear-all`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Fade out all history items
                    const historyItems = document.querySelectorAll('.history-item');
                    historyItems.forEach(item => {
                        item.style.transition = 'opacity 0.3s, transform 0.3s';
                        item.style.opacity = '0';
                        item.style.transform = 'scale(0.95)';
                    });
                    setTimeout(() => {
                        location.reload();
                    }, 300);
                } else {
                    alert('Failed to clear history');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        }
    </script>

</x-app-layout>
