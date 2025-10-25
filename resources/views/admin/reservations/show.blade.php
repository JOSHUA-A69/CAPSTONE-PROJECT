<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <h2 class="text-heading text-xl text-gray-800 dark:text-gray-200">
                Reservation #{{ $reservation->reservation_id }}
            </h2>

            <a href="{{ route('admin.reservations.index') }}" class="btn-ghost">
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
                <div class="mb-6">
                    <span class="badge-success">
                        <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        {{ session('status') }}
                    </span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6">
                    <span class="badge-danger">
                        <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        {{ session('error') }}
                    </span>
                </div>
            @endif

            <!-- Status Banner -->
            @php
                $statusColor = match($reservation->status) {
                    'confirmed', 'completed' => 'border-green-500',
                    'rejected', 'cancelled' => 'border-red-500',
                    default => 'border-yellow-500'
                };
            @endphp
            <div class="card border-l-4 {{ $statusColor }} mb-6">
                <div class="card-body">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h3 class="form-label mb-2">Current Status</h3>
                            @if(in_array($reservation->status, ['confirmed', 'completed']))
                                <span class="badge-success text-lg">{{ ucfirst(str_replace('_', ' ', $reservation->status)) }}</span>
                            @elseif(in_array($reservation->status, ['rejected', 'cancelled']))
                                <span class="badge-danger text-lg">{{ ucfirst(str_replace('_', ' ', $reservation->status)) }}</span>
                            @else
                                <span class="badge-warning text-lg">{{ ucfirst(str_replace('_', ' ', $reservation->status)) }}</span>
                            @endif
                        </div>
                        <div class="text-left sm:text-right">
                            <p class="text-muted text-sm">Submitted on</p>
                            <p class="text-heading font-medium">{{ $reservation->created_at->format('M d, Y h:i A') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column: Main Details -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Reservation Details Card -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="flex items-center">
                                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Reservation Details
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="form-label">Service</label>
                                    <p class="text-heading">{{ $reservation->service->service_name ?? '—' }}</p>
                                </div>

                                <div>
                                    <label class="form-label">Venue</label>
                                    <p class="text-heading">
                                        @if($reservation->custom_venue_name)
                                            {{ $reservation->custom_venue_name }}
                                            <span class="badge-info ml-2">Custom</span>
                                        @else
                                            {{ $reservation->venue->name ?? '—' }}
                                        @endif
                                    </p>
                                </div>

                                <div>
                                    <label class="form-label">Schedule</label>
                                    <p class="text-heading text-indigo-600">
                                        {{ optional($reservation->schedule_date)->format('M d, Y') }}<br>
                                        <span class="text-sm">{{ optional($reservation->schedule_date)->format('h:i A') }}</span>
                                    </p>
                                </div>

                                <div>
                                    <label class="form-label">Participants</label>
                                    <p class="text-heading">{{ $reservation->participants_count ?? '—' }} people</p>
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
                    @if(in_array($reservation->status, ['pending_priest_assignment', 'adviser_approved']))
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
                                            <option value="{{ $priest->user_id }}"
                                                {{ old('officiant_id') == $priest->user_id ? 'selected' : '' }}>
                                                {{ $priest->name }}
                                            </option>
                                        @endforeach
                                    </select>
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

                    <!-- Reject Button -->
                    @if($reservation->status === 'pending_priest_assignment' || $reservation->status === 'adviser_approved')
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Reject Reservation</h3>

                            <form action="{{ route('admin.reservations.reject', $reservation->reservation_id) }}" method="POST"
                                  onsubmit="return confirm('Are you sure you want to reject this reservation?');">
                                @csrf

                                <div class="mb-4">
                                    <label for="admin_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Rejection Reason (optional)
                                    </label>
                                    <textarea name="admin_notes" id="admin_notes" rows="3"
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
