<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Notifications') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Action Buttons -->
                    <div class="flex flex-wrap gap-2 sm:gap-3 mb-6">
                        <a href="{{ route('requestor.notifications.archived') }}" class="inline-flex items-center px-2.5 sm:px-4 py-1.5 sm:py-2 bg-gray-600 hover:bg-gray-700 text-white text-xs sm:text-sm font-medium rounded-lg sm:rounded-xl transition-all duration-200 ease-in-out hover:shadow-md">
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 mr-1.5 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                            </svg>
                            <span class="hidden sm:inline">View Archived</span>
                            <span class="sm:hidden">Archived</span>
                        </a>

                        @if($notifications->count() > 0)
                            <button onclick="clearAllNotifications()" class="inline-flex items-center px-2.5 sm:px-4 py-1.5 sm:py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs sm:text-sm font-medium rounded-lg sm:rounded-xl transition-all duration-200 ease-in-out hover:shadow-md">
                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 mr-1.5 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                <span class="hidden sm:inline">Clear All</span>
                                <span class="sm:hidden">Clear</span>
                            </button>

                            <button onclick="markAllAsRead()" class="inline-flex items-center px-2.5 sm:px-4 py-1.5 sm:py-2 bg-indigo-500 hover:bg-indigo-600 text-white text-xs sm:text-sm font-medium rounded-lg sm:rounded-xl transition-all duration-200 ease-in-out hover:shadow-md">
                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 mr-1.5 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="hidden sm:inline">Mark All as Read</span>
                                <span class="sm:hidden">Mark Read</span>
                            </button>
                        @endif
                    </div>

                    @if($notifications->isEmpty())
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zm-6 0H4l5 5v-5zm6-10h5l-5-5v5zm-6 0H4l5-5v5z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No notifications</h3>
                            <p class="mt-1 text-sm text-gray-500">You're all caught up!</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($notifications as $notification)
                            <div class="relative bg-white dark:bg-gray-700 border {{ !$notification->read_at ? 'border-indigo-200 dark:border-indigo-700 bg-indigo-50 dark:bg-indigo-900/20' : 'border-gray-200 dark:border-gray-600' }} rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow duration-200" id="notification-{{ $notification->notification_id }}">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center mb-2 gap-2">
                                            @php
                                                $typeColors = [
                                                    'approved' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                                                    'rejection' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
                                                    'cancelled' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
                                                    'new_reservation' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
                                                    'reminder' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
                                                    'status_update' => 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900 dark:text-cyan-300',
                                                    'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
                                                    'completed' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-300',
                                                ];
                                                $badgeColor = $typeColors[$notification->type] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-300';
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeColor }}">
                                                {{ ucfirst(str_replace('_', ' ', $notification->type)) }}
                                            </span>
                                            @if(!$notification->read_at)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300">
                                                    New
                                                </span>
                                            @endif
                                        </div>

                                        <p class="text-sm text-gray-900 dark:text-gray-100 mb-1">
                                            {{ $notification->message }}
                                        </p>

                                        @if($notification->reservation)
                                            <p class="text-xs text-gray-600 dark:text-gray-400">
                                                Reservation #{{ $notification->reservation_id }} -
                                                {{ $notification->reservation->schedule_date->format('M d, Y h:i A') }}
                                            </p>
                                        @endif

                                        <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                            {{ optional($notification->sent_at)->diffForHumans() ?? optional($notification->created_at)->diffForHumans() ?? 'N/A' }}
                                        </p>
                                    </div>

                                    <div class="ml-4 flex-shrink-0 flex items-start gap-2">
                                        @if($notification->reservation_id)
                                        <a href="{{ route('requestor.reservations.show', $notification->reservation_id) }}"
                                           class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 rounded-md transition-colors text-xs font-medium"
                                           title="View reservation">
                                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            View
                                        </a>
                                        @endif

                                        <button onclick="archiveNotification({{ $notification->notification_id }})"
                                                class="w-8 h-8 flex items-center justify-center bg-gray-100 hover:bg-red-100 text-gray-500 hover:text-red-600 rounded-full transition-colors"
                                                title="Archive this notification">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        @if($notifications->hasPages())
                            <div class="mt-6">
                                {{ $notifications->links() }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        // Archive single notification with modal confirmation
        function archiveNotification(notificationId) {
            const modalHtml = `
                <div id="archive-modal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeArchiveModal()"></div>
                        <div class="relative inline-block overflow-hidden text-left align-bottom bg-white dark:bg-gray-800 rounded-lg shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                            <div class="bg-gradient-to-r from-amber-500 to-orange-500 px-4 py-3">
                                <h3 class="text-lg font-medium text-white">Archive Notification</h3>
                            </div>
                            <div class="px-4 py-5 sm:p-6">
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Are you sure you want to archive this notification? You can restore it later from the archived notifications page.
                                </p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                                <button onclick="confirmArchive(${notificationId})" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-amber-500 text-base font-medium text-white hover:bg-amber-600 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                                    Archive
                                </button>
                                <button onclick="closeArchiveModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm transition-colors">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalHtml);
        }

        function closeArchiveModal() {
            const modal = document.getElementById('archive-modal');
            if (modal) modal.remove();
        }

        function confirmArchive(notificationId) {
            closeArchiveModal();
            fetch(`/requestor/notifications/${notificationId}/archive`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const item = document.getElementById(`notification-${notificationId}`);
                    item.style.transition = 'opacity 0.3s, transform 0.3s';
                    item.style.opacity = '0';
                    item.style.transform = 'translateX(20px)';
                    setTimeout(() => {
                        item.remove();
                        if (document.querySelectorAll('[id^="notification-"]').length === 0) {
                            location.reload();
                        }
                    }, 300);
                } else {
                    alert('Failed to archive notification');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while archiving');
            });
        }

        // Clear all notifications
        function clearAllNotifications() {
            const modalHtml = `
                <div id="clear-all-modal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeClearAllModal()"></div>
                        <div class="relative inline-block overflow-hidden text-left align-bottom bg-white dark:bg-gray-800 rounded-lg shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                            <div class="bg-gradient-to-r from-amber-500 to-orange-500 px-4 py-3">
                                <h3 class="text-lg font-medium text-white">Archive All Notifications</h3>
                            </div>
                            <div class="px-4 py-5 sm:p-6">
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Are you sure you want to archive all notifications? They will be moved to your archived notifications.
                                </p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                                <button onclick="confirmClearAll()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-amber-500 text-base font-medium text-white hover:bg-amber-600 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                                    Archive All
                                </button>
                                <button onclick="closeClearAllModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm transition-colors">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalHtml);
        }

        function closeClearAllModal() {
            const modal = document.getElementById('clear-all-modal');
            if (modal) modal.remove();
        }

        function confirmClearAll() {
            closeClearAllModal();
            fetch('{{ route("requestor.notifications.clear-all") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to clear notifications');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        }

        // Mark all as read
        function markAllAsRead() {
            fetch('{{ route("requestor.notifications.mark-all-read") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to mark notifications as read');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        }
    </script>
</x-app-layout>
