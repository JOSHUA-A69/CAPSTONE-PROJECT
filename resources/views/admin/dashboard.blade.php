<x-app-layout>
    <x-slot name="header">
        <h2 class="text-heading font-semibold text-xl leading-tight">
            CREaM Administrator Dashboard
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Welcome Message -->
            <div class="card mb-6">
                <div class="card-body">
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

                    <h3 class="text-2xl font-bold mb-2 text-heading">Welcome, {{ $displayName }}! 🔧</h3>
                    <p class="text-muted">Administrator Dashboard - Manage the entire CREaM system</p>
                </div>
            </div>

            <!-- Admin Quick Actions -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @php
                    $pendingCancellationsCount = \App\Models\ReservationCancellation::where('status', 'pending')->count();
                @endphp

                <!-- User Accounts Management -->
                <a href="{{ route('admin.users.index') }}" class="card-hover group">
                    <div class="card-body">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-lg text-heading mb-1">User Accounts</h4>
                                <p class="text-sm text-muted">Manage all system users and permissions</p>
                            </div>
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </a>

                <!-- Cancellation Requests -->
                <a href="{{ route('admin.cancellations.index') }}" class="card-hover group">
                    <div class="card-body">
                        <div class="flex items-start gap-4">
                            <div class="relative">
                                <div class="flex-shrink-0 w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                @if($pendingCancellationsCount > 0)
                                <span class="absolute -top-2 -right-2 px-2 py-1 bg-red-500 text-white text-xs font-bold rounded-full">{{ $pendingCancellationsCount }}</span>
                                @endif
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-lg text-heading mb-1">Cancellations</h4>
                                <p class="text-sm text-muted">
                                    @if($pendingCancellationsCount > 0)
                                        {{ $pendingCancellationsCount }} pending confirmation
                                    @else
                                        View recent and completed
                                    @endif
                                </p>
                            </div>
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-red-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </a>

                <!-- Service Assignments (Admin as Priest) - REPOSITIONED TO SECOND -->
                <a href="{{ route('admin.services.index') }}" class="card-hover group">
                    <div class="card-body">
                        <div class="flex items-start gap-4">
                            <div class="relative">
                                <div class="flex-shrink-0 w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                @if($pendingServicesCount > 0)
                                <span class="absolute -top-2 -right-2 px-2 py-1 bg-yellow-500 text-white text-xs font-bold rounded-full">{{ $pendingServicesCount }}</span>
                                @endif
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-lg text-heading mb-1">My Services</h4>
                                <p class="text-sm text-muted">
                                    @if($pendingServicesCount > 0)
                                        {{ $pendingServicesCount }} pending confirmation
                                    @elseif($upcomingServicesCount > 0)
                                        {{ $upcomingServicesCount }} upcoming services
                                    @else
                                        {{ $totalAssignedServices }} assigned services
                                    @endif
                                </p>
                            </div>
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-purple-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </a>

                <!-- Manage Services -->
                <a href="{{ route('admin.services.manage') }}" class="card-hover group">
                    <div class="card-body">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-pink-100 dark:bg-pink-900/30 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-pink-600 dark:text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-lg text-heading mb-1">Manage Services</h4>
                                <p class="text-sm text-muted">Edit, delete & update service types</p>
                            </div>
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-pink-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </a>

                <!-- All Reservations -->
                <a href="{{ route('admin.reservations.index') }}" class="card-hover group">
                    <div class="card-body">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-lg text-heading mb-1">All Reservations</h4>
                                <p class="text-sm text-muted">View all system reservations and requests</p>
                            </div>
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-green-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </a>

                <!-- Venues Management -->
                <a href="{{ route('admin.venues.index') }}" class="card-hover group">
                    <div class="card-body">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-teal-100 dark:bg-teal-900/30 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21V9a2 2 0 012-2h14a2 2 0 012 2v12M9 21V12h6v9" />
                                    <rect x="9" y="12" width="6" height="9" rx="1" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-lg text-heading mb-1">Venues</h4>
                                <p class="text-sm text-muted">Manage all event venues and locations</p>
                            </div>
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-teal-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </a>

                <!-- Notifications -->
                <a href="{{ route('admin.notifications.index') }}" class="card-hover group">
                    <div class="card-body">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-lg text-heading mb-1">Notifications</h4>
                                <p class="text-sm text-muted">View system notifications and alerts</p>
                            </div>
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-yellow-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </a>

                <!-- Change Requests -->
                <a href="{{ route('admin.change-requests.index') }}" class="card-hover group">
                    <div class="card-body">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5M18.5 2.5L21 5l-9.5 9.5H9v-2.5L18.5 2.5z" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-lg text-heading mb-1">Change Requests</h4>
                                <p class="text-sm text-muted">Review and approve or reject requested edits</p>
                            </div>
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-orange-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </a>

                <!-- Organizations (Admin read-only list) -->
                <a href="{{ route('admin.organizations.index') }}" class="card-hover group">
                    <div class="card-body">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-lg text-heading mb-1">Organizations</h4>
                                <p class="text-sm text-muted">View organizations and advisers</p>
                            </div>
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-indigo-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </a>

                <!-- Generate Reports -->
                <a href="{{ route('reports.index') }}" class="card-hover group bg-gray-50 dark:bg-gray-800" aria-label="Open Generate Report">
                    <div class="card-body">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-gray-200 dark:bg-gray-700 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v18h18" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 15l3-3 4 4 5-7" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-lg text-heading mb-1">Generate Report</h4>
                                <p class="text-sm text-muted">Create and download quarterly, statistics, and more</p>
                            </div>
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </a>

                <!-- Manage Elevated Code -->
                <a href="{{ route('admin.elevated-code.index') }}" class="card-hover group" aria-label="Manage Elevated Registration Code">
                    <div class="card-body">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-pink-100 dark:bg-pink-900/30 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-pink-600 dark:text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-lg text-heading mb-1">Manage Elevated Code</h4>
                                <p class="text-sm text-muted">Update registration code for elevated roles</p>
                            </div>
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-pink-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
