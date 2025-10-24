@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6">
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 px-6 py-8 text-white">
            <div class="flex items-center mb-4">
                <svg class="w-12 h-12 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <h1 class="text-3xl font-bold">Reservation Confirmation</h1>
                    <p class="text-blue-100 mt-1">Please review and confirm your reservation details</p>
                </div>
            </div>
        </div>

        <!-- Confirmation Notice -->
        <div class="px-6 py-6 bg-blue-50 dark:bg-blue-900/20 border-b border-blue-100 dark:border-blue-800">
            <div class="flex items-start">
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-200 mb-2">
                        CREaM Office has contacted you about this reservation
                    </h3>
                    <p class="text-sm text-blue-800 dark:text-blue-300 mb-3">
                        The CREaM staff has reached out to verify your availability. Please review the reservation details below and confirm if you would like to proceed with this request.
                    </p>
                    <p class="text-xs text-blue-700 dark:text-blue-400 font-medium">
                        Contacted on: {{ $reservation->contacted_at->format('F d, Y \a\t g:i A') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Reservation Details -->
        <div class="px-6 py-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Reservation Details
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Activity Information -->
                <div class="space-y-4">
                    <div>
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Activity Name</label>
                        <p class="text-base font-semibold text-gray-900 dark:text-gray-100 mt-1">{{ $reservation->activity_name ?? $reservation->purpose }}</p>
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date & Time</label>
                        <p class="text-base text-gray-900 dark:text-gray-100 mt-1">
                            {{ $reservation->schedule_date->format('F d, Y') }} at {{ $reservation->schedule_date->format('g:i A') }}
                        </p>
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Service Type</label>
                        <p class="text-base text-gray-900 dark:text-gray-100 mt-1">{{ $reservation->service->service_name }}</p>
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Venue</label>
                        <p class="text-base text-gray-900 dark:text-gray-100 mt-1">
                            @if($reservation->custom_venue_name)
                                📍 {{ $reservation->custom_venue_name }} <span class="text-xs text-gray-500">(Custom Location)</span>
                            @else
                                {{ $reservation->venue->name }}
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="space-y-4">
                    @if($reservation->theme)
                    <div>
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Theme</label>
                        <p class="text-base text-gray-900 dark:text-gray-100 mt-1">{{ $reservation->theme }}</p>
                    </div>
                    @endif

                    @if($reservation->participants_count)
                    <div>
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Expected Participants</label>
                        <p class="text-base text-gray-900 dark:text-gray-100 mt-1">{{ $reservation->participants_count }} people</p>
                    </div>
                    @endif

                    @if($reservation->organization)
                    <div>
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Organization</label>
                        <p class="text-base text-gray-900 dark:text-gray-100 mt-1">{{ $reservation->organization->org_name }}</p>
                    </div>
                    @endif

                    <!-- Additional Details removed (details field dropped) -->
                </div>
            </div>

            <!-- Ministry Volunteers removed: managed via events & assignments after approval -->
        </div>

        <!-- Confirmation Actions -->
        <div class="px-6 py-6 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Confirm Your Reservation</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                By confirming, you verify that the details above are correct and you are available for this reservation. The CREaM staff will proceed to assign an officiant and finalize the arrangements.
            </p>

            <div class="flex flex-col sm:flex-row gap-3">
                <!-- Confirm Button -->
                <form method="POST" action="{{ route('requestor.reservations.confirm-reservation', ['reservation_id' => $reservation->reservation_id, 'token' => $token]) }}" class="flex-1">
                    @csrf
                    <button type="submit"
                            onclick="return confirm('Are you sure you want to confirm this reservation? This action cannot be undone.');"
                            class="w-full inline-flex items-center justify-center px-6 py-3 bg-green-600 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-wider hover:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Confirm Reservation
                    </button>
                </form>

                <!-- Cancel Button -->
                <form method="POST" action="{{ route('requestor.reservations.decline-reservation', ['reservation_id' => $reservation->reservation_id, 'token' => $token]) }}" class="flex-1">
                    @csrf
                    <button type="submit"
                            onclick="return confirm('Are you sure you want to decline this reservation? This will cancel your request.');"
                            class="w-full inline-flex items-center justify-center px-6 py-3 bg-red-600 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-wider hover:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Decline Reservation
                    </button>
                </form>
            </div>

            <p class="text-xs text-gray-500 dark:text-gray-500 mt-4 text-center">
                If you have any questions, please contact the CREaM Office directly.
            </p>
        </div>
    </div>
</div>
@endsection
