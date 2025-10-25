@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    Reservation #{{ $reservation->reservation_id }}
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Submitted on {{ $reservation->created_at->format('F d, Y') }}
                </p>
            </div>
            <a href="{{ route('requestor.reservations.index') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to My Reservations
            </a>
        </div>

        <!-- Success/Info Messages -->
        @if (session('status'))
            <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                <div class="flex">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-sm text-green-800 dark:text-green-300">{{ session('message') ?? 'Action completed successfully' }}</p>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                <p class="text-sm text-red-800 dark:text-red-300">{{ session('error') }}</p>
            </div>
        @endif

        @if (session('info'))
            <div class="mb-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <p class="text-sm text-blue-800 dark:text-blue-300">{{ session('info') }}</p>
            </div>
        @endif

        <!-- Status Banner -->
        @php
            $statusColors = [
                'pending' => [
                    'banner' => 'bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800',
                    'badge' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100',
                    'text' => 'text-yellow-800 dark:text-yellow-200'
                ],
                'adviser_approved' => [
                    'banner' => 'bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800',
                    'badge' => 'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100',
                    'text' => 'text-blue-800 dark:text-blue-200'
                ],
                'pending_priest_assignment' => [
                    'banner' => 'bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800',
                    'badge' => 'bg-purple-100 text-purple-800 dark:bg-purple-800 dark:text-purple-100',
                    'text' => 'text-purple-800 dark:text-purple-200'
                ],
                'pending_priest_confirmation' => [
                    'banner' => 'bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800',
                    'badge' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-800 dark:text-indigo-100',
                    'text' => 'text-indigo-800 dark:text-indigo-200'
                ],
                'confirmed' => [
                    'banner' => 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800',
                    'badge' => 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100',
                    'text' => 'text-green-800 dark:text-green-200'
                ],
                'completed' => [
                    'banner' => 'bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800',
                    'badge' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-800 dark:text-emerald-100',
                    'text' => 'text-emerald-800 dark:text-emerald-200'
                ],
                'cancelled' => [
                    'banner' => 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800',
                    'badge' => 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100',
                    'text' => 'text-red-800 dark:text-red-200'
                ],
                'rejected' => [
                    'banner' => 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800',
                    'badge' => 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100',
                    'text' => 'text-red-800 dark:text-red-200'
                ],
            ];

            $currentColors = $statusColors[$reservation->status] ?? [
                'banner' => 'bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700',
                'badge' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-100',
                'text' => 'text-gray-800 dark:text-gray-200'
            ];
        @endphp

        <div class="{{ $currentColors['banner'] }}">
            <div class="flex items-center">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $currentColors['badge'] }}">
                    {{ ucfirst(str_replace('_', ' ', $reservation->status)) }}
                </span>
                <span class="ml-3 text-sm {{ $currentColors['text'] }}">
                    @if($reservation->status === 'pending')
                        Waiting for organization adviser approval
                    @elseif($reservation->status === 'adviser_approved')
                        Adviser approved — awaiting admin assignment and priest confirmation
                    @elseif($reservation->status === 'pending_priest_assignment')
                        Approved — waiting for priest assignment
                    @elseif($reservation->status === 'pending_priest_confirmation')
                        Priest assigned — waiting for priest confirmation
                    @elseif($reservation->status === 'approved' || $reservation->status === 'confirmed')
                        Confirmed — event scheduled
                    @elseif($reservation->status === 'completed')
                        Event completed successfully
                    @elseif($reservation->status === 'cancelled')
                        Reservation cancelled
                    @elseif($reservation->status === 'rejected')
                        Reservation not available
                    @endif
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6">
            <!-- Reservation Details Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Reservation Details
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Left Column -->
                        <div class="space-y-4">
                            @if($reservation->activity_name)
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Activity Name</label>
                                <p class="mt-1 text-base font-semibold">{{ $reservation->activity_name }}</p>
                            </div>
                            @endif

                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Service Type</label>
                                <p class="mt-1 text-base">{{ $reservation->service->service_name }}</p>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Venue</label>
                                <p class="mt-1 text-base">
                                    @if($reservation->custom_venue_name)
                                        📍 {{ $reservation->custom_venue_name }}
                                        <span class="text-xs text-gray-500 dark:text-gray-400">(Custom Location)</span>
                                    @else
                                        {{ $reservation->venue->name }}
                                    @endif
                                </p>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Schedule</label>
                                <p class="mt-1 text-base font-semibold text-blue-600">
                                    {{ $reservation->schedule_date->format('F d, Y') }}<br>
                                    <span class="text-sm">{{ $reservation->schedule_date->format('g:i A') }}</span>
                                </p>
                            </div>

                            @if($reservation->participants_count)
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Expected Participants</label>
                                <p class="mt-1 text-base">{{ $reservation->participants_count }} people</p>
                            </div>
                            @endif
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-4">
                            @if($reservation->theme)
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Theme</label>
                                <p class="mt-1 text-base text-gray-700 dark:text-gray-300">{{ $reservation->theme }}</p>
                            </div>
                            @endif

                            @if($reservation->organization)
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Organization</label>
                                <p class="mt-1 text-base">{{ $reservation->organization->org_name }}</p>
                            </div>
                            @endif

                            @if($reservation->purpose)
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Purpose</label>
                                <p class="mt-1 text-base">{{ $reservation->purpose }}</p>
                            </div>
                            @endif

                            <!-- Additional Details removed (details field dropped) -->

                            @if($reservation->officiant)
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Assigned Priest</label>
                                <p class="mt-1 text-base font-semibold">{{ $reservation->officiant->full_name }}</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Ministry Volunteers Section removed: managed via events & assignments after approval -->
                </div>
            </div>

            <!-- Actions Card (if applicable) -->
            @php
                $isTerminal = in_array($reservation->status, ['cancelled','rejected','completed']);
                $isPast = optional($reservation->schedule_date)->isPast();
                // Allow Requestor to cancel for any non-terminal, future-dated reservation
                $canCancel = !$isTerminal && !$isPast;
            @endphp

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">Actions</h3>

                    @if($canCancel)
                        <button onclick="document.getElementById('cancelForm').classList.toggle('hidden')"
                                class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 transition">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Cancel Reservation
                        </button>

                        <form id="cancelForm" method="POST" action="{{ route('requestor.reservations.cancel', $reservation->reservation_id) }}" class="hidden mt-4 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
                            @csrf
                            <label class="block text-sm font-medium mb-2">Reason for Cancellation</label>
                            <textarea name="reason" rows="3" required class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" placeholder="Please provide a reason..."></textarea>
                            <div class="mt-3 flex gap-2">
                                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition">Confirm Cancellation</button>
                                <button type="button" onclick="document.getElementById('cancelForm').classList.add('hidden')" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-md hover:bg-gray-400 dark:hover:bg-gray-500 transition">Cancel</button>
                            </div>
                        </form>
                    @else
                        <div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-700 p-4 text-sm text-gray-600 dark:text-gray-300">
                            @if($isTerminal)
                                This reservation is already {{ ucfirst($reservation->status) }}. No further actions are available.
                            @elseif($isPast)
                                This reservation is in the past ({{ optional($reservation->schedule_date)->format('M d, Y g:i A') }}). No further actions are available.
                            @else
                                No actions are available at this stage.
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
