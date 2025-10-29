<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Notifications') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @if($notifications->isEmpty())
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-12 text-center">
                    <svg class="mx-auto h-24 w-24 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">No notifications yet</h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        You don't have any notifications at this time.
                    </p>
                </div>
            </div>
            @else
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-3">
                    @foreach($notifications as $notification)
                    <a href="{{ route('adviser.notifications.show', $notification->notification_id) }}"
                       class="block rounded-lg p-4 shadow-md transition-all {{ $notification->isUnread() ? 'bg-blue-100 dark:bg-blue-900/40 border-2 border-blue-200 dark:border-blue-700' : 'bg-gray-50 dark:bg-gray-700/30 border border-gray-200 dark:border-gray-600' }}">
                        <div class="flex items-start gap-4">
                            <!-- Icon -->
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-full {{ $notification->type === 'Cancellation Request' ? 'bg-red-500' : ($notification->type === 'Update' ? 'bg-green-500' : 'bg-blue-500') }} flex items-center justify-center text-white">
                                    @if($notification->type === 'Cancellation Request')
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                    </svg>
                                    @else
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"></path>
                                    </svg>
                                    @endif
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm text-gray-900 dark:text-gray-100 {{ $notification->isUnread() ? 'font-semibold' : 'font-medium' }}">
                                        {!! $notification->message !!}
                                    </p>
                                    @if($notification->isUnread())
                                    <span class="ml-2 flex-shrink-0 inline-block w-2 h-2 bg-blue-600 dark:bg-blue-400 rounded-full animate-pulse"></span>
                                    @endif
                                </div>
                                <div class="mt-1 flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                                    <span>{{ optional($notification->sent_at)->diffForHumans() ?? optional($notification->created_at)->diffForHumans() ?? '' }}</span>
                                    <span class="px-2 py-1 rounded-full {{ $notification->type === 'Cancellation Request' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                                        {{ $notification->type }}
                                    </span>
                                </div>
                            </div>

                            <!-- Arrow -->
                            <div class="flex-shrink-0">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $notifications->links() }}
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
