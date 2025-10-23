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

                                            @php
                                                // Check if reservation can be undeclined
                                                $canUndecline = false;
                                                if ($decline->reservation) {
                                                    // Can undecline if:
                                                    // 1. Reservation is still pending reassignment
                                                    // 2. No other priest has been assigned yet
                                                    $canUndecline = in_array($decline->reservation->status, [
                                                        'pending_priest_reassignment',
                                                        'adviser_approved',
                                                        'admin_approved'
                                                    ]) && (
                                                        !$decline->reservation->officiant_id ||
                                                        $decline->reservation->officiant_id == Auth::id()
                                                    );
                                                }
                                            @endphp

                                            @if($canUndecline)
                                                <!-- Undecline Available Notice -->
                                                <div class="mt-3 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded">
                                                    <div class="flex items-start">
                                                        <svg class="w-5 h-5 text-green-600 dark:text-green-400 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                                        </svg>
                                                        <div class="flex-1">
                                                            <p class="text-sm font-medium text-green-800 dark:text-green-300">
                                                                This reservation has not been reassigned yet
                                                            </p>
                                                            <p class="text-xs text-green-700 dark:text-green-400 mt-1">
                                                                If your schedule cleared up, you can undo your decline and accept this assignment.
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Action Buttons -->
                                        <div class="ml-4 flex flex-col gap-2">
                                            @if($decline->reservation)
                                                <!-- View Reservation Button -->
                                                <a href="{{ route('priest.reservations.show', $decline->reservation_id) }}"
                                                   class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors whitespace-nowrap text-center">
                                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                    View Details
                                                </a>

                                                <!-- Undecline Button (if eligible) -->
                                                @if($canUndecline)
                                                    <form method="POST" action="{{ route('priest.reservations.undecline', $decline->reservation_id) }}"
                                                          onsubmit="return confirm('Are you sure you want to undo your decline and accept this assignment?\n\nYou will need to confirm your availability after undoing.');">
                                                        @csrf
                                                        <button type="submit"
                                                                class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors whitespace-nowrap">
                                                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                                                            </svg>
                                                            Undo Decline
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif
                                        </div>
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
