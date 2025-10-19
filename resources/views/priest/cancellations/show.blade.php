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
                        <h3 class="text-sm font-medium text-red-800 dark:text-red-300">Cancellation Request Pending</h3>
                        <p class="text-sm text-red-700 dark:text-red-400 mt-1">
                            A requestor has requested to cancel their reservation. Please review and confirm within 1 minute.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Cancellation Details Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Cancellation Details</h3>
                    
                    <div class="space-y-4">
                        <!-- Requestor Info -->
                        <div class="flex items-start border-b dark:border-gray-700 pb-4">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400 w-1/3">Requestor:</span>
                            <span class="text-sm text-gray-900 dark:text-gray-100 w-2/3">
                                {{ $cancellation->requestor->name }}
                                <span class="text-gray-500 dark:text-gray-400">({{ $cancellation->requestor->email }})</span>
                            </span>
                        </div>

                        <!-- Requested At -->
                        <div class="flex items-start border-b dark:border-gray-700 pb-4">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400 w-1/3">Requested At:</span>
                            <span class="text-sm text-gray-900 dark:text-gray-100 w-2/3">
                                {{ $cancellation->created_at->format('M d, Y h:i A') }}
                                <span class="text-gray-500 dark:text-gray-400">({{ $cancellation->created_at->diffForHumans() }})</span>
                            </span>
                        </div>

                        <!-- Reason -->
                        <div class="flex items-start border-b dark:border-gray-700 pb-4">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400 w-1/3">Reason:</span>
                            <span class="text-sm text-gray-900 dark:text-gray-100 w-2/3">
                                {{ $cancellation->reason }}
                            </span>
                        </div>

                        <!-- Status -->
                        <div class="flex items-start">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400 w-1/3">Status:</span>
                            <span class="text-sm">
                                <span class="px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                                    {{ ucwords(str_replace('_', ' ', $cancellation->status)) }}
                                </span>
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
                                {{ $cancellation->reservation->service_type }}
                            </span>
                        </div>

                        <!-- Schedule -->
                        <div class="flex items-start border-b dark:border-gray-700 pb-4">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400 w-1/3">Schedule:</span>
                            <span class="text-sm text-gray-900 dark:text-gray-100 w-2/3">
                                {{ \Carbon\Carbon::parse($cancellation->reservation->schedule_date)->format('F d, Y') }}
                                at {{ \Carbon\Carbon::parse($cancellation->reservation->schedule_time)->format('h:i A') }}
                            </span>
                        </div>

                        <!-- Organization -->
                        <div class="flex items-start border-b dark:border-gray-700 pb-4">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400 w-1/3">Organization:</span>
                            <span class="text-sm text-gray-900 dark:text-gray-100 w-2/3">
                                {{ $cancellation->reservation->organization->name ?? 'N/A' }}
                            </span>
                        </div>

                        <!-- Your Assignment -->
                        <div class="flex items-start bg-blue-50 dark:bg-blue-900/20 -mx-3 px-3 py-2 rounded">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400 w-1/3">Your Assignment:</span>
                            <span class="text-sm text-gray-900 dark:text-gray-100 w-2/3">
                                You are the assigned priest for this reservation
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Confirmation Status -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Confirmation Status</h3>
                    
                    <div class="space-y-3">
                        <!-- Staff -->
                        <div class="flex items-center justify-between py-2">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Staff</span>
                            @if($cancellation->isStaffConfirmed())
                            <span class="flex items-center text-green-600 dark:text-green-400 text-sm">
                                <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Confirmed
                            </span>
                            @else
                            <span class="flex items-center text-yellow-600 dark:text-yellow-400 text-sm">
                                <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                </svg>
                                Pending
                            </span>
                            @endif
                        </div>

                        <!-- Admin -->
                        <div class="flex items-center justify-between py-2">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Admin</span>
                            @if($cancellation->isAdminConfirmed())
                            <span class="flex items-center text-green-600 dark:text-green-400 text-sm">
                                <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Confirmed
                            </span>
                            @else
                            <span class="flex items-center text-yellow-600 dark:text-yellow-400 text-sm">
                                <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                </svg>
                                Pending
                            </span>
                            @endif
                        </div>

                        <!-- Adviser -->
                        @if($cancellation->needsAdviserConfirmation())
                        <div class="flex items-center justify-between py-2">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Adviser</span>
                            @if($cancellation->isAdviserConfirmed())
                            <span class="flex items-center text-green-600 dark:text-green-400 text-sm">
                                <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Confirmed
                            </span>
                            @else
                            <span class="flex items-center text-yellow-600 dark:text-yellow-400 text-sm">
                                <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                </svg>
                                Pending
                            </span>
                            @endif
                        </div>
                        @endif

                        <!-- Priest (This role) -->
                        <div class="flex items-center justify-between py-2 bg-blue-50 dark:bg-blue-900/20 -mx-3 px-3 rounded">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Priest (You)</span>
                            @if($cancellation->isPriestConfirmed())
                            <span class="flex items-center text-green-600 dark:text-green-400 text-sm">
                                <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Confirmed
                            </span>
                            @else
                            <span class="flex items-center text-yellow-600 dark:text-yellow-400 text-sm">
                                <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                </svg>
                                Pending (Action Required)
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            @if(!$cancellation->isPriestConfirmed())
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('priest.cancellations.confirm', $cancellation->cancellation_id) }}" method="POST">
                        @csrf
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Confirm Cancellation</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    By confirming, you acknowledge that you have reviewed this cancellation request.
                                    <span class="text-red-600 dark:text-red-400 font-medium">Please respond within 1 minute.</span>
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
                        <h3 class="text-sm font-medium text-green-800 dark:text-green-300">Already Confirmed</h3>
                        <p class="text-sm text-green-700 dark:text-green-400 mt-1">
                            You have already confirmed this cancellation request on {{ $cancellation->priest_confirmed_at->format('M d, Y h:i A') }}.
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Back Button -->
            <div class="mt-6">
                <a href="{{ route('priest.notifications.index') }}" 
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
