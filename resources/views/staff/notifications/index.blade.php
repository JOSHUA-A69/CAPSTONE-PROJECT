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
                <form method="POST" action="{{ route('staff.notifications.read-all') }}" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all duration-200 shadow-sm hover:shadow-md">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Mark All as Read
                    </button>
                </form>
            </div>

            <!-- Success Message -->
            @if(session('status') === 'all-notifications-read')
                <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 rounded-lg">
                    <p class="text-sm text-green-800 dark:text-green-200">All notifications marked as read!</p>
                </div>
            @endif

            <!-- Notifications List -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-3">
                    @forelse($notifications as $notification)
                        <div class="rounded-lg p-4 shadow-md transition-all {{ $notification->isUnread() ? 'bg-blue-50 dark:bg-blue-900/20 border-2 border-blue-300 dark:border-blue-600' : 'bg-gray-50 dark:bg-gray-700/30 border border-gray-200 dark:border-gray-600' }}">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center mb-2">
                                        @if(str_contains(strtolower($notification->message), 'declined') || str_contains(strtolower($notification->message), 'cancelled'))
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                                </svg>
                                                {{ $notification->type }}
                                            </span>
                                        @elseif(str_contains(strtolower($notification->message), 'approved') || str_contains(strtolower($notification->message), 'confirmed'))
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                </svg>
                                                {{ $notification->type }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                                {{ $notification->type }}
                                            </span>
                                        @endif

                                        @if($notification->isUnread())
                                            <span class="ml-2 flex items-center">
                                                <span class="w-2 h-2 bg-blue-600 dark:bg-blue-400 rounded-full animate-pulse"></span>
                                                <span class="ml-1 text-xs font-semibold text-blue-600 dark:text-blue-400">NEW</span>
                                            </span>
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
                                        {{ optional($notification->sent_at)->diffForHumans() ?? optional($notification->created_at)->diffForHumans() ?? '' }}
                                    </p>
                                </div>

                                <div class="ml-4 flex-shrink-0">
                                    @if($notification->reservation_id)
                                        @if($notification->isUnread())
                                            <form method="POST" action="{{ route('staff.notifications.read', $notification->notification_id) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-all duration-200">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                    Mark Read
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            <p class="mt-4 text-lg font-medium">No notifications yet</p>
                            <p class="mt-1 text-sm">You're all caught up!</p>
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
        });
    </script>
</x-app-layout>
