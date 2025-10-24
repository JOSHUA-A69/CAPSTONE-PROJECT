<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Cancellation Request') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Success/Error Messages -->
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

            @if(session('error'))
            <div class="mb-6 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-4 rounded-r-lg">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    <p class="text-sm font-medium text-red-800 dark:text-red-300">{{ session('error') }}</p>
                </div>
            </div>
            @endif

            @if(session('info'))
            <div class="mb-6 bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 p-4 rounded-r-lg">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-blue-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    <p class="text-sm font-medium text-blue-800 dark:text-blue-300">{{ session('info') }}</p>
                </div>
            </div>
            @endif

            <!-- Alert Banner -->
            <div class="mb-6 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-4 rounded-r-lg">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <h3 class="text-sm font-medium text-red-800 dark:text-red-300">Cancel Reservation</h3>
                        <p class="text-sm text-red-700 dark:text-red-400 mt-1">
                            Review the reservation details and confirm cancellation if appropriate.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Cancellation Details Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Requestor & Timing</h3>
                    
                    <div class="space-y-4">
                        <!-- Requestor Info -->
                        <div class="flex items-start border-b dark:border-gray-700 pb-4">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400 w-1/3">Requestor:</span>
                            <span class="text-sm text-gray-900 dark:text-gray-100 w-2/3">
                                {{ $reservation->user->name ?? ($reservation->user->email ?? '—') }}
                            </span>
                        </div>

                        <!-- Requested At -->
                        <div class="flex items-start border-b dark:border-gray-700 pb-4">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400 w-1/3">Created At:</span>
                            <span class="text-sm text-gray-900 dark:text-gray-100 w-2/3">
                                {{ optional($reservation->created_at)->format('M d, Y h:i A') }}
                                <span class="text-gray-500 dark:text-gray-400">({{ optional($reservation->created_at)->diffForHumans() }})</span>
                            </span>
                        </div>

                        
                    </div>
                </div>
            </div>

            <!-- Reservation Details Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Reservation Details</h3>
                    
                    <div class="space-y-4">
                        <!-- Service Type -->
                        <div class="flex items-start border-b dark:border-gray-700 pb-4">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400 w-1/3">Service Type:</span>
                            <span class="text-sm text-gray-900 dark:text-gray-100 w-2/3">
                                {{ $reservation->service->service_name ?? ($reservation->service_type ?? 'Reservation') }}
                            </span>
                        </div>

                        <!-- Schedule -->
                        <div class="flex items-start border-b dark:border-gray-700 pb-4">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400 w-1/3">Schedule:</span>
                            <span class="text-sm text-gray-900 dark:text-gray-100 w-2/3">
                                {{ \Carbon\Carbon::parse($reservation->schedule_date)->format('F d, Y') }}
                                at {{ \Carbon\Carbon::parse($reservation->schedule_date)->format('h:i A') }}
                            </span>
                        </div>

                        <!-- Organization -->
                        <div class="flex items-start border-b dark:border-gray-700 pb-4">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400 w-1/3">Organization:</span>
                            <span class="text-sm text-gray-900 dark:text-gray-100 w-2/3">
                                {{ $reservation->organization->name ?? 'N/A' }}
                            </span>
                        </div>

                        <!-- Assigned Priest -->
                        @if($reservation->officiant)
                        <div class="flex items-start">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400 w-1/3">Assigned Priest:</span>
                            <span class="text-sm text-gray-900 dark:text-gray-100 w-2/3">
                                {{ $reservation->officiant->name }}
                            </span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            

            <!-- Actions -->
            @if(($reservation->status ?? null) !== 'cancelled')
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('adviser.cancellations.confirm', $reservation->reservation_id) }}" method="POST">
                        @csrf
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Confirm Cancellation</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    Confirming will mark this reservation as cancelled and notify relevant parties.
                                </p>
                            </div>
                            <button type="submit" 
                                    class="ml-4 px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg shadow-md transition-colors duration-200">
                                Confirm Cancellation
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @else
            <div class="bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 p-4 rounded-r-lg">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <h3 class="text-sm font-medium text-green-800 dark:text-green-300">Already Cancelled</h3>
                        <p class="text-sm text-green-700 dark:text-green-400 mt-1">This reservation is already marked as cancelled.</p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Back Button -->
            <div class="mt-6">
                <a href="{{ route('adviser.notifications.index') }}" 
                   class="inline-flex items-center text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Notifications
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
