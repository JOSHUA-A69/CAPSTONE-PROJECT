<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
            <div>
                <h2 class="font-semibold text-lg sm:text-xl text-gray-800 dark:text-gray-200 leading-tight">
                     Declined Services
                </h2>
                <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Services you declined or were recorded as declined will appear here.
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.services.index') }}"
                   class="inline-flex items-center px-3 sm:px-4 py-1.5 sm:py-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-xs sm:text-sm font-semibold rounded-lg transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-105 whitespace-nowrap">
                    <span class="hidden sm:inline">Back to Services</span>
                    <span class="sm:hidden">Back</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-4 sm:py-8 lg:py-12">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
            <div class="card">
                <div class="card-body">
                    @if($reservations->isEmpty())
                        <div class="text-center py-10 sm:py-16">
                            <div class="inline-flex items-center justify-center w-16 h-16 sm:w-20 sm:h-20 bg-gradient-to-br from-red-50 to-rose-50 dark:from-red-900/20 dark:to-rose-900/20 rounded-full mb-3 sm:mb-4">
                                <svg class="w-8 h-8 sm:w-10 sm:h-10 text-red-400 dark:text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </div>
                            <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1 sm:mb-2">No declined services</h3>
                            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 max-w-md mx-auto px-2">
                                You haven't declined any service assignments.
                            </p>
                        </div>
                    @else
                        <div class="space-y-3 sm:space-y-4">
                            @foreach($reservations as $reservation)
                                <div class="border dark:border-gray-700 rounded-lg sm:rounded-xl p-3 sm:p-5 bg-gradient-to-r from-white to-gray-50 dark:from-gray-800 dark:to-gray-800/50 hover:shadow-md transition-shadow duration-200">
                                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 sm:gap-4 mb-3 sm:mb-4">
                                        <div class="flex-1 min-w-0">
                                            <h3 class="text-sm sm:text-lg font-bold text-gray-900 dark:text-gray-100 mb-1 sm:mb-2 line-clamp-2">
                                                {{ $reservation->activity_name ?? $reservation->service->service_name }}
                                            </h3>
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="inline-flex items-center px-2.5 sm:px-3 py-1 sm:py-1.5 text-xs font-bold rounded-full bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200 shadow-sm">
                                                    Declined
                                                </span>
                                                @php
                                                    $myLatestDecline = optional($reservation->declines
                                                        ->where('priest_id', auth()->id())
                                                        ->sortByDesc('declined_at')
                                                        ->first());
                                                @endphp
                                                @if($myLatestDecline && $myLatestDecline->reason)
                                                    <span class="inline-flex items-center px-2 sm:px-2.5 py-0.5 sm:py-1 text-xs font-medium rounded-full bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 line-clamp-1">
                                                        <svg class="w-3 h-3 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                        <span class="hidden sm:inline">{{ \Illuminate\Support\Str::limit($myLatestDecline->reason, 60) }}</span>
                                                        <span class="sm:hidden">{{ \Illuminate\Support\Str::limit($myLatestDecline->reason, 20) }}</span>
                                                    </span>
                                                @endif
                                                @if($reservation->organization)
                                                    <span class="inline-flex items-center px-2 sm:px-2.5 py-0.5 sm:py-1 text-xs font-medium rounded-full bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 line-clamp-1">
                                                        <svg class="w-3 h-3 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                        </svg>
                                                        <span class="hidden sm:inline">{{ $reservation->organization->org_name }}</span>
                                                        <span class="sm:hidden">{{ \Illuminate\Support\Str::limit($reservation->organization->org_name, 15) }}</span>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <a href="{{ route('admin.services.show', $reservation->reservation_id) }}"
                                           class="inline-flex items-center justify-center px-3 py-1.5 sm:px-4 sm:py-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-xs sm:text-sm font-semibold rounded-md sm:rounded-lg transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-105 whitespace-nowrap flex-shrink-0">
                                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            <span class="hidden sm:inline">View Details</span>
                                            <span class="sm:hidden">View</span>
                                        </a>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 text-xs sm:text-sm">
                                        <div class="flex items-center text-gray-700 dark:text-gray-300">
                                            <div class="flex-shrink-0 w-7 h-7 sm:w-8 sm:h-8 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center mr-2 sm:mr-3">
                                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $reservation->schedule_date->format('M d, Y') }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ date('g:i A', strtotime($reservation->schedule_time)) }}</div>
                                            </div>
                                        </div>

                                        @if($myLatestDecline)
                                        <div class="flex items-center text-gray-700 dark:text-gray-300">
                                            <div class="flex-shrink-0 w-7 h-7 sm:w-8 sm:h-8 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center mr-2 sm:mr-3">
                                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-900 dark:text-gray-100 line-clamp-1">{{ \Illuminate\Support\Str::limit($myLatestDecline->reason ?: 'No reason provided', 25) }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">Declined on {{ $myLatestDecline->declined_at?->format('M d, Y') }}</div>
                                            </div>
                                        </div>
                                        @endif

                                        <div class="flex items-center text-gray-700 dark:text-gray-300">
                                            <div class="flex-shrink-0 w-7 h-7 sm:w-8 sm:h-8 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center mr-2 sm:mr-3">
                                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-900 dark:text-gray-100 line-clamp-1">
                                                    @if($reservation->custom_venue_name)
                                                        {{ \Illuminate\Support\Str::limit($reservation->custom_venue_name, 20) }}
                                                    @else
                                                        {{ \Illuminate\Support\Str::limit($reservation->venue->name, 20) }}
                                                    @endif
                                                </div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">Venue</div>
                                            </div>
                                        </div>

                                        <div class="flex items-center text-gray-700 dark:text-gray-300">
                                            <div class="flex-shrink-0 w-7 h-7 sm:w-8 sm:h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center mr-2 sm:mr-3">
                                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-900 dark:text-gray-100 line-clamp-1">{{ $reservation->user?->full_name ?? 'Unknown User' }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">Requestor</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6">
                            {{ $reservations->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
