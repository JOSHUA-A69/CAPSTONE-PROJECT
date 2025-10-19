<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Notifications') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Mark All as Read Button -->
            <div class="mb-4 flex justify-end">
                <button onclick="markAllAsRead()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                    Mark All as Read
                </button>
            </div>

            <!-- Notifications List -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-3">
                    @forelse($notifications as $notification)
                        <div class="rounded-lg p-4 shadow-md transition-all {{ $notification->isUnread() ? 'bg-blue-100 dark:bg-blue-900/40 border-2 border-blue-200 dark:border-blue-700' : 'bg-gray-50 dark:bg-gray-700/30 border border-gray-200 dark:border-gray-600' }}">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center mb-2">
                                        @if($notification->type === 'Priest Declined')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                </svg>
                                                {{ $notification->type }}
                                            </span>
                                        @elseif($notification->type === 'Update')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                                {{ $notification->type }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-200 text-gray-800 dark:bg-gray-600 dark:text-gray-300">
                                                {{ $notification->type }}
                                            </span>
                                        @endif

                                        @if($notification->isUnread())
                                            <span class="ml-2 w-2 h-2 bg-blue-600 dark:bg-blue-400 rounded-full animate-pulse"></span>
                                        @endif
                                    </div>

                                    <p class="text-sm text-gray-900 dark:text-gray-100 {{ $notification->isUnread() ? 'font-semibold' : 'font-normal' }} mb-1">
                                        {!! $notification->message !!}
                                    </p>

                                    @if($notification->reservation)
                                        <p class="text-xs text-gray-600 dark:text-gray-400 {{ $notification->isUnread() ? 'font-medium' : '' }}">
                                            Reservation #{{ $notification->reservation_id }} -
                                            {{ $notification->reservation->schedule_date->format('M d, Y h:i A') }}
                                        </p>
                                    @endif

                                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                        {{ $notification->sent_at->diffForHumans() }}
                                    </p>
                                </div>

                                <div class="ml-4 flex-shrink-0">
                                    @if($notification->type === 'Priest Declined')
                                        <a href="{{ route('admin.notifications.priest-declined', $notification->notification_id) }}" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none transition">
                                            View Details
                                        </a>
                                    @elseif($notification->reservation_id)
                                        <a href="{{ route('admin.reservations.show', $notification->reservation_id) }}" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none transition">
                                            View
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            <p class="mt-2">No notifications yet</p>
                        </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                @if($notifications->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        {{ $notifications->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        // Trigger notification count update when page loads
        window.addEventListener('DOMContentLoaded', function() {
            // Dispatch custom event to update notification count
            if (window.dispatchEvent) {
                window.dispatchEvent(new Event('notification-update'));
            }

            // Also manually update if Alpine is available
            setTimeout(function() {
                const bellButton = parent.document.querySelector('[x-data]');
                if (bellButton && bellButton.__x) {
                    bellButton.__x.$data.updateCount();
                }
            }, 500);
        });

        function markAllAsRead() {
            fetch('{{ route('admin.notifications.mark-all-read') }}', {
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
                }
            });
        }
    </script>
</x-app-layout>
