<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Declined Services History
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Back to Reservations -->
            <div class="mb-6">
                <a href="{{ route('priest.reservations.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back to My Assignments
                </a>
            </div>

            <!-- Declined Services List -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    @if($declines->isEmpty())
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No declined services</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                You have not declined any service assignments.
                            </p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($declines as $decline)
                                <div class="border dark:border-gray-700 rounded-lg p-4 bg-red-50 dark:bg-red-900/10">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3 mb-2">
                                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                                    {{ $decline->reservation_activity_name }}
                                                </h3>
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-200">
                                                    Declined
                                                </span>
                                            </div>

                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm text-gray-600 dark:text-gray-400 mb-3">
                                                <div class="flex items-center">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                    <span class="font-medium">Original Schedule:</span>&nbsp;
                                                    {{ \Carbon\Carbon::parse($decline->reservation_schedule_date)->format('M d, Y - g:i A') }}
                                                </div>

                                                <div class="flex items-center">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    </svg>
                                                    {{ $decline->reservation_venue }}
                                                </div>

                                                <div class="flex items-center">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <span class="font-medium">Declined:</span>&nbsp;
                                                    {{ $decline->declined_at->format('M d, Y g:i A') }}
                                                </div>
                                            </div>

                                            <!-- Reason -->
                                            <div class="mt-3 p-3 bg-white dark:bg-gray-800 rounded border dark:border-gray-700">
                                                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1">Reason for Declining:</p>
                                                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $decline->reason }}</p>
                                            </div>
                                        </div>

                                        <!-- View Reservation Link (if still exists) -->
                                        @if($decline->reservation)
                                            <a href="{{ route('priest.reservations.show', $decline->reservation_id) }}"
                                               class="ml-4 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors whitespace-nowrap">
                                                View Reservation
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            {{ $declines->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Statistics Card -->
            <div class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Summary</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="p-4 bg-red-50 dark:bg-red-900/10 rounded-lg">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Total Declined</p>
                            <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $declines->total() }}</p>
                        </div>

                        <div class="p-4 bg-blue-50 dark:bg-blue-900/10 rounded-lg">
                            <p class="text-sm text-gray-600 dark:text-gray-400">This Month</p>
                            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                {{ $declines->where('declined_at', '>=', now()->startOfMonth())->count() }}
                            </p>
                        </div>

                        <div class="p-4 bg-purple-50 dark:bg-purple-900/10 rounded-lg">
                            <p class="text-sm text-gray-600 dark:text-gray-400">This Year</p>
                            <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                                {{ $declines->where('declined_at', '>=', now()->startOfYear())->count() }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
