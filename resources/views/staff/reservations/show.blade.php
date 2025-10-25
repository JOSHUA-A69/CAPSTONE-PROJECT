<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Reservation #{{ $reservation->reservation_id }}
            </h2>

            <a href="{{ route('staff.reservations.index') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Reservations
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Success/Error Messages -->
            @if(session('status'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    ✅ {{ session('status') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    ❌ {{ session('error') }}
                </div>
            @endif

            <!-- Status Banner -->
            <div class="mb-6 p-4 rounded-lg border-l-4
                {{ $reservation->status === 'pending' ? 'bg-yellow-50 border-yellow-400' : '' }}
                {{ $reservation->status === 'adviser_approved' ? 'bg-blue-50 border-blue-400' : '' }}
                {{ $reservation->status === 'pending_priest_assignment' ? 'bg-purple-50 border-purple-400' : '' }}
                {{ $reservation->status === 'pending_priest_confirmation' ? 'bg-indigo-50 border-indigo-400' : '' }}
                {{ $reservation->status === 'confirmed' ? 'bg-green-50 border-green-400' : '' }}
                {{ $reservation->status === 'rejected' ? 'bg-red-50 border-red-400' : '' }}
                {{ $reservation->status === 'cancelled' ? 'bg-gray-50 border-gray-400' : '' }}
                {{ $reservation->status === 'completed' ? 'bg-teal-50 border-teal-400' : '' }}
            ">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold">Current Status</h3>
                        <p class="text-2xl font-bold mt-1">{{ ucfirst(str_replace('_', ' ', $reservation->status)) }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-600">Submitted on</p>
                        <p class="font-medium">{{ $reservation->created_at->format('M d, Y h:i A') }}</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column: Main Details -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Reservation Details Card -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-semibold mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Reservation Details
                            </h3>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Service</label>
                                    <p class="mt-1 text-base">{{ $reservation->service->service_name ?? '—' }}</p>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Venue</label>
                                    <p class="mt-1 text-base">
                                        @if($reservation->custom_venue_name)
                                            📍 {{ $reservation->custom_venue_name }}
                                            <span class="text-xs text-gray-500 dark:text-gray-400">(Custom Location)</span>
                                        @else
                                            {{ $reservation->venue->name ?? '—' }}
                                        @endif
                                    </p>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Schedule</label>
                                    <p class="mt-1 text-base font-semibold text-blue-600">
                                        {{ optional($reservation->schedule_date)->format('M d, Y') }}<br>
                                        <span class="text-sm">{{ optional($reservation->schedule_date)->format('h:i A') }}</span>
                                    </p>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Participants</label>
                                    <p class="mt-1 text-base">{{ $reservation->participants_count ?? '—' }} people</p>
                                </div>

                                @if($reservation->activity_name)
                                <div class="col-span-2">
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Activity Name</label>
                                    <p class="mt-1 text-base font-semibold">{{ $reservation->activity_name }}</p>
                                </div>
                                @endif

                                @if($reservation->theme)
                                <div class="col-span-2">
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Theme</label>
                                    <p class="mt-1 text-base text-gray-700 dark:text-gray-300">{{ $reservation->theme }}</p>
                                </div>
                                @endif

                                @if($reservation->purpose)
                                <div class="col-span-2">
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Purpose</label>
                                    <p class="mt-1 text-base">{{ $reservation->purpose }}</p>
                                </div>
                                @endif

                                @if($reservation->details)
                                <div class="col-span-2">
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Additional Details</label>
                                    <p class="mt-1 text-base text-gray-700 dark:text-gray-300">{{ $reservation->details }}</p>
                                </div>
                                @endif

                                <!-- Ministry Volunteers Section -->
                                @if($reservation->commentator || $reservation->servers || $reservation->readers || $reservation->choir || $reservation->psalmist || $reservation->prayer_leader)
                                <div class="col-span-2 pt-4 border-t border-gray-200 dark:border-gray-700">
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-3 block">Ministry Volunteers</label>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        @if($reservation->commentator)
                                        <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded">
                                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Commentator</p>
                                            <p class="text-sm mt-1">{{ $reservation->commentator }}</p>
                                        </div>
                                        @endif

                                        @if($reservation->servers)
                                        <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded">
                                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Servers</p>
                                            <p class="text-sm mt-1">{{ $reservation->servers }}</p>
                                        </div>
                                        @endif

                                        @if($reservation->readers)
                                        <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded">
                                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Readers</p>
                                            <p class="text-sm mt-1">{{ $reservation->readers }}</p>
                                        </div>
                                        @endif

                                        @if($reservation->choir)
                                        <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded">
                                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Choir</p>
                                            <p class="text-sm mt-1">{{ $reservation->choir }}</p>
                                        </div>
                                        @endif

                                        @if($reservation->psalmist)
                                        <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded">
                                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Psalmist</p>
                                            <p class="text-sm mt-1">{{ $reservation->psalmist }}</p>
                                        </div>
                                        @endif

                                        @if($reservation->prayer_leader)
                                        <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded">
                                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Leader for Prayer of the Faithful</p>
                                            <p class="text-sm mt-1">{{ $reservation->prayer_leader }}</p>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                @endif

                                @if($reservation->officiant)
                                <div class="col-span-2 pt-4 border-t border-gray-200 dark:border-gray-700">
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Assigned Priest/Officiant</label>
                                    <div class="mt-2 flex items-center">
                                        <svg class="w-8 h-8 mr-3 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        <div>
                                            <p class="font-semibold text-lg">{{ $reservation->officiant->full_name }}</p>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $reservation->officiant->email }}</p>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- History Timeline Card -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-semibold mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Activity History
                            </h3>

                            @if($reservation->history->isEmpty())
                                <p class="text-gray-500 text-sm">No activity recorded yet.</p>
                            @else
                                <div class="space-y-4">
                                    @foreach($reservation->history->sortByDesc('created_at') as $h)
                                    <div class="flex">
                                        <div class="flex-shrink-0 w-2 bg-blue-500 rounded-full mr-4"></div>
                                        <div class="flex-1 pb-4">
                                            <p class="text-sm font-semibold">{{ ucfirst(str_replace('_', ' ', $h->action)) }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                by {{ $h->performedBy?->full_name ?? $h->performedBy?->email ?? 'System' }}
                                                • {{ $h->created_at->format('M d, Y h:i A') }}
                                            </p>
                                            @if($h->remarks)
                                            <p class="text-sm text-gray-700 dark:text-gray-300 mt-2 p-2 bg-gray-50 dark:bg-gray-700 rounded">
                                                "{{ $h->remarks }}"
                                            </p>
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                </div>

                <!-- Right Column: Contact & Actions -->
                <div class="space-y-6">

                    <!-- Contact Information Card -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-semibold mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Contact Information
                            </h3>

                            <!-- Requestor -->
                            <div class="mb-4 pb-4 border-b border-gray-200 dark:border-gray-700">
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Requestor</p>
                                <p class="mt-1 font-semibold">{{ $reservation->user->full_name }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $reservation->user->email }}</p>
                                @if($reservation->user->phone)
                                <a href="tel:{{ $reservation->user->phone }}" class="text-sm text-blue-600 hover:underline flex items-center mt-1">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                    {{ $reservation->user->phone }}
                                </a>
                                @endif
                            </div>

                            <!-- Organization -->
                            @if($reservation->organization)
                            <div class="mb-4 pb-4 border-b border-gray-200 dark:border-gray-700">
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Organization</p>
                                <p class="mt-1 font-semibold">{{ $reservation->organization->org_name }}</p>
                                @if($reservation->organization->adviser)
                                <div class="mt-2 text-sm">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Adviser:</p>
                                    <p class="font-medium">{{ $reservation->organization->adviser->full_name }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $reservation->organization->adviser->email }}</p>
                                </div>
                                @endif
                            </div>
                            @endif

                            <!-- Quick Contact Actions -->
                            <div class="space-y-2">
                                @if($reservation->user->phone)
                                <a href="tel:{{ $reservation->user->phone }}"
                                   class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                    Call Requestor
                                </a>
                                @endif

                                <a href="mailto:{{ $reservation->user->email }}"
                                   class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    Send Email
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Staff Actions Card -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-semibold mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                Staff Actions
                            </h3>

                            @if($reservation->status === 'adviser_approved')
                                <!-- Step 1: After Adviser Approval - Contact & Approve -->
                                @if(!$reservation->contacted_at)
                                    <!-- Step 1a: Contact Requestor -->
                                    <div class="space-y-3">
                                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-4">
                                            <p class="text-sm text-blue-800 dark:text-blue-300">
                                                <strong>Next Step:</strong> Contact the requestor via phone or email to verify their availability and confirm details.
                                            </p>
                                        </div>

                                        <!-- Mark as Contacted Button -->
                                        <form method="POST" action="{{ route('staff.reservations.mark-contacted', $reservation->reservation_id) }}"
                                              onsubmit="return confirm('Have you contacted the requestor? This will send them a confirmation request.');">
                                            @csrf
                                            <button type="submit"
                                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                                </svg>
                                                Mark as Contacted
                                            </button>
                                        </form>

                                        <!-- Mark as Not Available Button -->
                                        <button onclick="showNotAvailableModal()"
                                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 active:bg-yellow-800 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Mark as Not Available
                                        </button>

                                        <!-- Cancel Button -->
                                        <button onclick="showCancelModal()"
                                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            Cancel Reservation
                                        </button>
                                    </div>

                                @elseif(!$reservation->requestor_confirmed_at)
                                    <!-- Step 1b: Waiting for Requestor Confirmation -->
                                    <div class="space-y-3">
                                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                                            <div class="flex items-start">
                                                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                </svg>
                                                <div class="flex-1">
                                                    <h4 class="text-sm font-semibold text-yellow-900 dark:text-yellow-200 mb-1">
                                                        Awaiting Requestor Confirmation
                                                    </h4>
                                                    <p class="text-sm text-yellow-800 dark:text-yellow-300 mb-2">
                                                        Contacted on {{ $reservation->contacted_at->format('M d, Y \a\t g:i A') }}
                                                    </p>
                                                    <p class="text-xs text-yellow-700 dark:text-yellow-400">
                                                        A confirmation request has been sent to the requestor. Once they confirm, you can proceed to approve and assign a priest.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Cancel Button -->
                                        <button onclick="showCancelModal()"
                                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            Cancel Reservation
                                        </button>
                                    </div>

                                @else
                                    <!-- Step 1c: Requestor Confirmed - Ready to Approve -->
                                    <div class="space-y-3">
                                        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-4">
                                            <div class="flex items-start">
                                                <svg class="w-5 h-5 text-green-600 dark:text-green-400 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                <div class="flex-1">
                                                    <h4 class="text-sm font-semibold text-green-900 dark:text-green-200 mb-1">
                                                        Requestor Confirmed!
                                                    </h4>
                                                    <p class="text-sm text-green-800 dark:text-green-300">
                                                        Confirmed on {{ $reservation->requestor_confirmed_at->format('M d, Y \a\t g:i A') }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                            The requestor has confirmed their reservation. You can now approve and proceed to priest assignment.
                                        </p>

                                        <!-- Approve Button -->
                                        <form method="POST" action="{{ route('staff.reservations.approve', $reservation->reservation_id) }}"
                                              onsubmit="return confirm('Approve this reservation for priest assignment?');">
                                            @csrf
                                            <button type="submit"
                                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                Approve Reservation
                                            </button>
                                        </form>

                                        <!-- Cancel Button -->
                                        <button onclick="showCancelModal()"
                                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            Cancel Reservation
                                        </button>
                                    </div>
                                @endif
                            @elseif($reservation->status === 'pending_priest_assignment')
                                <!-- This status should not occur - priest is already selected by requestor -->
                                <div class="space-y-3">
                                    <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded">
                                        <p class="text-sm text-yellow-800 dark:text-yellow-300">
                                            ⚠️ Unexpected state: Priest should already be assigned by requestor. Contact system administrator.
                                        </p>
                                    </div>
                                </div>

                            @elseif($reservation->status === 'pending_priest_confirmation')
                                <!-- Step 3: Waiting for Priest Confirmation -->
                                <div class="space-y-3">
                                    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded">
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <p class="text-sm text-blue-700 dark:text-blue-300">
                                                Waiting for priest confirmation. The assigned priest has been notified.
                                            </p>
                                        </div>
                                    </div>

                                    @if($reservation->officiant)
                                    <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded">
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Assigned to:</p>
                                        <p class="font-semibold">{{ $reservation->officiant->full_name }}</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $reservation->officiant->email }}</p>
                                        @if($reservation->priest_notified_at)
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            Notified: {{ $reservation->priest_notified_at->format('M d, Y g:i A') }}
                                        </p>
                                        @endif
                                    </div>
                                    @endif

                                    <!-- Cancel Button -->
                                    <button onclick="showCancelModal()"
                                            class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                        Cancel Reservation
                                    </button>

                                    <!-- Reschedule -->
                                    <div class="p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded">
                                        <h4 class="text-sm font-semibold mb-2">Reschedule</h4>
                                        <form method="POST" action="{{ route('staff.reservations.reschedule', $reservation->reservation_id) }}"
                                              onsubmit="return confirm('Reschedule this reservation? The assigned priest will be asked to confirm again.');">
                                            @csrf
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                <input type="datetime-local" name="schedule_date" required
                                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                <input type="text" name="remarks" placeholder="Remarks (optional)"
                                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            </div>
                                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Conflicts with the assigned priest will be checked.</p>
                                            <button type="submit"
                                                    class="mt-3 w-full inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                                                Reschedule
                                            </button>
                                        </form>
                                    </div>
                                </div>

                            @elseif($reservation->status === 'pending_priest_reassignment')
                                <!-- Priest Declined - Reassign New Priest -->
                                <div class="space-y-3">
                                    <div class="p-4 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded">
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                            </svg>
                                            <div class="flex-1">
                                                <h4 class="text-sm font-semibold text-orange-900 dark:text-orange-200">
                                                    Priest Declined Assignment
                                                </h4>
                                                <p class="text-sm text-orange-800 dark:text-orange-300">
                                                    Please assign a different priest to this reservation.
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <form method="POST" action="{{ route('staff.reservations.assign-priest', $reservation->reservation_id) }}">
                                        @csrf
                                        <div class="mb-4">
                                            <label for="officiant_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Assign New Priest <span class="text-red-500">*</span>
                                            </label>
                                            <select id="officiant_id"
                                                    name="officiant_id"
                                                    required
                                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                <option value="">-- Select a priest --</option>
                                                @forelse($availablePriests as $priest)
                                                    <option value="{{ $priest->id }}">
                                                        {{ $priest->full_name }} ({{ $priest->email }})
                                                    </option>
                                                @empty
                                                    <option value="" disabled>No available priests for this schedule</option>
                                                @endforelse
                                            </select>
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                Only priests without conflicts in the next {{ config('reservations.conflict_minutes') }} minutes window are shown.
                                            </p>
                                        </div>

                                        <button type="submit"
                                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 active:bg-purple-800 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                            </svg>
                                            Reassign Priest
                                        </button>
                                    </form>

                                    <!-- Cancel Button -->
                                    <button onclick="showCancelModal()"
                                            class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                        Cancel Reservation
                                    </button>
                                </div>

                            @elseif($reservation->status === 'confirmed' || $reservation->status === 'approved')
                                <!-- Step 4: Confirmed - Can Finalize After Event -->
                                <div class="space-y-3">
                                    <div class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded">
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <p class="text-sm text-green-700 dark:text-green-300">
                                                Reservation confirmed! Waiting for event date.
                                            </p>
                                        </div>
                                    </div>

                                    @if($reservation->schedule_date && $reservation->schedule_date->isPast())
                                    <!-- Finalize Button (only show after event date) -->
                                    <form method="POST" action="{{ route('staff.reservations.finalize', $reservation->reservation_id) }}"
                                          onsubmit="return confirm('Mark this reservation as completed?');">
                                        @csrf
                                        <button type="submit"
                                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-teal-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-teal-700 active:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Finalize Reservation
                                        </button>
                                    </form>
                                    @else
                                    <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded">
                                        <p class="text-sm text-yellow-700 dark:text-yellow-300">
                                            ⏰ Event scheduled for {{ $reservation->schedule_date->format('M d, Y') }}.
                                            Finalize option will be available after the event date.
                                        </p>
                                    </div>
                                    @endif

                                    <!-- Cancel Button -->
                                    <button onclick="showCancelModal()"
                                            class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                        Cancel Reservation
                                    </button>
                                </div>

                            @elseif($reservation->status === 'completed')
                                <!-- Step 5: Completed -->
                                <div class="p-4 bg-teal-50 dark:bg-teal-900/20 border border-teal-200 dark:border-teal-800 rounded">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <p class="text-sm text-teal-700 dark:text-teal-300 font-semibold">
                                            ✅ Reservation completed successfully!
                                        </p>
                                    </div>
                                </div>

                            @elseif(in_array($reservation->status, ['cancelled', 'rejected']))
                                <!-- Cancelled/Rejected -->
                                <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                        <p class="text-sm text-red-700 dark:text-red-300 font-semibold">
                                            This reservation has been {{ $reservation->status }}.
                                        </p>
                                    </div>
                                </div>

                            @else
                                <!-- Unknown Status -->
                                <div class="p-4 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded">
                                    <p class="text-sm text-gray-700 dark:text-gray-300">
                                        No actions available for this status.
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- Modals -->

    <!-- Cancel Modal -->
    <div id="cancelModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-50 items-center justify-center p-4" style="display: none;">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Cancel Reservation</h3>
                    <button onclick="hideCancelModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('staff.reservations.cancel', $reservation->reservation_id) }}">
                    @csrf
                    <div class="mb-4">
                        <label for="cancel_reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Reason for Cancellation <span class="text-red-500">*</span>
                        </label>
                        <textarea id="cancel_reason"
                                  name="reason"
                                  rows="4"
                                  required
                                  class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                                  placeholder="Please provide a reason for cancelling this reservation..."></textarea>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            This reason will be sent to the requestor via notification.
                        </p>
                    </div>

                    <div class="flex gap-3">
                        <button type="button"
                                onclick="hideCancelModal()"
                                class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-gray-300 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Close
                        </button>
                        <button type="submit"
                                class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Cancel Reservation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Not Available Modal -->
    <div id="notAvailableModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-50 items-center justify-center p-4" style="display: none;">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Mark as Not Available</h3>
                    <button onclick="hideNotAvailableModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('staff.reservations.not-available', $reservation->reservation_id) }}">
                    @csrf
                    <div class="mb-4">
                        <label for="not_available_reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Reason <span class="text-red-500">*</span>
                        </label>
                        <textarea id="not_available_reason"
                                  name="reason"
                                  rows="4"
                                  required
                                  class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500"
                                  placeholder="e.g., Venue not available, Schedule conflict, etc."></textarea>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            The requestor will be notified with this reason.
                        </p>
                    </div>

                    <div class="flex gap-3">
                        <button type="button"
                                onclick="hideNotAvailableModal()"
                                class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-gray-300 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Close
                        </button>
                        <button type="submit"
                                class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 active:bg-yellow-800 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        function showCancelModal() {
            document.getElementById('cancelModal').style.display = 'flex';
        }
        function hideCancelModal() {
            document.getElementById('cancelModal').style.display = 'none';
        }
        function showNotAvailableModal() {
            document.getElementById('notAvailableModal').style.display = 'flex';
        }
        function hideNotAvailableModal() {
            document.getElementById('notAvailableModal').style.display = 'none';
        }
    </script>

</x-app-layout>
