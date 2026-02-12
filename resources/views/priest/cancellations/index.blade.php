<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Cancellation Requests') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Flash Messages -->
            @if(session('success'))
            <div class="mb-6 bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 p-4 rounded-r-lg">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <p class="text-sm font-medium text-green-800 dark:text-green-300">{{ session('success') }}</p>
                </div>
            </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if($cancellations->isEmpty())
                        <div class="text-center py-8">
                            <svg class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-lg text-gray-500 dark:text-gray-400">No pending cancellation requests found.</p>
                        </div>
                    @else
                        <div class="space-y-4 sm:space-y-5">
                            @foreach($cancellations as $cancellation)
                                <div class="border dark:border-gray-700 rounded-xl overflow-hidden bg-red-50 dark:bg-red-900/10 transition-all hover:shadow-md">
                                    <!-- Card Content -->
                                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-0">
                                        <!-- Main Content Section -->
                                        <div class="flex-1 p-4 sm:p-5 lg:p-6">
                                            <!-- Service and Status Badge -->
                                            <div class="flex flex-col sm:flex-row sm:items-center sm:gap-3 mb-4 gap-2">
                                                <h3 class="text-base sm:text-lg font-bold text-gray-900 dark:text-gray-100 truncate">
                                                    {{ $cancellation->reservation->activity_name ?? $cancellation->reservation->service->service_name }}
                                                </h3>
                                                <span class="px-3 py-1 text-xs font-semibold rounded-full
                                                    {{ $cancellation->isPriestConfirmed() ? 'bg-green-200 dark:bg-green-900/60 text-green-800 dark:text-green-200' : 'bg-yellow-200 dark:bg-yellow-900/60 text-yellow-800 dark:text-yellow-200' }}
                                                    w-fit">
                                                    {{ $cancellation->isPriestConfirmed() ? 'Confirmed' : 'Pending' }}
                                                </span>
                                            </div>

                                            <!-- Request Details Grid -->
                                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 mb-4">
                                                <!-- Requestor -->
                                                <div class="bg-white dark:bg-gray-800/50 rounded-lg p-3 sm:p-4">
                                                    <div class="flex items-start gap-2 sm:gap-3">
                                                        <svg class="w-5 h-5 sm:w-5 sm:h-5 text-gray-500 dark:text-gray-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                        </svg>
                                                        <div class="min-w-0 flex-1">
                                                            <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase tracking-wide">Requestor</p>
                                                            <p class="text-sm sm:text-base font-semibold text-gray-900 dark:text-gray-100 truncate">
                                                                {{ $cancellation->requestor->name }}
                                                            </p>
                                                            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 truncate">
                                                                {{ $cancellation->requestor->email }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Organization -->
                                                <div class="bg-white dark:bg-gray-800/50 rounded-lg p-3 sm:p-4">
                                                    <div class="flex items-start gap-2 sm:gap-3">
                                                        <svg class="w-5 h-5 sm:w-5 sm:h-5 text-gray-500 dark:text-gray-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"></path>
                                                        </svg>
                                                        <div class="min-w-0 flex-1">
                                                            <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase tracking-wide">Organization</p>
                                                            <p class="text-sm sm:text-base font-semibold text-gray-900 dark:text-gray-100 truncate">
                                                                {{ $cancellation->reservation->organization->org_name ?? 'N/A' }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Date Requested -->
                                                <div class="bg-white dark:bg-gray-800/50 rounded-lg p-3 sm:p-4">
                                                    <div class="flex items-start gap-2 sm:gap-3">
                                                        <svg class="w-5 h-5 sm:w-5 sm:h-5 text-gray-500 dark:text-gray-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                        <div class="min-w-0 flex-1">
                                                            <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase tracking-wide">Requested</p>
                                                            <p class="text-sm sm:text-base font-semibold text-gray-900 dark:text-gray-100">
                                                                {{ $cancellation->created_at->format('M d, Y') }}
                                                            </p>
                                                            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                                                                {{ $cancellation->created_at->format('g:i A') }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Reason Section -->
                                            <div class="bg-white dark:bg-gray-800/50 rounded-lg p-3 sm:p-4 mb-4">
                                                <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold uppercase tracking-wide mb-2">Cancellation Reason</p>
                                                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">{{ $cancellation->reason }}</p>
                                            </div>

                                            <!-- Status Information -->
                                            @if($cancellation->isPriestConfirmed())
                                                <div class="p-3 sm:p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                                                    <div class="flex gap-2 sm:gap-3">
                                                        <svg class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                        </svg>
                                                        <div class="flex-1 min-w-0">
                                                            <p class="text-sm font-semibold text-green-800 dark:text-green-300">
                                                                You confirmed this cancellation
                                                            </p>
                                                            <p class="text-xs text-green-700 dark:text-green-400 mt-1">
                                                                Confirmed on {{ $cancellation->priest_confirmed_at->format('M d, Y') }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="p-3 sm:p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                                                    <div class="flex gap-2 sm:gap-3">
                                                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                        </svg>
                                                        <div class="flex-1 min-w-0">
                                                            <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">
                                                                Awaiting your confirmation
                                                            </p>
                                                            <p class="text-xs text-amber-700 dark:text-amber-400 mt-1">
                                                                Please review and confirm within 1 minute.
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Action Button Section -->
                                        <div class="border-t lg:border-t-0 lg:border-l dark:border-gray-700 p-4 sm:p-5 lg:p-6 flex flex-col gap-2 sm:gap-3 w-full lg:w-auto lg:min-w-max">
                                            <a href="{{ route('priest.cancellations.show', $cancellation->cancellation_id) }}"
                                               class="flex items-center justify-center gap-2 px-4 py-2.5 sm:py-3 bg-blue-600 hover:bg-blue-700 text-white text-sm sm:text-base font-semibold rounded-lg transition-all duration-200 transform hover:scale-105 whitespace-nowrap w-full lg:w-auto">
                                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                <span class="hidden sm:inline">Review & Respond</span>
                                                <span class="sm:hidden">Review</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <!-- Pagination -->
                        <div class="mt-6">
                            {{ $cancellations->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
