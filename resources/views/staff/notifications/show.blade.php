<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <h2 class="text-heading text-2xl font-bold text-gray-800 dark:text-gray-200">
                Notification Details
            </h2>

            <a href="{{ route('staff.notifications.index') }}" class="btn-ghost">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Notifications
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Notification Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg sm:rounded-xl mb-6">
                <div class="p-6">
                    <!-- Notification Header -->
                    <div class="flex items-start gap-4 mb-6">
                        <div class="flex-shrink-0">
                            @if($isUnnoticedNotification ?? false)
                                <div class="w-12 h-12 rounded-xl bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            @else
                                <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1">
                            @if($isUnnoticedNotification ?? false)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-100 dark:bg-orange-900/40 text-orange-800 dark:text-orange-200 mb-2">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    Unnoticed Reservation Alert
                                </span>
                            @endif
                            <div class="text-lg text-gray-900 dark:text-white leading-relaxed">
                                {{ $notification->message }}
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                                {{ $notification->sent_at ? $notification->sent_at->format('F j, Y \a\t g:i A') : $notification->created_at->format('F j, Y \a\t g:i A') }}
                                <span class="text-gray-400 dark:text-gray-500">•</span>
                                {{ $notification->sent_at ? $notification->sent_at->diffForHumans() : $notification->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>

                    <!-- Adviser Contact Information (for unnoticed reservation) -->
                    @if(($isUnnoticedNotification ?? false) && $adviserInfo)
                    <div class="mt-6 p-6 bg-gradient-to-r from-orange-50 to-amber-50 dark:from-orange-900/20 dark:to-amber-900/20 rounded-xl border border-orange-200 dark:border-orange-700/50">
                        <h3 class="text-lg font-bold text-orange-800 dark:text-orange-200 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Adviser Contact Information
                        </h3>
                        <p class="text-sm text-orange-700 dark:text-orange-300 mb-4">
                            The adviser has not responded to this reservation request for <strong>{{ $adviserInfo['hours_pending'] }} hours</strong>. 
                            You may contact them directly if needed.
                        </p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm">
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Adviser Name</label>
                                <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ $adviserInfo['name'] }}</p>
                            </div>
                            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm">
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email Address</label>
                                <a href="mailto:{{ $adviserInfo['email'] }}" class="mt-1 text-lg font-semibold text-blue-600 dark:text-blue-400 hover:underline block">
                                    {{ $adviserInfo['email'] }}
                                </a>
                            </div>
                            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm">
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Phone Number</label>
                                <a href="tel:{{ $adviserInfo['phone'] }}" class="mt-1 text-lg font-semibold text-blue-600 dark:text-blue-400 hover:underline block">
                                    {{ $adviserInfo['phone'] }}
                                </a>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-3">
                            <a href="mailto:{{ $adviserInfo['email'] }}?subject=Pending Reservation Request - Action Required&body=Dear {{ $adviserInfo['name'] }},%0D%0A%0D%0AThis is a follow-up regarding a pending reservation request from {{ $adviserInfo['requestor_name'] }} for {{ $adviserInfo['service_name'] }}.%0D%0A%0D%0AThe request has been waiting for your approval for over 24 hours. Please log in to the system to review and take action.%0D%0A%0D%0AThank you.%0D%0A%0D%0ABest regards,%0D%0ACREaM Office" 
                               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                Send Email
                            </a>
                            <a href="tel:{{ $adviserInfo['phone'] }}" 
                               class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                                Call Adviser
                            </a>
                        </div>
                    </div>
                    @endif

                    <!-- Reservation Details (if linked) -->
                    @if($notification->reservation)
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            Reservation Details
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Service</label>
                                <p class="mt-1 text-base font-semibold text-gray-900 dark:text-white">{{ $notification->reservation->service->service_name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Schedule</label>
                                <p class="mt-1 text-base font-semibold text-gray-900 dark:text-white">
                                    {{ $notification->reservation->schedule_date ? $notification->reservation->schedule_date->format('F d, Y - h:i A') : 'N/A' }}
                                </p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Requestor</label>
                                <p class="mt-1 text-base text-gray-900 dark:text-white">{{ $notification->reservation->user?->full_name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Organization</label>
                                <p class="mt-1 text-base text-gray-900 dark:text-white">{{ $notification->reservation->organization?->org_name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Venue</label>
                                <p class="mt-1 text-base text-gray-900 dark:text-white">{{ $notification->reservation->custom_venue_name ?? $notification->reservation->venue->name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</label>
                                <p class="mt-1">
                                    @php
                                        $status = $notification->reservation->status;
                                        $statusClass = match($status) {
                                            'approved', 'confirmed', 'completed' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
                                            'rejected', 'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
                                            'admin_approved' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
                                            default => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300'
                                        };
                                        $statusLabel = match($status) {
                                            'approved' => 'Approved by Admin',
                                            'admin_approved' => 'Awaiting Admin',
                                            'adviser_approved' => 'Awaiting Priest',
                                            'pending' => 'Awaiting Adviser',
                                            default => ucfirst(str_replace('_', ' ', $status))
                                        };
                                    @endphp
                                    <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        <div class="mt-6">
                            <a href="{{ route('staff.reservations.show', $notification->reservation->reservation_id) }}" 
                               class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                View Full Reservation
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
