<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('New Assignment Notification') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Alert Header -->
            <div class="bg-purple-50 dark:bg-purple-900/20 border-l-4 border-purple-600 p-6 mb-6 rounded-lg">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-bold text-purple-800 dark:text-purple-300">
                            📋 New Service Assignment
                        </h3>
                        <p class="text-sm text-purple-700 dark:text-purple-400 mt-1">
                            You have been assigned to officiate a service
                        </p>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <!-- Introduction -->
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <p class="text-gray-700 dark:text-gray-300">
                        Dear Father {{ auth()->user()->first_name }},
                    </p>
                    <div class="mt-4 p-4 bg-purple-50 dark:bg-purple-900/20 rounded-md border border-purple-200 dark:border-purple-800">
                        <p class="text-sm text-purple-800 dark:text-purple-300 font-medium">
                            🔔 You have been assigned to officiate the following service.<br>
                            Please review the details and confirm your availability.
                        </p>
                    </div>
                </div>

                <!-- Reservation Details -->
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Service Details</h3>
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
                            <dt class="font-medium text-gray-500 dark:text-gray-400 w-40">Requestor:</dt>
                            <dd class="text-gray-900 dark:text-gray-100">
                                {{ $notification->reservation->user->first_name }} {{ $notification->reservation->user->last_name }}
                            </dd>
                        </div>
                        <div class="flex">
                            <dt class="font-medium text-gray-500 dark:text-gray-400 w-40">Date & Time:</dt>
                            <dd class="text-gray-900 dark:text-gray-100 font-semibold">
                                {{ $notification->reservation->schedule_date->format('l, F d, Y - h:i A') }}
                            </dd>
                        </div>
                        <div class="flex">
                            <dt class="font-medium text-gray-500 dark:text-gray-400 w-40">Venue:</dt>
                            <dd class="text-gray-900 dark:text-gray-100">
                                @if($notification->reservation->custom_venue_name)
                                    📍 {{ $notification->reservation->custom_venue_name }} <em class="text-sm text-gray-500">(Custom Location)</em>
                                @else
                                    📍 {{ $notification->reservation->venue->name ?? 'N/A' }}
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- Admin Remarks (if available) -->
                @php
                    $data = null;
                    if ($notification->data) {
                        $data = is_string($notification->data) ? json_decode($notification->data, true) : $notification->data;
                    }
                @endphp

                @if($data && isset($data['admin_remarks']) && $data['admin_remarks'])
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Admin's Message:</h3>
                    <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-md border border-gray-200 dark:border-gray-700">
                        <p class="text-gray-800 dark:text-gray-200">{{ $data['admin_remarks'] }}</p>
                    </div>
                </div>
                @endif

                <!-- Action Buttons -->
                <div class="p-6 bg-gray-50 dark:bg-gray-900/50">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 text-center">Your Response</h3>

                    <div class="flex gap-3 justify-center">
                        <!-- View Full Details Button -->
                        <a href="{{ route('priest.reservations.show', $notification->reservation_id) }}"
                           class="inline-flex items-center px-6 py-3 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            View Full Reservation Details
                        </a>
                    </div>

                    <p class="text-sm text-gray-600 dark:text-gray-400 text-center mt-4">
                        Click the button above to view complete details and confirm or decline this assignment
                    </p>
                </div>

                <!-- Next Steps -->
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Next Steps:</h3>
                    <ol class="list-decimal list-inside space-y-2 text-gray-700 dark:text-gray-300">
                        <li>Review the complete service details</li>
                        <li>Check your schedule for the date and time</li>
                        <li>Confirm your availability if you can officiate</li>
                        <li>Decline if you have a conflict (provide a reason)</li>
                    </ol>
                </div>

                <!-- Footer -->
                <div class="p-6 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700 text-center text-sm text-gray-600 dark:text-gray-400">
                    <p class="font-semibold">CREaM - eReligiousServices Management System</p>
                    <p>Holy Name University</p>
                    <p class="text-xs mt-2 text-gray-500 dark:text-gray-500">
                        This is an automated notification. Please respond through the system.
                    </p>
                </div>
            </div>

            <!-- Back Button -->
            <div class="mt-6">
                <a href="{{ route('priest.notifications.index') }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                    ← Back to All Notifications
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
