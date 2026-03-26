<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-heading font-bold text-lg sm:text-xl lg:text-2xl leading-tight">
                    Requestor Dashboard
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-4 sm:py-6 lg:py-8 bg-gradient-to-br from-blue-50/30 to-purple-50/30 dark:from-gray-900 dark:to-gray-800 min-h-screen">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 mb-4 sm:mb-8">
                <!-- Left Column - Welcome and Quick Tips -->
                <div class="space-y-4 sm:space-y-6">
                    <!-- Welcome Section - No Card Background -->
                    <div>
                        <div class="mb-2 sm:mb-4">
                            <p class="text-sm sm:text-lg text-gray-600 dark:text-gray-400 mb-0.5 sm:mb-1">Welcome,</p>
                            <h3 class="text-xl sm:text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white break-words word-break">
                                {{ Auth::user()->full_name }}
                            </h3>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 text-sm sm:text-base leading-relaxed">
                            Manage your requests, review updates, and stay connected with the community.
                        </p>
                    </div>

                    <!-- Quick Tips -->
                    <div class="bg-gradient-to-br from-yellow-50 to-amber-50 dark:from-yellow-900/10 dark:to-amber-900/10 rounded-2xl sm:rounded-3xl p-4 sm:p-6 border border-yellow-200 dark:border-yellow-800/30">
                        <div class="flex items-center gap-2 sm:gap-3 mb-3 sm:mb-4">
                            <div class="w-8 h-8 sm:w-10 sm:h-10 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg sm:rounded-xl flex items-center justify-center">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <span class="font-bold text-yellow-900 dark:text-yellow-200 text-sm sm:text-lg">Quick Tips</span>
                        </div>
                        <ul class="text-xs sm:text-sm text-yellow-800 dark:text-yellow-300 space-y-1.5 sm:space-y-2.5 ml-1">
                            <li class="flex items-start">
                                <span class="mr-1.5 sm:mr-2">•</span>
                                <span>Submit requests at least 7 days in advance</span>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-1.5 sm:mr-2">•</span>
                                <span>Check your email regularly for updates</span>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-1.5 sm:mr-2">•</span>
                                <span>Respond to confirmations promptly</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Right Column - Action Buttons and Events -->
                <div class="lg:col-span-2 space-y-4 sm:space-y-6">
                    <!-- Action Buttons -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <a href="{{ route('requestor.reservations.create') }}" class="group inline-flex items-center justify-center gap-2 sm:gap-3 px-4 sm:px-6 py-4 sm:py-5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl sm:rounded-3xl shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all duration-300">
                            <div class="bg-white/20 p-1.5 sm:p-2 rounded-lg sm:rounded-xl group-hover:bg-white/30 group-hover:scale-110 transition-all duration-300">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                                </svg>
                            </div>
                            <span class="text-sm sm:text-base font-semibold">New Service Request</span>
                        </a>
                        <a href="{{ route('requestor.organization-bookings.create') }}" class="group inline-flex items-center justify-center gap-2 sm:gap-3 px-4 sm:px-6 py-4 sm:py-5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl sm:rounded-3xl shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all duration-300">
                            <div class="bg-white/20 p-1.5 sm:p-2 rounded-lg sm:rounded-xl group-hover:bg-white/30 group-hover:scale-110 transition-all duration-300">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                                </svg>
                            </div>
                            <span class="text-sm sm:text-base font-semibold">Book Organization Service</span>
                        </a>
                    </div>

                    <!-- Upcoming Events Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        @php
                            $nextReservation = \App\Models\Reservation::where('user_id', auth()->id())
                                ->whereIn('status', ['approved', 'confirmed'])
                                ->where('schedule_date', '>=', now())
                                ->orderBy('schedule_date')
                                ->first();

                            $nextOrgBooking = \App\Models\OrganizationBookingRequest::where('requestor_id', auth()->id())
                                ->where('status', 'approved')
                                ->where('requested_date', '>=', now())
                                ->orderBy('requested_date')
                                ->first();
                        @endphp

                        <!-- Service Reservation Card -->
                        @if($nextReservation)
                            <a href="{{ route('requestor.reservations.show', $nextReservation->reservation_id) }}" class="group bg-white dark:bg-gray-800 rounded-2xl sm:rounded-3xl p-4 sm:p-6 shadow-lg border border-gray-100 dark:border-gray-700 hover:shadow-2xl hover:border-purple-200 dark:hover:border-purple-700 transform hover:scale-[1.02] transition-all duration-300">
                                <div class="flex flex-col items-center text-center space-y-2 sm:space-y-4">
                                    <div class="inline-flex items-center justify-center w-12 h-12 sm:w-16 sm:h-16 bg-purple-100 dark:bg-purple-900/30 rounded-xl sm:rounded-2xl group-hover:scale-110 transition-transform">
                                        <svg class="w-6 h-6 sm:w-8 sm:h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-[10px] sm:text-xs font-bold text-purple-600 dark:text-purple-400 uppercase tracking-wider mb-1 sm:mb-2">Upcoming Event</div>
                                        <div class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white mb-1 sm:mb-2">{{ \Carbon\Carbon::parse($nextReservation->schedule_date)->timezone(config('app.timezone'))->format('M d') }}</div>
                                        <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 font-medium">{{ \Carbon\Carbon::parse($nextReservation->schedule_date)->timezone(config('app.timezone'))->diffForHumans() }}</div>
                                    </div>
                                    <div class="inline-flex items-center px-3 py-1.5 sm:px-4 sm:py-2 bg-gradient-to-r from-green-100 to-emerald-100 dark:from-green-900/40 dark:to-emerald-900/40 text-green-700 dark:text-green-300 text-xs sm:text-sm rounded-full font-semibold">
                                        <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 bg-green-500 rounded-full mr-1.5 sm:mr-2 animate-pulse"></span>
                                        Confirmed
                                    </div>
                                </div>
                            </a>
                        @else
                            <div class="bg-white dark:bg-gray-800 rounded-2xl sm:rounded-3xl p-4 sm:p-8 shadow-lg border border-gray-100 dark:border-gray-700 flex flex-col items-center justify-center text-center space-y-2 sm:space-y-4">
                                <div class="inline-flex items-center justify-center w-12 h-12 sm:w-16 sm:h-16 bg-purple-100 dark:bg-purple-900/30 rounded-xl sm:rounded-2xl">
                                    <svg class="w-6 h-6 sm:w-8 sm:h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900 dark:text-white mb-0.5 sm:mb-1 text-sm sm:text-base">No Service Reservations Yet</h4>
                                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Click the button above to make your first reservation</p>
                                </div>
                            </div>
                        @endif

                        <!-- Organization Booking Card -->
                        @if($nextOrgBooking)
                            <a href="{{ route('requestor.organization-bookings.show', $nextOrgBooking->id) }}" class="group bg-white dark:bg-gray-800 rounded-2xl sm:rounded-3xl p-4 sm:p-6 shadow-lg border border-gray-100 dark:border-gray-700 hover:shadow-2xl hover:border-emerald-200 dark:hover:border-emerald-700 transform hover:scale-[1.02] transition-all duration-300">
                                <div class="flex flex-col items-center text-center space-y-2 sm:space-y-4">
                                    <div class="inline-flex items-center justify-center w-12 h-12 sm:w-16 sm:h-16 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl sm:rounded-2xl group-hover:scale-110 transition-transform">
                                        <svg class="w-6 h-6 sm:w-8 sm:h-8 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-3-3h-2m0 0a3 3 0 10-6 0m6 0H9m11 0v2m-6-8a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-[10px] sm:text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider mb-1 sm:mb-2">Upcoming Org Event</div>
                                        <div class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white mb-1 sm:mb-2">{{ \Carbon\Carbon::parse($nextOrgBooking->requested_date)->timezone(config('app.timezone'))->format('M d') }}</div>
                                        <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 font-medium">{{ \Carbon\Carbon::parse($nextOrgBooking->requested_date)->timezone(config('app.timezone'))->diffForHumans() }}</div>
                                    </div>
                                    <div class="inline-flex items-center px-3 py-1.5 sm:px-4 sm:py-2 bg-gradient-to-r from-emerald-100 to-teal-100 dark:from-emerald-900/40 dark:to-teal-900/40 text-emerald-700 dark:text-emerald-300 text-xs sm:text-sm rounded-full font-semibold">
                                        <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 bg-emerald-500 rounded-full mr-1.5 sm:mr-2 animate-pulse"></span>
                                        Approved
                                    </div>
                                </div>
                            </a>
                        @else
                            <div class="bg-white dark:bg-gray-800 rounded-2xl sm:rounded-3xl p-4 sm:p-8 shadow-lg border border-gray-100 dark:border-gray-700 flex flex-col items-center justify-center text-center space-y-2 sm:space-y-4">
                                <div class="inline-flex items-center justify-center w-12 h-12 sm:w-16 sm:h-16 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl sm:rounded-2xl">
                                    <svg class="w-6 h-6 sm:w-8 sm:h-8 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-3-3h-2m0 0a3 3 0 10-6 0m6 0H9m11 0v2m-6-8a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900 dark:text-white mb-0.5 sm:mb-1 text-sm sm:text-base">No Organization Bookings Yet</h4>
                                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Click the button above to book your first organization service</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            @php
                $totalReservations = \App\Models\Reservation::where('user_id', auth()->id())->count();
                $pendingCount = \App\Models\Reservation::where('user_id', auth()->id())->whereIn('status', ['pending', 'adviser_approved'])->count();
                $approvedCount = \App\Models\Reservation::where('user_id', auth()->id())->whereIn('status', ['admin_approved', 'approved', 'confirmed'])->count();
                $upcomingCount = \App\Models\Reservation::where('user_id', auth()->id())
                    ->whereIn('status', ['admin_approved', 'approved', 'confirmed'])
                    ->where('schedule_date', '>=', now())
                    ->count();

                // Organization booking stats
                $orgBookingTotal = \App\Models\OrganizationBookingRequest::where('requestor_id', auth()->id())->count();
                $orgBookingPending = \App\Models\OrganizationBookingRequest::where('requestor_id', auth()->id())->where('status', 'pending')->count();
                $orgBookingApproved = \App\Models\OrganizationBookingRequest::where('requestor_id', auth()->id())->where('status', 'approved')->count();
            @endphp



            <!-- Quick Stats Overview -->
            <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6 mb-4 sm:mb-8">
                <!-- Total Requests -->
                <a href="{{ route('requestor.reservations.index') }}" class="group">
                    <div class="bg-white dark:bg-gray-800 p-3 sm:p-6 rounded-xl sm:rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 border border-gray-100 dark:border-gray-700 group-hover:border-blue-200 dark:group-hover:border-blue-600 group-hover:-translate-y-1">
                        <div class="flex items-center justify-between">
                            <div class="min-w-0 flex-1">
                                <p class="text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-400 mb-1 sm:mb-2 truncate">Total Requests</p>
                                <p class="text-lg sm:text-3xl font-bold text-gray-900 dark:text-white">{{ $totalReservations }}</p>
                                <p class="text-xs sm:text-xs text-gray-500 dark:text-gray-400 mt-0.5 sm:mt-1 truncate">Click to view</p>
                            </div>
                            <div class="bg-blue-50 dark:bg-blue-900/20 p-2 sm:p-3 rounded-xl sm:rounded-2xl group-hover:scale-110 transition-transform duration-300 flex-shrink-0 ml-2">
                                <svg class="w-4 h-4 sm:w-6 sm:h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- Pending Requests -->
                <a href="{{ route('requestor.reservations.index', ['status' => 'pending']) }}" class="group">
                    <div class="bg-white dark:bg-gray-800 p-3 sm:p-6 rounded-xl sm:rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 border border-gray-100 dark:border-gray-700 group-hover:border-orange-200 dark:group-hover:border-orange-600 group-hover:-translate-y-1">
                        <div class="flex items-center justify-between">
                            <div class="min-w-0 flex-1">
                                <p class="text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-400 mb-1 sm:mb-2 truncate">Pending</p>
                                <p class="text-lg sm:text-3xl font-bold text-gray-900 dark:text-white">{{ $pendingCount }}</p>
                                <p class="text-xs sm:text-xs text-gray-500 dark:text-gray-400 mt-0.5 sm:mt-1 truncate">Awaiting</p>
                            </div>
                            <div class="bg-orange-50 dark:bg-orange-900/20 p-2 sm:p-3 rounded-xl sm:rounded-2xl group-hover:scale-110 transition-transform duration-300 flex-shrink-0 ml-2">
                                <svg class="w-4 h-4 sm:w-6 sm:h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </a>
                <!-- Approved Requests -->
                <a href="{{ route('requestor.reservations.index', ['status' => 'approved']) }}" class="group">
                    <div class="bg-white dark:bg-gray-800 p-3 sm:p-6 rounded-xl sm:rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 border border-gray-100 dark:border-gray-700 group-hover:border-green-200 dark:group-hover:border-green-600 group-hover:-translate-y-1">
                        <div class="flex items-center justify-between">
                            <div class="min-w-0 flex-1">
                                <p class="text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-400 mb-1 sm:mb-2 truncate">Approved</p>
                                <p class="text-lg sm:text-3xl font-bold text-gray-900 dark:text-white">{{ $approvedCount }}</p>
                                <p class="text-xs sm:text-xs text-gray-500 dark:text-gray-400 mt-0.5 sm:mt-1 truncate">Ready!</p>
                            </div>
                            <div class="bg-green-50 dark:bg-green-900/20 p-2 sm:p-3 rounded-xl sm:rounded-2xl group-hover:scale-110 transition-transform duration-300 flex-shrink-0 ml-2">
                                <svg class="w-4 h-4 sm:w-6 sm:h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- Upcoming Events -->
                <a href="{{ route('requestor.reservations.index', ['status' => 'upcoming']) }}" class="group">
                    <div class="bg-white dark:bg-gray-800 p-3 sm:p-6 rounded-xl sm:rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 border border-gray-100 dark:border-gray-700 group-hover:border-purple-200 dark:group-hover:border-purple-600 group-hover:-translate-y-1">
                        <div class="flex items-center justify-between">
                            <div class="min-w-0 flex-1">
                                <p class="text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-400 mb-1 sm:mb-2 truncate">Upcoming</p>
                                <p class="text-lg sm:text-3xl font-bold text-gray-900 dark:text-white">{{ $upcomingCount }}</p>
                                <p class="text-xs sm:text-xs text-gray-500 dark:text-gray-400 mt-0.5 sm:mt-1 truncate">This month</p>
                            </div>
                            <div class="bg-purple-50 dark:bg-purple-900/20 p-2 sm:p-3 rounded-xl sm:rounded-2xl group-hover:scale-110 transition-transform duration-300 flex-shrink-0 ml-2">
                                <svg class="w-4 h-4 sm:w-6 sm:h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-8">
                <!-- Recent Activity -->
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl sm:rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700">
                    <div class="p-4 sm:p-6 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="text-base sm:text-xl font-bold text-gray-900 dark:text-white flex items-center">
                            <div class="bg-blue-50 dark:bg-blue-900/20 p-1.5 sm:p-2 rounded-lg sm:rounded-2xl mr-2 sm:mr-3">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            Recent Activity
                        </h3>
                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-0.5 sm:mt-1">Your latest requests and updates</p>
                    </div>
                    <div class="p-3 sm:p-6">
                        @php
                            // Get recent reservations
                            $recentReservations = \App\Models\Reservation::with(['service', 'history'])
                                ->where('user_id', auth()->id())
                                ->orderByDesc('updated_at')
                                ->take(10)
                                ->get()
                                ->map(function($r) {
                                    return [
                                        'type' => 'reservation',
                                        'id' => $r->reservation_id,
                                        'name' => $r->activity_name,
                                        'subtitle' => ($r->service->service_name ?? 'N/A') . ' • ' . \Carbon\Carbon::parse($r->schedule_date)->timezone(config('app.timezone'))->format('M d, Y'),
                                        'status' => $r->status,
                                        'updated_at' => $r->updated_at,
                                        'route' => route('requestor.reservations.show', $r->reservation_id),
                                    ];
                                });

                            // Get recent organization booking requests
                            $recentOrgBookings = \App\Models\OrganizationBookingRequest::with(['organization'])
                                ->where('requestor_id', auth()->id())
                                ->orderByDesc('updated_at')
                                ->take(10)
                                ->get()
                                ->map(function($r) {
                                    return [
                                        'type' => 'org_booking',
                                        'id' => $r->id,
                                        'name' => $r->activity_name,
                                        'subtitle' => ($r->organization->org_name ?? 'N/A') . ' • ' . ($r->requested_date ? \Carbon\Carbon::parse($r->requested_date)->format('M d, Y') : 'Date TBD'),
                                        'status' => $r->status,
                                        'updated_at' => $r->updated_at,
                                        'route' => route('requestor.organization-bookings.show', $r->id),
                                    ];
                                });

                            // Merge and sort by updated_at
                            $recentActivity = $recentReservations->concat($recentOrgBookings)
                                ->sortByDesc('updated_at')
                                ->values();

                            $totalCount = $recentActivity->count();
                        @endphp

                        @if($recentActivity->isEmpty())
                            <div class="text-center py-16">
                                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                </div>
                                <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No activity yet</h4>
                                <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-sm mx-auto">Start your spiritual journey by creating your first request</p>
                                <a href="{{ route('requestor.reservations.create') }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold rounded-2xl shadow-lg hover:shadow-xl transition-all duration-200">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Create Your First Request
                                </a>
                            </div>
                        @else
                            <div class="space-y-4" id="recentActivityContainer">
                                @foreach($recentActivity as $index => $activity)
                                    <div class="activity-item flex items-start space-x-4 p-5 bg-gray-50 dark:bg-gray-700/50 rounded-2xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200 group {{ $index >= 3 ? 'hidden' : '' }}" data-index="{{ $index }}">
                                        <div class="flex-shrink-0">
                                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center transition-all duration-200 group-hover:scale-110
                                                {{ in_array($activity['status'], ['confirmed', 'approved', 'admin_approved', 'adviser_approved']) ? 'bg-green-100 dark:bg-green-900/30' :
                                                   ($activity['status'] === 'cancelled' || $activity['status'] === 'rejected' ? 'bg-red-100 dark:bg-red-900/30' : 'bg-orange-100 dark:bg-orange-900/30') }}">
                                                @if(in_array($activity['status'], ['confirmed', 'approved', 'admin_approved', 'adviser_approved']))
                                                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                @elseif($activity['status'] === 'cancelled' || $activity['status'] === 'rejected')
                                                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                @else
                                                    <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 mb-1">
                                                <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                                    {{ $activity['name'] }}
                                                </p>
                                                @if($activity['type'] === 'org_booking')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">
                                                        Org
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $activity['subtitle'] }}
                                            </p>
                                            <p class="text-xs text-gray-400 mt-1">
                                                {{ $activity['updated_at']->diffForHumans() }}
                                            </p>
                                        </div>
                                        <div>
                                            <a href="{{ $activity['route'] }}" class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 text-sm font-medium transition-colors">
                                                View →
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            @if($totalCount > 3)
                                <div class="mt-4 text-center">
                                    <button id="toggleActivityBtn" onclick="toggleRecentActivity()" class="inline-flex items-center px-4 py-2 text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/20 dark:hover:bg-blue-900/30 rounded-2xl transition-all duration-200">
                                        <span id="toggleBtnText">See More</span>
                                        <svg id="toggleBtnIcon" class="w-4 h-4 ml-2 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>
                                </div>
                            @endif

                            <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-700 text-center">
                                <a href="{{ route('requestor.reservations.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 font-medium">
                                    View All Reservations
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                    </svg>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Helpful Information Sidebar -->
                <div class="space-y-6">

                    <!-- Contact Support Card removed by request -->

                    <!-- Calendar Integration -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-lg border border-gray-100 dark:border-gray-700">
                        <div class="text-center">
                            <div class="bg-purple-50 dark:bg-purple-900/20 w-12 h-12 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <h4 class="font-bold text-gray-900 dark:text-white mb-2">📅 Stay Organized</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">Keep track of your upcoming events and appointments</p>
                            <a href="{{ route('requestor.reservations.calendar') }}" class="block w-full text-center bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-4 rounded-2xl transition-all duration-200 shadow-lg hover:shadow-xl">
                                View Calendar
                            </a>
                        </div>
                    </div>

                    <!-- My Reservations -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-lg border border-gray-100 dark:border-gray-700">
                        <div class="text-center">
                            <div class="bg-blue-50 dark:bg-blue-900/20 w-12 h-12 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                            <h4 class="font-bold text-gray-900 dark:text-white mb-2">My Reservations</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">Review all your submitted and upcoming reservation requests</p>
                            <a href="{{ route('requestor.reservations.index') }}" class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-2xl transition-all duration-200 shadow-lg hover:shadow-xl">
                                View My Reservations
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let isExpanded = false;

        function toggleRecentActivity() {
            const items = document.querySelectorAll('.activity-item');
            const btnText = document.getElementById('toggleBtnText');
            const btnIcon = document.getElementById('toggleBtnIcon');

            isExpanded = !isExpanded;

            items.forEach((item, index) => {
                if (index >= 3) {
                    if (isExpanded) {
                        item.classList.remove('hidden');
                        // Add a slight delay for animation effect
                        setTimeout(() => {
                            item.style.opacity = '1';
                            item.style.transform = 'translateY(0)';
                        }, index * 50);
                    } else {
                        item.classList.add('hidden');
                    }
                }
            });

            if (isExpanded) {
                btnText.textContent = 'Show Less';
                btnIcon.style.transform = 'rotate(180deg)';
            } else {
                btnText.textContent = 'See More';
                btnIcon.style.transform = 'rotate(0deg)';
            }
        }
    </script>
</x-app-layout>
