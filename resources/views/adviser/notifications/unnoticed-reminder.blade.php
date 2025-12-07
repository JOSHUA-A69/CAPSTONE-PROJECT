<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <h2 class="text-heading text-2xl font-bold text-gray-800 dark:text-gray-200">
                Action Required
            </h2>

            <a href="{{ route('adviser.notifications.index') }}" class="btn-ghost">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Notifications
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Alert Banner -->
            <div class="bg-gradient-to-r from-orange-500 to-amber-500 rounded-xl p-6 mb-6 text-white shadow-lg">
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0">
                        <div class="w-14 h-14 rounded-full bg-white/20 flex items-center justify-center">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold mb-1">Pending Reservation - {{ $adviserContactInfo['hours_pending'] }} Hours</h3>
                        <p class="text-white/90">A reservation request has been waiting for your approval for over 24 hours. Please review and take action.</p>
                    </div>
                </div>
            </div>

            <!-- Reservation Details Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg sm:rounded-xl mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        Reservation Details
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Service Requested</label>
                                <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ $adviserContactInfo['service_name'] }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Schedule</label>
                                <p class="mt-1 text-base text-gray-900 dark:text-white">{{ $adviserContactInfo['schedule_date'] }}</p>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Requestor</label>
                                <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ $adviserContactInfo['requestor_name'] }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Organization</label>
                                <p class="mt-1 text-base text-gray-900 dark:text-white">{{ $adviserContactInfo['organization_name'] }}</p>
                            </div>
                        </div>
                    </div>

                    @if($notification->reservation)
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <a href="{{ route('adviser.reservations.show', $notification->reservation->reservation_id) }}" 
                           class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            Review & Approve/Reject Reservation
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Your Contact Information Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg sm:rounded-xl">
                <div class="p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Your Contact Information
                    </h3>
                    
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4 border border-blue-200 dark:border-blue-700/50 mb-4">
                        <p class="text-sm text-blue-700 dark:text-blue-300">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            The CREaM Office staff may contact you using the information below if this reservation remains unattended.
                            Please ensure your contact details are up to date.
                        </p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg">
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email Address</label>
                            <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ $adviserContactInfo['email'] }}</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg">
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Phone Number</label>
                            <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ $adviserContactInfo['phone'] }}</p>
                        </div>
                    </div>

                    <div class="mt-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-700/50">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <div>
                                <p class="font-medium text-yellow-800 dark:text-yellow-200">Need to update your contact info?</p>
                                <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">
                                    Visit your <a href="{{ route('profile.edit') }}" class="underline font-semibold hover:text-yellow-900 dark:hover:text-yellow-100">profile settings</a> to update your email address or phone number.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
