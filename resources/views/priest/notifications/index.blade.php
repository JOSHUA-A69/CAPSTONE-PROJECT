<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('My Notifications') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($notifications->isEmpty())
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No notifications</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">You're all caught up!</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($notifications as $notification)
                                <div class="rounded-lg p-4 shadow-md transition-all {{ $notification->isUnread() ? 'bg-blue-100 dark:bg-blue-900/40 border-2 border-blue-200 dark:border-blue-700' : 'bg-gray-50 dark:bg-gray-700/30 border border-gray-200 dark:border-gray-600' }}">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-2">
                                                @php
                                                    $typeColor = match($notification->type) {
                                                        'Assignment' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
                                                        'Update' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                                                        'System Alert' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                                                        default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                                    };
                                                @endphp
                                                <span class="text-xs px-2 py-1 rounded-full font-medium {{ $typeColor }}">
                                                    {{ $notification->type }}
                                                </span>
                                                @if($notification->isUnread())
                                                    <span class="inline-flex items-center">
                                                        <span class="w-2 h-2 bg-blue-600 dark:bg-blue-400 rounded-full animate-pulse"></span>
                                                        <span class="ml-1 text-xs text-blue-600 dark:text-blue-400 font-medium">New</span>
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="text-gray-900 dark:text-gray-100 mb-2 {{ $notification->isUnread() ? 'font-semibold' : 'font-normal' }}">{!! $notification->message !!}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $notification->sent_at->diffForHumans() }}</p>
                                        </div>
                                        <div class="ml-4">
                                            @if($notification->type === 'Assignment' && $notification->reservation_id)
                                                <a href="{{ route('priest.notifications.assignment', $notification->notification_id) }}"
                                                   class="inline-flex items-center px-3 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                                    View Details
                                                </a>
                                            @elseif($notification->reservation_id)
                                                <a href="{{ route('priest.reservations.show', $notification->reservation_id) }}"
                                                   class="inline-flex items-center px-3 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                                    View Reservation
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6">
                            {{ $notifications->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
