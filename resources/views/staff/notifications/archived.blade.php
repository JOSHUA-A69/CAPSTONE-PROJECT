<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col space-y-3 sm:flex-row sm:justify-between sm:items-center sm:space-y-0">
            <h2 class="font-semibold text-lg sm:text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Archived Notifications') }}
            </h2>
            <a href="{{ route('staff.notifications.index') }}" class="inline-flex items-center gap-1.5 text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Notifications
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($notifications->isEmpty())
                        <div class="text-center py-16 px-4">
                            <div class="bg-gray-100 dark:bg-gray-700 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                                <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                                </svg>
                            </div>
                            <h3 class="text-base sm:text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No archived notifications</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto leading-relaxed">You haven't archived any notifications yet. Archived notifications will appear here for your reference.</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($notifications as $notification)
                            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 sm:p-5 shadow-sm hover:shadow-md transition-shadow" id="archived-notification-{{ $notification->notification_id }}">
                                <div class="space-y-3">
                                    <!-- Header Section -->
                                    <div class="flex flex-col space-y-2 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 w-fit">
                                            {{ $notification->type }}
                                        </span>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 italic">
                                            Archived {{ $notification->archived_at->diffForHumans() }}
                                        </p>
                                    </div>

                                    <!-- Message Section -->
                                    <div class="space-y-2">
                                        <p class="text-sm sm:text-base text-gray-900 dark:text-gray-100 leading-relaxed">
                                            {!! $notification->message !!}
                                        </p>

                                        @if($notification->reservation)
                                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                                <p class="text-xs text-gray-600 dark:text-gray-400">
                                                    <span class="font-medium">Reservation #{{ $notification->reservation_id }}</span>
                                                    <span class="block sm:inline sm:ml-2">
                                                        {{ $notification->reservation->schedule_date->format('M d, Y h:i A') }}
                                                    </span>
                                                </p>
                                            </div>
                                        @endif

                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            <span class="font-medium">Sent:</span> {{ optional($notification->sent_at)->diffForHumans() ?? optional($notification->created_at)->diffForHumans() ?? 'N/A' }}
                                        </p>
                                    </div>
                                        
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1 italic">
                                            Archived {{ $notification->archived_at->diffForHumans() }}
                                        </p>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="flex flex-col space-y-2 sm:flex-row sm:space-y-0 sm:space-x-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                                        <button onclick="restoreNotification({{ $notification->notification_id }})" 
                                                class="flex items-center justify-center gap-2 px-4 py-2.5 bg-green-50 hover:bg-green-100 text-green-700 rounded-lg transition-colors text-sm font-medium w-full sm:w-auto"
                                                title="Restore this notification">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                            </svg>
                                            <span>Restore</span>
                                        </button>
                                        
                                        @if($notification->reservation_id)
                                        <a href="{{ route('staff.reservations.show', $notification->reservation_id) }}" 
                                           class="flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg transition-colors text-sm font-medium w-full sm:w-auto"
                                           title="View reservation">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            <span>View Reservation</span>
                                        </a>
                                        @endif
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
        function restoreNotification(notificationId) {
            if (!confirm('Restore this notification?')) return;

            fetch(`/staff/notifications/${notificationId}/restore`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const notificationItem = document.getElementById(`archived-notification-${notificationId}`);
                    notificationItem.style.transition = 'opacity 0.3s, transform 0.3s';
                    notificationItem.style.opacity = '0';
                    notificationItem.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        notificationItem.remove();
                        const remainingItems = document.querySelectorAll('[id^="archived-notification-"]');
                        if (remainingItems.length === 0) location.reload();
                    }, 300);
                } else {
                    alert('Failed to restore notification');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        }
    </script>
</x-app-layout>
