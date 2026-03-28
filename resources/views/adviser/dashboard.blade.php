<x-app-layout>
    <x-slot name="header">
        <h2 class="text-heading font-semibold text-lg sm:text-xl leading-tight">
            CREaM Adviser Dashboard
        </h2>
    </x-slot>

    <div class="py-4 sm:py-6 lg:py-8">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
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
                $orgBookingPending = \App\Models\OrganizationBookingRequest::where(function($q) use ($orgIds) {
                        $q->whereIn('organization_id', $orgIds)
                          ->orWhereHas('organizations', function($sq) use ($orgIds) {
                              $sq->whereIn('organization_id', $orgIds);
                          });
                    })
                    ->where('status', 'pending')
                    ->count();

                $orgBookingApproved = \App\Models\OrganizationBookingRequest::where(function($q) use ($orgIds) {
                        $q->whereIn('organization_id', $orgIds)
                          ->orWhereHas('organizations', function($sq) use ($orgIds) {
                              $sq->whereIn('organization_id', $orgIds);
                          });
                    })
                    ->where('status', 'approved')
                    ->count();

                // Pending Cancellations
                $cancellationCount = \App\Models\ReservationCancellation::whereHas('reservation', function($q) use ($orgIds) {
                        $q->whereIn('org_id', $orgIds)
                          ->orWhereHas('organizations', function($sq) use ($orgIds) {
                              $sq->whereIn('organizations.org_id', $orgIds);
                          });
                    })
                    ->whereNotIn('status', ['rejected', 'completed'])
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
            <div class="bg-white border border-gray-200 rounded-2xl p-4 sm:p-6 lg:p-8 mb-4 sm:mb-8 shadow-lg">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 sm:gap-6">
                    <div class="flex-1 min-w-0">
                    <div>
                        <p class="text-xs sm:text-sm text-gray-500 mb-0.5 sm:mb-1">Welcome,</p>
                        <h3 class="text-xl sm:text-3xl lg:text-4xl font-bold text-gray-800 break-words">
                            {{ $displayName }}
                        </h3>
                    </div>
                    <p class="text-gray-600 text-sm sm:text-lg leading-relaxed mt-1.5 sm:mt-2">
                        Review and triage reservation requests for your assigned organizations.
                    </p>
                    </div>
                    @if($nextService)
                        <div class="bg-gray-100 rounded-2xl p-4 sm:p-6 shadow-sm w-full sm:w-auto sm:min-w-[180px] lg:min-w-[220px]">
                            <div class="text-center">
                                <div class="text-[10px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 sm:mb-2">NEXT SERVICE</div>
                                <div class="text-xl sm:text-3xl font-bold text-gray-800 mb-0.5 sm:mb-1">{{ $nextService->schedule_date->format('M d') }}</div>
                                <div class="text-xs sm:text-sm text-gray-500 mb-2 sm:mb-3">
                                    @if($daysUntilService === 0)
                                        Today
                                    @elseif($daysUntilService === 1)
                                        Tomorrow
                                    @elseif($daysUntilService > 1)
                                        {{ $daysUntilService }} days
                                    @elseif($daysUntilService < 0)
                                        {{ abs($daysUntilService) }} days ago
                                    @endif
                                </div>
                                <div class="inline-block bg-green-100 text-green-700 text-[10px] sm:text-xs font-medium px-2 sm:px-3 py-0.5 sm:py-1 rounded-full">
                                    Confirmed
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-gray-100 rounded-2xl p-4 sm:p-6 shadow-sm w-full sm:w-auto sm:min-w-[180px] lg:min-w-[220px]">
                            <div class="text-center">
                                <div class="text-[10px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 sm:mb-2">NEXT SERVICE</div>
                                <div class="text-sm sm:text-lg font-semibold text-gray-800 mb-1 sm:mb-2">No Events</div>
                                <div class="text-xs sm:text-sm text-gray-500">Pending review</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Main Workflow Cards -->
            <div class="mb-4 sm:mb-8">
                <h3 class="text-base sm:text-lg lg:text-xl font-bold text-gray-900 dark:text-white mb-3 sm:mb-4">Reservation Management</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 lg:gap-6">
                    <!-- Pending Requests -->
                    <a href="{{ route('adviser.reservations.index', ['filter' => 'pending']) }}" class="group rounded-2xl overflow-hidden">
                        <div class="bg-white border border-gray-200 shadow-lg hover:shadow-xl transition-all duration-200 p-3 sm:p-4 lg:p-6">
                            <div class="flex items-center gap-3 sm:gap-4">
                                <div class="flex-shrink-0 w-10 h-10 sm:w-12 sm:h-12 bg-amber-100 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <h4 class="font-semibold text-base sm:text-lg text-gray-800 truncate">Pending</h4>
                                        <span data-stat="pending_bookings" class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold bg-amber-100 text-amber-700 rounded-full min-w-[24px] h-6">{{ $pendingCount }}</span>
                                    </div>
                                    <p class="text-xs sm:text-sm text-gray-500">Requests awaiting your review</p>
                                </div>
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400 group-hover:text-amber-600 group-hover:translate-x-1 transition-all flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>
                    </a>

                    <!-- Cancellation Requests -->
                    <a href="{{ route('adviser.cancellations.index') }}" class="group rounded-2xl overflow-hidden">
                        <div class="bg-white border border-gray-200 shadow-lg hover:shadow-xl transition-all duration-200 p-3 sm:p-4 lg:p-6">
                            <div class="flex items-center gap-3 sm:gap-4">
                                <div class="relative">
                                    <div class="flex-shrink-0 w-10 h-10 sm:w-12 sm:h-12 bg-red-100 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    @if($cancellationCount > 0)
                                        <span data-stat="pending_cancellations" class="absolute -top-1.5 -right-1.5 sm:-top-2 sm:-right-2 px-1.5 py-0.5 sm:px-2 sm:py-1 bg-red-500 text-white text-[10px] sm:text-xs font-bold rounded-full">{{ $cancellationCount }}</span>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-semibold text-base sm:text-lg text-gray-800 mb-1">Cancellations</h4>
                                    <p class="text-xs sm:text-sm text-gray-500">
                                        @if($cancellationCount > 0)
                                            {{ $cancellationCount }} awaiting confirmation
                                        @else
                                            No pending cancellations
                                        @endif
                                    </p>
                                </div>
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400 group-hover:text-red-600 group-hover:translate-x-1 transition-all flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>
                    </a>

                    <!-- Adviser Approved -->
                    <a href="{{ route('adviser.reservations.index', ['filter' => 'adviser_approved']) }}" class="group rounded-2xl overflow-hidden">
                        <div class="bg-white border border-gray-200 shadow-lg hover:shadow-xl transition-all duration-200 p-3 sm:p-4 lg:p-6">
                            <div class="flex items-center gap-3 sm:gap-4">
                                <div class="flex-shrink-0 w-10 h-10 sm:w-12 sm:h-12 bg-blue-100 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7 20h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v11a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <h4 class="font-semibold text-base sm:text-lg text-gray-800 truncate">Approved</h4>
                                        <span data-stat="approved_bookings" class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold bg-blue-100 text-blue-700 rounded-full min-w-[24px] h-6">{{ $adviserApprovedCount }}</span>
                                    </div>
                                    <p class="text-xs sm:text-sm text-gray-500">Awaiting admin approval</p>
                                </div>
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400 group-hover:text-blue-600 group-hover:translate-x-1 transition-all flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>
                    </a>

                    <!-- Upcoming Approved -->
                    <a href="{{ route('adviser.reservations.index', ['filter' => 'upcoming']) }}" class="group rounded-2xl overflow-hidden">
                        <div class="bg-white border border-gray-200 shadow-lg hover:shadow-xl transition-all duration-200 p-3 sm:p-4 lg:p-6">
                            <div class="flex items-center gap-3 sm:gap-4">
                                <div class="flex-shrink-0 w-10 h-10 sm:w-12 sm:h-12 bg-emerald-100 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <h4 class="font-semibold text-base sm:text-lg text-gray-800 truncate">Upcoming</h4>
                                        <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold bg-emerald-100 text-emerald-700 rounded-full min-w-[24px] h-6">{{ $approvedUpcomingCount }}</span>
                                    </div>
                                    <p class="text-xs sm:text-sm text-gray-500">Scheduled services</p>
                                </div>
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400 group-hover:text-emerald-600 group-hover:translate-x-1 transition-all flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>
                    </a>

                    <!-- Unnoticed (24h+) -->
                    <a href="{{ route('adviser.reservations.index', ['filter' => 'unnoticed']) }}" class="group rounded-2xl overflow-hidden">
                        <div class="bg-white border border-gray-200 shadow-lg hover:shadow-xl transition-all duration-200 p-3 sm:p-4 lg:p-6">
                            <div class="flex items-center gap-3 sm:gap-4">
                                <div class="flex-shrink-0 w-10 h-10 sm:w-12 sm:h-12 bg-gray-200 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <h4 class="font-semibold text-base sm:text-lg text-gray-800 truncate">Unnoticed</h4>
                                        <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold bg-gray-200 text-gray-700 rounded-full min-w-[24px] h-6">{{ $unnoticedCount }}</span>
                                    </div>
                                    <p class="text-xs sm:text-sm text-gray-500">Require urgent review</p>
                                </div>
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400 group-hover:text-gray-600 group-hover:translate-x-1 transition-all flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Organization Booking Services Section -->
            @php
                // Get recent organization bookings for this adviser
                $recentOrgBookings = \App\Models\OrganizationBookingRequest::with(['requestor', 'organization', 'organizations'])
                    ->where(function ($q) use ($orgIds) {
                        $q->whereIn('organization_id', $orgIds)
                          ->orWhereHas('organizations', function ($subQ) use ($orgIds) {
                              $subQ->whereIn('organization_id', $orgIds);
                          });
                    })
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();
            @endphp
            <div class="mb-4 sm:mb-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 mb-3 sm:mb-4">
                    <h3 class="text-base sm:text-lg lg:text-xl font-bold text-gray-900 dark:text-white">Organization Booking Services</h3>
                    <a href="{{ route('adviser.organization-bookings.index') }}" class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-purple-600 hover:text-purple-700 hover:bg-purple-50 rounded-lg transition-colors">
                        View All
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 lg:gap-6 mb-4 sm:mb-6">
                    <!-- Pending Org Bookings -->
                    <a href="{{ route('adviser.organization-bookings.index', ['status' => 'pending']) }}" class="group rounded-2xl overflow-hidden">
                        <div class="bg-white border border-gray-200 shadow-lg hover:shadow-xl transition-all duration-200 p-3 sm:p-4 lg:p-6">
                            <div class="flex items-center gap-3 sm:gap-4">
                                <div class="flex-shrink-0 w-10 h-10 sm:w-12 sm:h-12 bg-purple-100 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <h4 class="font-semibold text-base sm:text-lg text-gray-800 truncate">Pending Requests</h4>
                                        <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold bg-purple-100 text-purple-700 rounded-full min-w-[24px] h-6">{{ $orgBookingPending }}</span>
                                    </div>
                                    <p class="text-xs sm:text-sm text-gray-500">Awaiting your review</p>
                                </div>
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400 group-hover:text-purple-600 group-hover:translate-x-1 transition-all flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>
                    </a>

                    <!-- Approved Org Bookings -->
                    <a href="{{ route('adviser.organization-bookings.index', ['status' => 'approved']) }}" class="group rounded-2xl overflow-hidden">
                        <div class="bg-white border border-gray-200 shadow-lg hover:shadow-xl transition-all duration-200 p-3 sm:p-4 lg:p-6">
                            <div class="flex items-center gap-3 sm:gap-4">
                                <div class="flex-shrink-0 w-10 h-10 sm:w-12 sm:h-12 bg-teal-100 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <h4 class="font-semibold text-base sm:text-lg text-gray-800 truncate">Approved Bookings</h4>
                                        <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold bg-teal-100 text-teal-700 rounded-full min-w-[24px] h-6">{{ $orgBookingApproved }}</span>
                                    </div>
                                    <p class="text-xs sm:text-sm text-gray-500">Ready to be scheduled</p>
                                </div>
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400 group-hover:text-teal-600 group-hover:translate-x-1 transition-all flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Recent Booking Requests List -->
                @if($recentOrgBookings->count() > 0)
                <div class="bg-white border border-gray-200 rounded-2xl shadow-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Activity</th>
                                    <th class="px-4 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Requestor</th>
                                    <th class="hidden md:table-cell px-4 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Organizations</th>
                                    <th class="px-4 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Date</th>
                                    <th class="px-4 sm:px-6 py-3 sm:py-4 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                                    <th class="px-4 sm:px-6 py-3 sm:py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($recentOrgBookings as $booking)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <!-- Activity Name -->
                                    <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ Str::limit($booking->activity_name, 30) }}</div>
                                        <div class="text-xs text-gray-500 mt-0.5">{{ $booking->purpose ? Str::limit($booking->purpose, 25) : '—' }}</div>
                                    </td>

                                    <!-- Requestor Name -->
                                    <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-800">{{ $booking->requestor->first_name ?? 'Unknown' }}</div>
                                        <div class="text-xs text-gray-500">{{ $booking->requestor->email ?? '—' }}</div>
                                    </td>

                                    <!-- Organizations (hidden on mobile) -->
                                    <td class="hidden md:table-cell px-4 sm:px-6 py-3 sm:py-4">
                                        <div class="flex flex-wrap gap-1">
                                            @php
                                                $organizations = $booking->organizations->count() > 0 ? $booking->organizations : ($booking->organization ? [$booking->organization] : []);
                                            @endphp
                                            @foreach($organizations->take(2) as $org)
                                                <span class="inline-block px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded">
                                                    {{ Str::limit($org->org_name, 15) }}
                                                </span>
                                            @endforeach
                                            @if($organizations->count() > 2)
                                                <span class="inline-block px-2 py-1 text-xs font-medium bg-gray-100 text-gray-700 rounded">
                                                    +{{ $organizations->count() - 2 }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Requested Date -->
                                    <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $booking->requested_date?->format('M d, Y') ?? '—' }}
                                    </td>

                                    <!-- Status Badge -->
                                    <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-right">
                                        @switch($booking->status)
                                            @case('pending')
                                                <span class="inline-block px-3 py-1 text-xs font-semibold text-amber-700 bg-amber-100 rounded-full">Pending</span>
                                                @break
                                            @case('approved')
                                                <span class="inline-block px-3 py-1 text-xs font-semibold text-teal-700 bg-teal-100 rounded-full">Approved</span>
                                                @break
                                            @case('rejected')
                                                <span class="inline-block px-3 py-1 text-xs font-semibold text-red-700 bg-red-100 rounded-full">Rejected</span>
                                                @break
                                            @default
                                                <span class="inline-block px-3 py-1 text-xs font-semibold text-gray-700 bg-gray-100 rounded-full">{{ ucfirst($booking->status) }}</span>
                                        @endswitch
                                    </td>

                                    <!-- Action Button -->
                                    <td class="px-4 sm:px-6 py-3 sm:py-4 text-center whitespace-nowrap">
                                        <a href="{{ route('adviser.organization-bookings.show', $booking->id) }}" class="inline-flex items-center gap-1 px-3 py-2 text-xs font-medium text-purple-600 hover:text-purple-700 hover:bg-purple-50 rounded-lg transition-colors">
                                            <span class="hidden sm:inline">View</span>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @else
                <div class="bg-white border border-gray-200 rounded-2xl p-6 sm:p-8 text-center">
                    <div class="flex justify-center mb-4">
                        <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                    </div>
                    <p class="text-gray-500 text-sm">No organization booking requests yet.</p>
                    <p class="text-gray-400 text-xs mt-1">Booking requests from requestors will appear here.</p>
                </div>
                @endif
            </div>

            </div>

            <!-- Assigned Organizations -->
        </div>
    </div>
</x-app-layout>
