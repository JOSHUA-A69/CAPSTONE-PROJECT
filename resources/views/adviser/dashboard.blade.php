<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @php
                $user = auth()->user();
                $displayName = $user->first_name ?? $user->name ?? $user->email ?? 'User';
                $orgIds = $user->organizations->pluck('org_id');

                // Core counts
                $pendingCount = \App\Models\Reservation::where(function($q) use ($orgIds) {
                        $q->whereIn('org_id', $orgIds)
                          ->orWhereHas('organizations', function($sq) use ($orgIds) {
                              $sq->whereIn('organizations.org_id', $orgIds);
                          });
                    })
                    ->where('status', 'pending')
                    ->count();

                $adviserApprovedCount = \App\Models\Reservation::where(function($q) use ($orgIds) {
                        $q->whereIn('org_id', $orgIds)
                          ->orWhereHas('organizations', function($sq) use ($orgIds) {
                              $sq->whereIn('organizations.org_id', $orgIds);
                          });
                    })
                    ->where('status', 'adviser_approved')
                    ->count();

                $approvedUpcomingCount = \App\Models\Reservation::where(function($q) use ($orgIds) {
                        $q->whereIn('org_id', $orgIds)
                          ->orWhereHas('organizations', function($sq) use ($orgIds) {
                              $sq->whereIn('organizations.org_id', $orgIds);
                          });
                    })
                    ->whereIn('status', ['admin_approved', 'approved'])
                    ->where('schedule_date', '>=', now())
                    ->count();

                // Unnoticed > 24h old still pending
                $unnoticedCount = \App\Models\Reservation::where(function($q) use ($orgIds) {
                        $q->whereIn('org_id', $orgIds)
                          ->orWhereHas('organizations', function($sq) use ($orgIds) {
                              $sq->whereIn('organizations.org_id', $orgIds);
                          });
                    })
                    ->unnoticedByAdviser()
                    ->count();

                // Organization booking counts
                $orgBookingPending = \App\Models\OrganizationBookingRequest::whereIn('organization_id', $orgIds)
                    ->where('status', 'pending')
                    ->count();
                    
                $orgBookingApproved = \App\Models\OrganizationBookingRequest::whereIn('organization_id', $orgIds)
                    ->where('status', 'approved')
                    ->count();

                // Pending Cancellations
                $cancellationCount = \App\Models\ReservationCancellation::whereHas('reservation', function($q) use ($orgIds) {
                        $q->whereIn('org_id', $orgIds)
                          ->orWhereHas('organizations', function($sq) use ($orgIds) {
                              $sq->whereIn('organizations.org_id', $orgIds);
                          });
                    })
                    ->whereNull('adviser_confirmed_at')
                    ->count();

                // Next upcoming service
                $nextService = \App\Models\Reservation::where(function($q) use ($orgIds) {
                        $q->whereIn('org_id', $orgIds)
                          ->orWhereHas('organizations', function($sq) use ($orgIds) {
                              $sq->whereIn('organizations.org_id', $orgIds);
                          });
                    })
                    ->whereIn('status', ['admin_approved', 'approved'])
                    ->where('schedule_date', '>=', now())
                    ->orderBy('schedule_date', 'asc')
                    ->first();
                
                $daysUntilService = null;
                $serviceDate = $nextService ? $nextService->schedule_date->startOfDay() : null;
                $today = now()->startOfDay();
                if ($nextService) {
                    if ($serviceDate->equalTo($today)) {
                        $daysUntilService = 0;
                    } elseif ($serviceDate->greaterThan($today)) {
                        $daysUntilService = $today->diffInDays($serviceDate);
                    } else {
                        $daysUntilService = -$serviceDate->diffInDays($today);
                    }
                }
            @endphp

            <!-- Welcome Banner -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/10 dark:to-indigo-900/10 rounded-2xl p-8 mb-8 border border-blue-100 dark:border-blue-800/30">
                <div class="flex items-center justify-between flex-wrap gap-6">
                    <div class="flex-1">
                        <h3 class="text-3xl font-bold mb-3 text-gray-900 dark:text-white">
                            Welcome, {{ $displayName }}! 👋
                        </h3>
                        <p class="text-gray-600 dark:text-gray-300 text-lg leading-relaxed max-w-2xl">
                            Review and triage reservation requests for your assigned organizations.
                        </p>
                    </div>
                    @if($nextService)
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-lg border border-gray-100 dark:border-gray-700 min-w-[220px]">
                            <div class="text-center">
                                <div class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wide mb-2">NEXT SERVICE</div>
                                <div class="text-3xl font-bold text-gray-900 dark:text-white mb-1">{{ $nextService->schedule_date->format('M d') }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                                    @if($daysUntilService === 0)
                                        Today
                                    @elseif($daysUntilService === 1)
                                        Tomorrow
                                    @elseif($daysUntilService > 1)
                                        {{ $daysUntilService }} days from now
                                    @elseif($daysUntilService < 0)
                                        {{ abs($daysUntilService) }} days ago
                                    @endif
                                </div>
                                <div class="inline-block bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 text-xs font-medium px-3 py-1 rounded-full">
                                    Confirmed
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-lg border border-gray-100 dark:border-gray-700 min-w-[220px]">
                            <div class="text-center">
                                <div class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-2">NEXT SERVICE</div>
                                <div class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No Events Scheduled</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">All upcoming services are pending review</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Main Workflow Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Pending Requests -->
                <a href="{{ route('adviser.reservations.index', ['filter' => 'pending']) }}" class="group">
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 border border-gray-100 dark:border-gray-700 group-hover:border-orange-200 dark:group-hover:border-orange-600 group-hover:-translate-y-1">
                        <div class="flex items-center justify-between mb-4">
                            <div class="bg-orange-50 dark:bg-orange-900/20 p-3 rounded-xl group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ $pendingCount }}</div>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Pending Requests</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Requires your review</p>
                        </div>
                    </div>
                </a>

                <!-- Cancellation Requests -->
                <a href="{{ route('adviser.cancellations.index') }}" class="group">
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 border border-gray-100 dark:border-gray-700 group-hover:border-red-200 dark:group-hover:border-red-600 group-hover:-translate-y-1">
                        <div class="flex items-center justify-between mb-4">
                            <div class="bg-red-50 dark:bg-red-900/20 p-3 rounded-xl group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ $cancellationCount }}</div>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Cancellation Requests</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Waiting for confirmation</p>
                        </div>
                    </div>
                </a>

                <!-- Adviser Approved -->
                <a href="{{ route('adviser.reservations.index', ['filter' => 'adviser_approved']) }}" class="group">
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 border border-gray-100 dark:border-gray-700 group-hover:border-blue-200 dark:group-hover:border-blue-600 group-hover:-translate-y-1">
                        <div class="flex items-center justify-between mb-4">
                            <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded-xl group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7 20h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v11a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ $adviserApprovedCount }}</div>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Adviser Approved</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Awaiting admin review</p>
                        </div>
                    </div>
                </a>

                <!-- Upcoming Approved -->
                <a href="{{ route('adviser.reservations.index', ['filter' => 'upcoming']) }}" class="group">
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 border border-gray-100 dark:border-gray-700 group-hover:border-green-200 dark:group-hover:border-green-600 group-hover:-translate-y-1">
                        <div class="flex items-center justify-between mb-4">
                            <div class="bg-green-50 dark:bg-green-900/20 p-3 rounded-xl group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ $approvedUpcomingCount }}</div>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Upcoming Approved</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Scheduled events</p>
                        </div>
                    </div>
                </a>

                <!-- Unnoticed (24h+) -->
                <a href="{{ route('adviser.reservations.index', ['filter' => 'unnoticed']) }}" class="group">
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 border border-gray-100 dark:border-gray-700 group-hover:border-red-200 dark:group-hover:border-red-600 group-hover:-translate-y-1">
                        <div class="flex items-center justify-between mb-4">
                            <div class="bg-red-50 dark:bg-red-900/20 p-3 rounded-xl group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ $unnoticedCount }}</div>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Unnoticed (24h+)</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Urgent review needed</p>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Organization Booking Requests Section -->
            @if($orgBookingPending > 0 || $orgBookingApproved > 0)
            <div class="mb-8">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Organization Booking Requests</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Pending Org Bookings -->
                    <a href="{{ route('adviser.organization-bookings.index', ['status' => 'pending']) }}" class="group">
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 border border-gray-100 dark:border-gray-700 group-hover:border-purple-200 dark:group-hover:border-purple-600 group-hover:-translate-y-1">
                            <div class="flex items-center justify-between mb-4">
                                <div class="bg-purple-50 dark:bg-purple-900/20 p-3 rounded-xl group-hover:scale-110 transition-transform duration-300">
                                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                </div>
                                <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ $orgBookingPending }}</div>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Pending Org Bookings</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Awaiting your approval</p>
                            </div>
                        </div>
                    </a>

                    <!-- Approved Org Bookings -->
                    <a href="{{ route('adviser.organization-bookings.index', ['status' => 'approved']) }}" class="group">
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 border border-gray-100 dark:border-gray-700 group-hover:border-teal-200 dark:group-hover:border-teal-600 group-hover:-translate-y-1">
                            <div class="flex items-center justify-between mb-4">
                                <div class="bg-teal-50 dark:bg-teal-900/20 p-3 rounded-xl group-hover:scale-110 transition-transform duration-300">
                                    <svg class="w-6 h-6 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ $orgBookingApproved }}</div>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Approved Org Bookings</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Ready for scheduling</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            @endif

            </div>

            <!-- Assigned Organizations -->
        </div>
    </div>
</x-app-layout>
