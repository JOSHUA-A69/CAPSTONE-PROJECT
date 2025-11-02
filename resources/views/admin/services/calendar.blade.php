<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    🗓️ My Services Calendar
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    A quick view of your upcoming and confirmed services
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.services.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                    </svg>
                    Back to Services
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="card">
                <div class="card-body">
                    @if(empty($events) || collect($events)->isEmpty())
                        <div class="text-center py-16">
                            <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded-full mb-4">
                                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">No upcoming services</h3>
                            <p class="text-gray-600 dark:text-gray-400 max-w-md mx-auto">
                                You're all caught up. When you have scheduled services, they will appear here.
                            </p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($events as $event)
                                <a href="{{ $event['url'] }}"
                                   class="block p-5 border dark:border-gray-700 rounded-xl hover:bg-indigo-50 dark:hover:bg-gray-700/40 transition-colors">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <div class="text-sm text-gray-500">#{{ $event['id'] }}</div>
                                            <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $event['title'] }}</div>
                                            <div class="text-sm text-gray-600 dark:text-gray-300">{{ \Carbon\Carbon::parse($event['start'])->format('M d, Y g:i A') }}</div>
                                        </div>
                                        <div class="w-3 h-3 rounded-full" style="background-color: {{ $event['backgroundColor'] }}"></div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
