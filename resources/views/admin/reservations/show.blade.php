<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Reservation #{{ $reservation->reservation_id }}
            </h2>

            <a href="{{ route('admin.reservations.index') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition">
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
                <div class="mb-4 p-4 bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-400 rounded">
                    ✅ {{ session('status') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-400 rounded">
                    ❌ {{ session('error') }}
                </div>
            @endif

            <!-- Status Banner -->
            <div class="mb-6 p-6 rounded-lg border-l-4
                {{ $reservation->status === 'pending' ? 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-400' : '' }}
                {{ $reservation->status === 'adviser_approved' ? 'bg-blue-50 dark:bg-blue-900/20 border-blue-400' : '' }}
                {{ $reservation->status === 'pending_priest_assignment' ? 'bg-purple-50 dark:bg-purple-900/20 border-purple-400' : '' }}
                {{ $reservation->status === 'pending_priest_confirmation' ? 'bg-indigo-50 dark:bg-indigo-900/20 border-indigo-400' : '' }}
                {{ $reservation->status === 'confirmed' ? 'bg-green-50 dark:bg-green-900/20 border-green-400' : '' }}
                {{ $reservation->status === 'rejected' ? 'bg-red-50 dark:bg-red-900/20 border-red-400' : '' }}
                {{ $reservation->status === 'cancelled' ? 'bg-gray-50 dark:bg-gray-700 border-gray-400' : '' }}
                {{ $reservation->status === 'completed' ? 'bg-teal-50 dark:bg-teal-900/20 border-teal-400' : '' }}
            ">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Current Status</h3>
                        <p class="text-2xl font-bold mt-1 text-gray-900 dark:text-gray-100">
                            {{ ucfirst(str_replace('_', ' ', $reservation->status)) }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Submitted on</p>
                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ $reservation->created_at->format('M d, Y h:i A') }}</p>
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
                                            <span class="text-xs text-gray-500 dark:text-gray-400">(Custom)</span>
                                        @else
                                            {{ $reservation->venue->name ?? '—' }}
                                        @endif
                                    </p>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Schedule</label>
                                    <p class="mt-1 text-base font-semibold text-blue-600 dark:text-blue-400">
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
                                    <p class="mt-1 text-base">{{ $reservation->theme }}</p>
                                </div>
                                @endif

                                @if($reservation->purpose)
                                <div class="col-span-2">
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Purpose</label>
                                    <p class="mt-1 text-base">{{ $reservation->purpose }}</p>
                                </div>
                                @endif

                                @if($reservation->preferredOfficiant)
                                <div class="col-span-2">
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Requestor's Preferred Priest</label>
                                    <p class="mt-1 text-base font-semibold">{{ $reservation->preferredOfficiant->full_name }} <span class="text-xs text-gray-500">(preference)</span></p>
                                </div>
                                @endif

                                @if($reservation->details)
                                <div class="col-span-2">
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Additional Details</label>
                                    <p class="mt-1 text-base">{{ $reservation->details }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Requester Information -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-semibold mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Requester Information
                            </h3>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</label>
                                    <p class="mt-1 text-base">{{ $reservation->user->name ?? '—' }}</p>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</label>
                                    <p class="mt-1 text-base">{{ $reservation->user->email ?? '—' }}</p>
                                </div>

                                @if($reservation->organization)
                                <div class="col-span-2">
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Organization</label>
                                    <p class="mt-1 text-base font-semibold">{{ $reservation->organization->org_name }}</p>
                                    @if($reservation->organization->adviser)
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                            Adviser: {{ $reservation->organization->adviser->name }}
                                        </p>
                                    @endif
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Right Column: Actions & Assigned Priest -->
                <div class="lg:col-span-1 space-y-6">

                    <!-- Assigned Priest Info -->
                    @if($reservation->officiant)
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4 flex items-center text-gray-900 dark:text-gray-100">
                                <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Assigned Priest
                            </h3>

                            <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg border border-purple-200 dark:border-purple-700">
                                <p class="font-semibold text-lg text-gray-900 dark:text-gray-100">{{ $reservation->officiant->name }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $reservation->officiant->email }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Assign Priest Form -->
                    @if(in_array($reservation->status, ['pending_priest_assignment', 'pending_priest_reassignment', 'adviser_approved']))
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Assign Priest</h3>

                            <form action="{{ route('admin.reservations.assign-priest', $reservation->reservation_id) }}" method="POST">
                                @csrf

                                <div class="mb-4">
                                    <label for="officiant_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Select Available Priest
                                    </label>
                                    <select name="officiant_id" id="officiant_id" required
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                        <option value="">-- Select Priest --</option>
                                        @foreach($availablePriests as $priest)
                                            <option value="{{ $priest->id }}"
                                                {{ old('officiant_id', $reservation->preferred_officiant_id) == $priest->id ? 'selected' : '' }}>
                                                {{ $priest->name ?? ($priest->first_name . ' ' . $priest->last_name) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        Showing priests without conflicts in the next {{ config('reservations.conflict_minutes') }} minutes window.
                                    </p>
                                    @error('officiant_id')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <button type="submit"
                                        class="w-full px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 transition">
                                    Assign Priest & Approve
                                </button>
                            </form>

                            @if($availablePriests->isEmpty())
                                <div class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded text-sm text-yellow-800 dark:text-yellow-400">
                                    ⚠️ No priests available for this schedule. All priests may have conflicting reservations.
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Reschedule -->
                    @if(!in_array($reservation->status, ['cancelled','rejected','completed']))
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Reschedule</h3>

                            <form action="{{ route('admin.reservations.reschedule', $reservation->reservation_id) }}" method="POST"
                                  onsubmit="return confirm('Reschedule this reservation? The assigned priest will be asked to confirm again.');">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="schedule_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">New Date & Time</label>
                                        <input type="datetime-local" id="schedule_date" name="schedule_date" required
                                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                               value="">
                                    </div>
                                    <div>
                                        <label for="remarks" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Remarks (optional)</label>
                                        <input type="text" id="remarks" name="remarks"
                                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                               placeholder="Reason for reschedule">
                                    </div>
                                </div>
                                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">If a priest is assigned, conflicts will be checked and they’ll be asked to confirm again.</p>
                                <button type="submit"
                                        class="mt-4 w-full px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                                    Reschedule Reservation
                                </button>
                            </form>
                        </div>
                    </div>
                    @endif

                    <!-- Reject Button -->
                    @if($reservation->status === 'pending_priest_assignment' || $reservation->status === 'adviser_approved')
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Reject Reservation</h3>

                            <form action="{{ route('admin.reservations.reject', $reservation->reservation_id) }}" method="POST"
                                  onsubmit="return confirm('Are you sure you want to reject this reservation?');">
                                @csrf

                                <div class="mb-4">
                                    <label for="reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Rejection Reason
                                    </label>
                                    <textarea name="reason" id="reason" rows="3"
                                              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500"
                                              placeholder="Provide reason for rejection...">{{ old('admin_notes') }}</textarea>
                                </div>

                                <button type="submit"
                                        class="w-full px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 transition">
                                    Reject Reservation
                                </button>
                            </form>
                        </div>
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
