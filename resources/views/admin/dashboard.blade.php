<x-app-layout>
    <x-slot name="header">
        <h2 class="text-heading font-semibold text-base sm:text-lg lg:text-xl leading-tight">
            CREaM Admin Dashboard
        </h2>
    </x-slot>

    <div class="py-4 sm:py-6 lg:py-12">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
            <!-- Welcome Message -->
            <div class="card mb-4 sm:mb-6 rounded-2xl overflow-hidden bg-white border border-gray-200 shadow-lg">
                <div class="card-body p-3 sm:p-4 lg:p-6">
                    @php
                        $user = auth()->user();
                        $displayName = $user->first_name ?? $user->name ?? $user->email ?? 'User';

                        // Get admin's service assignments (as a priest)
                        // Count all services where admin is assigned as priest and awaiting their confirmation
                        $pendingServicesCount = \App\Models\Reservation::where('officiant_id', $user->id)
                            ->whereIn('status', ['pending_priest_confirmation', 'admin_approved', 'pending_adviser_approval'])
                            ->where(function($query) {
                                $query->whereNull('priest_confirmation')
                                      ->orWhere('priest_confirmation', '!=', 'confirmed');
                            })
                            ->count();

                        // Count upcoming confirmed services
                        $upcomingServicesCount = \App\Models\Reservation::where('officiant_id', $user->id)
                            ->where('priest_confirmation', 'confirmed')
                            ->where('status', 'confirmed')
                            ->where('schedule_date', '>=', now())
                            ->count();

                        // Get total assigned services (for display when no pending)
                        $totalAssignedServices = \App\Models\Reservation::where('officiant_id', $user->id)
                            ->whereNotIn('status', ['cancelled', 'declined'])
                            ->count();
                    @endphp

                    <div>
                        <p class="text-[10px] sm:text-xs lg:text-sm text-gray-500 mb-0.5 sm:mb-1">Welcome,</p>
                        <h3 class="text-lg sm:text-2xl lg:text-3xl font-bold text-gray-800 break-words">
                            {{ $displayName }}
                        </h3>
                    </div>
                    <p class="text-gray-600 text-xs sm:text-sm lg:text-base mt-1 sm:mt-2">Administrator Dashboard - Manage CREaM system</p>
                </div>
            </div>

            <!-- Admin Quick Actions -->
            <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-4 lg:gap-6">
                @php
                    $pendingCancellationsCount = \App\Models\ReservationCancellation::where('status', 'pending')->count();
                @endphp

                <!-- User Accounts Management -->
                <a href="{{ route('admin.users.index') }}" class="block group rounded-2xl overflow-hidden hover:shadow-lg transition-shadow duration-200">
                    <div class="card-body p-3 sm:p-4 lg:p-6 bg-white border border-gray-200 shadow-lg">
                        <div class="flex flex-col sm:flex-row items-start gap-2 sm:gap-4">
                            <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 lg:w-12 lg:h-12 bg-blue-100 rounded-lg flex items-center justify-center pointer-events-none">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0 pointer-events-none">
                                <h4 class="font-semibold text-xs sm:text-sm lg:text-lg text-gray-800 mb-0.5 sm:mb-1 truncate">Users</h4>
                                <p class="text-[10px] sm:text-xs lg:text-sm text-gray-500 truncate">Manage accounts</p>
                            </div>
                            <svg class="hidden sm:block w-4 h-4 lg:w-5 lg:h-5 text-gray-400 group-hover:text-blue-600 transition-colors pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </a>

                <!-- Cancellation Requests -->
                <a href="{{ route('admin.cancellations.index') }}" class="block group rounded-2xl overflow-hidden hover:shadow-lg transition-shadow duration-200">
                    <div class="card-body p-3 sm:p-4 lg:p-6 bg-white border border-gray-200 shadow-lg">
                        <div class="flex flex-col sm:flex-row items-start gap-2 sm:gap-4">
                            <div class="relative pointer-events-none">
                                <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 lg:w-12 lg:h-12 bg-red-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                @if($pendingCancellationsCount > 0)
                                <span data-stat="pending_cancellations" class="absolute -top-1 -right-1 sm:-top-2 sm:-right-2 px-1 sm:px-2 py-0.5 sm:py-1 bg-red-500 text-white text-[8px] sm:text-xs font-bold rounded-full">{{ $pendingCancellationsCount }}</span>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0 pointer-events-none">
                                <h4 class="font-semibold text-xs sm:text-sm lg:text-lg text-gray-800 mb-0.5 sm:mb-1 truncate">Cancellations</h4>
                                <p class="text-[10px] sm:text-xs lg:text-sm text-gray-500 truncate">
                                    @if($pendingCancellationsCount > 0)
                                        {{ $pendingCancellationsCount }} pending
                                    @else
                                        View history
                                    @endif
                                </p>
                            </div>
                            <svg class="hidden sm:block w-4 h-4 lg:w-5 lg:h-5 text-gray-400 group-hover:text-red-600 transition-colors pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </a>

                <!-- Service Assignments (Admin as Priest) - REPOSITIONED TO SECOND -->
                <a href="{{ route('admin.services.index') }}" class="block group rounded-2xl overflow-hidden hover:shadow-lg transition-shadow duration-200">
                    <div class="card-body p-3 sm:p-4 lg:p-6 bg-white border border-gray-200 shadow-lg">
                        <div class="flex flex-col sm:flex-row items-start gap-2 sm:gap-4">
                            <div class="relative pointer-events-none">
                                <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 lg:w-12 lg:h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                @if($pendingServicesCount > 0)
                                <span class="absolute -top-1 -right-1 sm:-top-2 sm:-right-2 px-1 sm:px-2 py-0.5 sm:py-1 bg-purple-500 text-white text-[8px] sm:text-xs font-bold rounded-full">{{ $pendingServicesCount }}</span>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0 pointer-events-none">
                                <h4 class="font-semibold text-xs sm:text-sm lg:text-lg text-gray-800 mb-0.5 sm:mb-1 truncate">My Services</h4>
                                <p class="text-[10px] sm:text-xs lg:text-sm text-gray-500 truncate">
                                    @if($pendingServicesCount > 0)
                                        {{ $pendingServicesCount }} pending
                                    @elseif($upcomingServicesCount > 0)
                                        {{ $upcomingServicesCount }} upcoming
                                    @else
                                        {{ $totalAssignedServices }} assigned
                                    @endif
                                </p>
                            </div>
                            <svg class="hidden sm:block w-4 h-4 lg:w-5 lg:h-5 text-gray-400 group-hover:text-purple-600 transition-colors pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </a>

                <!-- Manage Services -->
                <a href="{{ route('admin.services.manage') }}" class="block group rounded-2xl overflow-hidden hover:shadow-lg transition-shadow duration-200">
                    <div class="card-body p-3 sm:p-4 lg:p-6 bg-white border border-gray-200 shadow-lg">
                        <div class="flex flex-col sm:flex-row items-start gap-2 sm:gap-4">
                            <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 lg:w-12 lg:h-12 bg-pink-100 rounded-lg flex items-center justify-center pointer-events-none">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0 pointer-events-none">
                                <h4 class="font-semibold text-xs sm:text-sm lg:text-lg text-gray-800 mb-0.5 sm:mb-1 truncate">Manage Services</h4>
                                <p class="text-[10px] sm:text-xs lg:text-sm text-gray-500 truncate">Edit service types</p>
                            </div>
                            <svg class="hidden sm:block w-4 h-4 lg:w-5 lg:h-5 text-gray-400 group-hover:text-pink-600 transition-colors pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </a>

                <!-- All Reservations -->
                <a href="{{ route('admin.reservations.index') }}" class="block group rounded-2xl overflow-hidden hover:shadow-lg transition-shadow duration-200">
                    <div class="card-body p-3 sm:p-4 lg:p-6 bg-white border border-gray-200 shadow-lg">
                        <div class="flex flex-col sm:flex-row items-start gap-2 sm:gap-4">
                            <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 lg:w-12 lg:h-12 bg-emerald-100 rounded-lg flex items-center justify-center pointer-events-none">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0 pointer-events-none">
                                <h4 class="font-semibold text-xs sm:text-sm lg:text-lg text-gray-800 mb-0.5 sm:mb-1 truncate">Reservations</h4>
                                <p class="text-[10px] sm:text-xs lg:text-sm text-gray-500 truncate">View all requests</p>
                            </div>
                            <svg class="hidden sm:block w-4 h-4 lg:w-5 lg:h-5 text-gray-400 group-hover:text-emerald-600 transition-colors pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </a>

                <!-- Venues Management -->
                <a href="{{ route('admin.venues.index') }}" class="block group rounded-2xl overflow-hidden hover:shadow-lg transition-shadow duration-200">
                    <div class="card-body p-3 sm:p-4 lg:p-6 bg-white border border-gray-200 shadow-lg">
                        <div class="flex flex-col sm:flex-row items-start gap-2 sm:gap-4">
                            <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 lg:w-12 lg:h-12 bg-cyan-100 rounded-lg flex items-center justify-center pointer-events-none">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21V9a2 2 0 012-2h14a2 2 0 012 2v12M9 21V12h6v9" />
                                    <rect x="9" y="12" width="6" height="9" rx="1" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0 pointer-events-none">
                                <h4 class="font-semibold text-xs sm:text-sm lg:text-lg text-gray-800 mb-0.5 sm:mb-1 truncate">Venues</h4>
                                <p class="text-[10px] sm:text-xs lg:text-sm text-gray-500 truncate">Manage locations</p>
                            </div>
                            <svg class="hidden sm:block w-4 h-4 lg:w-5 lg:h-5 text-gray-400 group-hover:text-cyan-600 transition-colors pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </a>

                <!-- Notifications -->
                <a href="{{ route('admin.notifications.index') }}" class="block group rounded-2xl overflow-hidden hover:shadow-lg transition-shadow duration-200">
                    <div class="card-body p-3 sm:p-4 lg:p-6 bg-white border border-gray-200 shadow-lg">
                        <div class="flex flex-col sm:flex-row items-start gap-2 sm:gap-4">
                            <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 lg:w-12 lg:h-12 bg-amber-100 rounded-lg flex items-center justify-center pointer-events-none">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0 pointer-events-none">
                                <h4 class="font-semibold text-xs sm:text-sm lg:text-lg text-gray-800 mb-0.5 sm:mb-1 truncate">Notifications</h4>
                                <p class="text-[10px] sm:text-xs lg:text-sm text-gray-500 truncate">View alerts</p>
                            </div>
                            <svg class="hidden sm:block w-4 h-4 lg:w-5 lg:h-5 text-gray-400 group-hover:text-amber-600 transition-colors pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </a>

                <!-- Change Requests -->
                <a href="{{ route('admin.change-requests.index') }}" class="block group rounded-2xl overflow-hidden hover:shadow-lg transition-shadow duration-200">
                    <div class="card-body p-3 sm:p-4 lg:p-6 bg-white border border-gray-200 shadow-lg">
                        <div class="flex flex-col sm:flex-row items-start gap-2 sm:gap-4">
                            <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 lg:w-12 lg:h-12 bg-orange-100 rounded-lg flex items-center justify-center pointer-events-none">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5M18.5 2.5L21 5l-9.5 9.5H9v-2.5L18.5 2.5z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0 pointer-events-none">
                                <h4 class="font-semibold text-xs sm:text-sm lg:text-lg text-gray-800 mb-0.5 sm:mb-1 truncate">Changes</h4>
                                <p class="text-[10px] sm:text-xs lg:text-sm text-gray-500 truncate">Review edits</p>
                            </div>
                            <svg class="hidden sm:block w-4 h-4 lg:w-5 lg:h-5 text-gray-400 group-hover:text-orange-600 transition-colors pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </a>

                <!-- Organizations (Admin read-only list) -->
                <a href="{{ route('admin.organizations.index') }}" class="block group rounded-2xl overflow-hidden hover:shadow-lg transition-shadow duration-200">
                    <div class="card-body p-3 sm:p-4 lg:p-6 bg-white border border-gray-200 shadow-lg">
                        <div class="flex flex-col sm:flex-row items-start gap-2 sm:gap-4">
                            <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 lg:w-12 lg:h-12 bg-indigo-100 rounded-lg flex items-center justify-center pointer-events-none">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0 pointer-events-none">
                                <h4 class="font-semibold text-xs sm:text-sm lg:text-lg text-gray-800 mb-0.5 sm:mb-1 truncate">Organizations</h4>
                                <p class="text-[10px] sm:text-xs lg:text-sm text-gray-500 truncate">View advisers</p>
                            </div>
                            <svg class="hidden sm:block w-4 h-4 lg:w-5 lg:h-5 text-gray-400 group-hover:text-indigo-600 transition-colors pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </a>

                <!-- Generate Reports -->
                <a href="{{ route('reports.index') }}" class="block group rounded-2xl overflow-hidden hover:shadow-lg transition-shadow duration-200" aria-label="Open Generate Report">
                    <div class="card-body p-3 sm:p-4 lg:p-6 bg-white border border-gray-200 shadow-lg">
                        <div class="flex flex-col sm:flex-row items-start gap-2 sm:gap-4">
                            <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 lg:w-12 lg:h-12 bg-gray-100 rounded-lg flex items-center justify-center pointer-events-none">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v18h18" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 15l3-3 4 4 5-7" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0 pointer-events-none">
                                <h4 class="font-semibold text-xs sm:text-sm lg:text-lg text-gray-800 mb-0.5 sm:mb-1 truncate">Reports</h4>
                                <p class="text-[10px] sm:text-xs lg:text-sm text-gray-500 truncate">Create & download</p>
                            </div>
                            <svg class="hidden sm:block w-4 h-4 lg:w-5 lg:h-5 text-gray-400 group-hover:text-gray-600 transition-colors pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </a>

                <!-- Manage Elevated Code -->
                <a href="{{ route('admin.elevated-code.index') }}" class="block group rounded-2xl overflow-hidden hover:shadow-lg transition-shadow duration-200" aria-label="Manage Elevated Registration Code">
                    <div class="card-body p-3 sm:p-4 lg:p-6 bg-white border border-gray-200 shadow-lg">
                        <div class="flex flex-col sm:flex-row items-start gap-2 sm:gap-4">
                            <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 lg:w-12 lg:h-12 bg-fuchsia-100 rounded-lg flex items-center justify-center pointer-events-none">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6 text-fuchsia-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0 pointer-events-none">
                                <h4 class="font-semibold text-xs sm:text-sm lg:text-lg text-gray-800 mb-0.5 sm:mb-1 truncate">Elevated Code</h4>
                                <p class="text-[10px] sm:text-xs lg:text-sm text-gray-500 truncate">Update reg code</p>
                            </div>
                            <svg class="hidden sm:block w-4 h-4 lg:w-5 lg:h-5 text-gray-400 group-hover:text-fuchsia-600 transition-colors pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
