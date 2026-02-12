<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg sm:text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Cancellation Requests') }}
        </h2>
    </x-slot>

    <div class="py-4 sm:py-8">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
            <!-- Compact Single Card Layout -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <!-- Card Header with Tabs -->
                <div class="px-4 sm:px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-gray-100">
                            @if(($status ?? 'pending') === 'completed') Completed @else Pending @endif Cancellation Requests
                        </h3>
                        <!-- Status Tabs -->
                        <div class="flex rounded-lg bg-gray-100 dark:bg-gray-700 p-1">
                            <a href="{{ route('staff.cancellations.index', ['status' => 'pending']) }}"
                               class="px-3 py-1.5 rounded-md text-xs sm:text-sm font-medium transition-all duration-200 {{ ($status ?? 'pending') === 'pending' ? 'bg-emerald-500 text-white shadow-sm' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100' }}">
                                Pending
                            </a>
                            <a href="{{ route('staff.cancellations.index', ['status' => 'completed']) }}"
                               class="px-3 py-1.5 rounded-md text-xs sm:text-sm font-medium transition-all duration-200 {{ ($status ?? 'pending') === 'completed' ? 'bg-emerald-500 text-white shadow-sm' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100' }}">
                                Completed
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Cancellations List -->
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($cancellations as $c)
                        <!-- Desktop Layout (Responsive in mobile) -->
                        <div class="p-4 sm:p-6 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <!-- Main Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="space-y-3">
                                        <!-- Header with ID and Timeline -->
                                        <div>
                                            <div class="flex items-center gap-2 flex-wrap mb-2">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                                    #{{ $c->cancellation_id }}
                                                </span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $c->created_at->diffForHumans() }}</span>
                                            </div>
                                            <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">
                                                {{ $c->reservation->activity_name ?? 'Reservation #'.$c->reservation_id }}
                                            </h4>
                                        </div>

                                        <!-- Details Grid - Mobile optimized -->
                                        <div class="grid grid-cols-2 gap-2 sm:gap-3 sm:flex sm:flex-wrap sm:items-center">
                                            <!-- Requestor -->
                                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded p-2 sm:p-0 sm:bg-transparent">
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5 sm:mb-0">Requestor</p>
                                                <p class="text-xs sm:text-sm font-medium text-gray-900 dark:text-gray-100 truncate sm:ml-2">
                                                    {{ $c->requestor->name }}
                                                </p>
                                            </div>

                                            <!-- Date -->
                                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded p-2 sm:p-0 sm:bg-transparent">
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5 sm:mb-0">Scheduled</p>
                                                <p class="text-xs sm:text-sm font-medium text-gray-900 dark:text-gray-100 sm:ml-2">
                                                    {{ optional($c->reservation->schedule_date)->format('M d, Y') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Confirmations Status & Action - Mobile optimized -->
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4">
                                    <!-- Confirmation Badges -->
                                    <div class="grid grid-cols-2 gap-2 sm:flex sm:gap-2 flex-shrink-0">
                                        <span class="px-2 py-1 rounded text-xs font-medium text-center sm:text-left {{ $c->isStaffConfirmed() ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }}">
                                            {{ $c->isStaffConfirmed() ? '✓' : '○' }} Staff
                                        </span>
                                        <span class="px-2 py-1 rounded text-xs font-medium text-center sm:text-left {{ $c->isAdminConfirmed() ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }}">
                                            {{ $c->isAdminConfirmed() ? '✓' : '○' }} Admin
                                        </span>
                                        <span class="px-2 py-1 rounded text-xs font-medium text-center sm:text-left {{ $c->isAdviserConfirmed() ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }}">
                                            {{ $c->isAdviserConfirmed() ? '✓' : '○' }} Adviser
                                        </span>
                                        <span class="px-2 py-1 rounded text-xs font-medium text-center sm:text-left {{ $c->isPriestConfirmed() ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }}">
                                            {{ $c->isPriestConfirmed() ? '✓' : '○' }} Priest
                                        </span>
                                    </div>

                                    <!-- Action Button -->
                                    <a href="{{ route('staff.cancellations.show', $c->cancellation_id) }}"
                                       class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2.5 sm:py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-semibold sm:font-medium rounded-lg transition-colors duration-150 shadow-sm flex-shrink-0">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        Review
                                    </a>
                                </div>
                            </div>
                        </div>
                            </div>
                        </div>
                    @empty
                        <!-- Empty State -->
                        <div class="p-8 sm:p-12 text-center">
                            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-1">No records found</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                @if(($status ?? 'pending') === 'completed')
                                    No completed cancellation requests yet.
                                @else
                                    No pending cancellation requests at this time.
                                @endif
                            </p>
                        </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                @if($cancellations->hasPages())
                <div class="px-4 sm:px-6 py-3 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700">
                    {{ $cancellations->withQueryString()->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
