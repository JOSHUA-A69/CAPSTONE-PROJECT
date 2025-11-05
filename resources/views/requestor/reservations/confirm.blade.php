<x-app-layout>
    <x-slot name="header">
        <h2 class="text-heading font-semibold text-xl leading-tight">
                    @endif
                </div>
            </div>

            <!-- Ministry Volunteers -->
            @if($reservation->commentator || $reservation->servers || $reservation->readers || $reservation->choir || $reservation->psalmist || $reservation->prayer_leader)
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Ministry Volunteers</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if($reservation->commentator)
                    <div>
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Commentator</label>
                        <p class="text-sm text-gray-900 dark:text-gray-100 mt-1">{{ $reservation->commentator }}</p>
                    </div>
                    @endif

                    @if($reservation->servers)
                    <div>
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Servers</label>
                        <p class="text-sm text-gray-900 dark:text-gray-100 mt-1">{{ $reservation->servers }}</p>
                    </div>
                    @endif

                    @if($reservation->readers)
                    <div>
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Readers</label>
                        <p class="text-sm text-gray-900 dark:text-gray-100 mt-1">{{ $reservation->readers }}</p>
                    </div>
                    @endif

                    @if($reservation->choir)
                    <div>
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Choir</label>
                        <p class="text-sm text-gray-900 dark:text-gray-100 mt-1">{{ $reservation->choir }}</p>
                    </div>
                    @endif

                    @if($reservation->psalmist)
                    <div>
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Psalmist</label>
                        <p class="text-sm text-gray-900 dark:text-gray-100 mt-1">{{ $reservation->psalmist }}</p>
                    </div>
                    @endif

                    @if($reservation->prayer_leader)
                    <div>
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Leader for Prayer of the Faithful</label>
                        <p class="text-sm text-gray-900 dark:text-gray-100 mt-1">{{ $reservation->prayer_leader }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif
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
