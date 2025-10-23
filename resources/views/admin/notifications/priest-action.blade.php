<x-app-layout>
    @php
        // Parse action type from data
        $action = $data['action'] ?? 'unknown';
        $priestName = $data['priest_name'] ?? 'Unknown';
        $priestId = $data['priest_id'] ?? null;
        $requestorName = $data['requestor_name'] ?? 'Unknown';

        // Determine notification styling based on action
        $isConfirmation = $action === 'priest_confirmed';
        $isCancellation = $action === 'priest_cancelled_confirmation';
        $isUndecline = $action === 'priest_undeclined';

        if ($isConfirmation) {
            $bgColor = 'bg-green-50 dark:bg-green-900/20';
            $borderColor = 'border-green-600';
            $textColor = 'text-green-800 dark:text-green-300';
            $iconColor = 'text-green-600';
            $title = '✓ Priest Confirmed Assignment';
            $subtitle = 'Good News - No Action Required';
            $icon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
        } elseif ($isCancellation) {
            $bgColor = 'bg-red-50 dark:bg-red-900/20';
            $borderColor = 'border-red-600';
            $textColor = 'text-red-800 dark:text-red-300';
            $iconColor = 'text-red-600';
            $title = '⚠️ Priest Cancelled Confirmation';
            $subtitle = 'Urgent - Reassignment Required';
            $icon = '<path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>';
        } else {
            $bgColor = 'bg-blue-50 dark:bg-blue-900/20';
            $borderColor = 'border-blue-600';
            $textColor = 'text-blue-800 dark:text-blue-300';
            $iconColor = 'text-blue-600';
            $title = '🔄 Priest Undeclined Reservation';
            $subtitle = 'Status Update';
            $icon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>';
        }

        $displayPriestName = stripos($priestName, 'Fr.') === 0 ? $priestName : 'Fr. ' . $priestName;
    @endphp

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $isConfirmation ? 'Priest Approved Reservation' : ($isCancellation ? 'Priest Cancelled Confirmation' : 'Priest Action Notification') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Alert Header -->
            <div class="{{ $bgColor }} border-l-4 {{ $borderColor }} p-6 mb-6 rounded-lg">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 {{ $iconColor }}" fill="currentColor" viewBox="0 0 20 20">
                            {!! $icon !!}
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-bold {{ $textColor }}">
                            {{ $title }}
                        </h3>
                        <p class="text-sm {{ $textColor }} mt-1">
                            {{ $subtitle }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <!-- Introduction -->
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <p class="text-gray-700 dark:text-gray-300">
                        Dear CREaM Administrator,
                    </p>
                    <div class="mt-4 p-4 {{ $bgColor }} rounded-md border {{ str_replace('50', '200', str_replace('900/20', '800', $bgColor)) }}">
                        <p class="text-sm {{ $textColor }} font-medium">
                            @if($isConfirmation)
                                ✓ {{ $displayPriestName }} has <strong>confirmed</strong> their availability for this reservation.<br>
                                The reservation is now fully approved and the requestor has been notified.
                            @elseif($isCancellation)
                                ⚠️ {{ $displayPriestName }} has <strong>cancelled</strong> their previously confirmed assignment.<br>
                                You need to assign another priest or contact the requestor about rescheduling.
                            @else
                                🔄 {{ $displayPriestName }} has <strong>undeclined</strong> their previous decline.<br>
                                The priest is now available again for this reservation.
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Priest Information -->
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Priest Information</h3>
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0">
                            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center text-white font-bold text-xl">
                                {{ strtoupper(substr($priestName, 0, 1)) }}{{ strtoupper(substr(explode(' ', $priestName)[1] ?? '', 0, 1)) }}
                            </div>
                        </div>
                        <div>
                            <p class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                                {{ $displayPriestName }}
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Assigned Priest
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Reservation Details -->
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Reservation Details</h3>
                    <dl class="space-y-3">
                        <div class="flex">
                            <dt class="font-medium text-gray-500 dark:text-gray-400 w-40">Reservation ID:</dt>
                            <dd class="text-gray-900 dark:text-gray-100 font-mono">#{{ $notification->reservation->reservation_id }}</dd>
                        </div>
                        <div class="flex">
                            <dt class="font-medium text-gray-500 dark:text-gray-400 w-40">Service Type:</dt>
                            <dd class="text-gray-900 dark:text-gray-100">{{ $notification->reservation->service->service_name }}</dd>
                        </div>
                        <div class="flex">
                            <dt class="font-medium text-gray-500 dark:text-gray-400 w-40">Date & Time:</dt>
                            <dd class="text-gray-900 dark:text-gray-100">
                                📅 {{ $notification->reservation->schedule_date->format('l, F d, Y') }}<br>
                                🕐 {{ $notification->reservation->schedule_date->format('h:i A') }}
                            </dd>
                        </div>
                        <div class="flex">
                            <dt class="font-medium text-gray-500 dark:text-gray-400 w-40">Venue:</dt>
                            <dd class="text-gray-900 dark:text-gray-100">
                                📍 {{ $notification->reservation->custom_venue_name ?? $notification->reservation->venue->name ?? 'N/A' }}
                            </dd>
                        </div>
                        <div class="flex">
                            <dt class="font-medium text-gray-500 dark:text-gray-400 w-40">Status:</dt>
                            <dd>
                                <span class="px-3 py-1 rounded-full text-xs font-semibold
                                    @if($notification->reservation->status === 'approved') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300
                                    @elseif($notification->reservation->status === 'pending_priest_reassignment') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300
                                    @else bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300
                                    @endif">
                                    {{ ucwords(str_replace('_', ' ', $notification->reservation->status)) }}
                                </span>
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- Requestor Information -->
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Requestor Information</h3>
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-cyan-600 flex items-center justify-center text-white font-semibold">
                                {{ strtoupper(substr($requestorName, 0, 1)) }}{{ strtoupper(substr(explode(' ', $requestorName)[1] ?? '', 0, 1)) }}
                            </div>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-gray-100">
                                {{ $requestorName }}
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Service Requestor
                            </p>
                            @if($notification->reservation->user->email)
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                📧 {{ $notification->reservation->user->email }}
                            </p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Additional Service Details -->
                @if($notification->reservation->activity_name || $notification->reservation->theme || $notification->reservation->participants_count)
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Service Details</h3>
                    <dl class="space-y-3">
                        @if($notification->reservation->activity_name)
                        <div class="flex">
                            <dt class="font-medium text-gray-500 dark:text-gray-400 w-40">Activity Name:</dt>
                            <dd class="text-gray-900 dark:text-gray-100">{{ $notification->reservation->activity_name }}</dd>
                        </div>
                        @endif
                        @if($notification->reservation->theme)
                        <div class="flex">
                            <dt class="font-medium text-gray-500 dark:text-gray-400 w-40">Theme:</dt>
                            <dd class="text-gray-900 dark:text-gray-100">{{ $notification->reservation->theme }}</dd>
                        </div>
                        @endif
                        @if($notification->reservation->participants_count)
                        <div class="flex">
                            <dt class="font-medium text-gray-500 dark:text-gray-400 w-40">Participants:</dt>
                            <dd class="text-gray-900 dark:text-gray-100">{{ $notification->reservation->participants_count }} people</dd>
                        </div>
                        @endif
                    </dl>
                </div>
                @endif

                <!-- Action Buttons -->
                @if($isCancellation)
                <div class="p-6 bg-gray-50 dark:bg-gray-900/50">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 text-center">
                        Required Actions
                    </h3>

                    <div class="flex gap-3 justify-center flex-wrap">
                        <!-- View Full Reservation Button -->
                        <a href="{{ route('admin.reservations.show', $notification->reservation->reservation_id) }}"
                           class="inline-flex items-center px-6 py-3 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            View Complete Reservation
                        </a>

                        <!-- Reassign Priest Button -->
                        <a href="{{ route('admin.reservations.show', $notification->reservation->reservation_id) }}#assign-priest"
                           class="inline-flex items-center px-6 py-3 bg-red-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Assign Another Priest
                        </a>
                    </div>

                    <p class="text-sm text-gray-600 dark:text-gray-400 text-center mt-4">
                        Please reassign a priest as soon as possible to avoid service disruption.
                    </p>
                </div>
                @endif

                <!-- Notification Metadata -->
                <div class="p-6 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400">
                        <div>
                            <span class="font-semibold">Notification Received:</span> {{ $notification->sent_at->format('F d, Y - h:i A') }}
                        </div>
                        <div>
                            <span class="font-semibold">Type:</span>
                            <span class="px-2 py-1 rounded text-xs font-semibold
                                @if($isConfirmation) bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300
                                @elseif($isCancellation) bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300
                                @else bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300
                                @endif">
                                {{ $notification->type }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="p-6 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700 text-center text-sm text-gray-600 dark:text-gray-400">
                    <p class="font-semibold">CREaM - eReligiousServices Management System</p>
                    <p>Holy Name University</p>
                    <p class="text-xs mt-2 text-gray-500 dark:text-gray-500">
                        This is an automated notification from the system.
                    </p>
                </div>
            </div>

            <!-- Back Button -->
            <div class="mt-6">
                <a href="{{ route('admin.notifications.index') }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                    ← Back to All Notifications
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
