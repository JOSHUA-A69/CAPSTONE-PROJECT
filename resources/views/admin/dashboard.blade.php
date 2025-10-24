<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">CREaM Administrator Dashboard</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Welcome Card -->
            <div class="mb-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @php
                        $user = auth()->user();
                        $displayName = $user->first_name ?? $user->name ?? $user->email ?? 'User';
                        $userCount = \App\Models\User::count();
                        $pendingApprovalCount = class_exists('App\\Models\\Reservation')
                            ? \App\Models\Reservation::where('status', 'adviser_approved')->count()
                            : 0;
                    @endphp

                    <h3 class="text-2xl font-bold mb-2">Welcome, {{ $displayName }}!</h3>
                    <p class="text-gray-600 dark:text-gray-400">Oversee users and approvals across the system.</p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Pending Admin Approvals -->
                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-800/20 overflow-hidden shadow-sm sm:rounded-lg border border-yellow-200 dark:border-yellow-800">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-yellow-800 dark:text-yellow-300 mb-1">Pending Admin Approvals</p>
                                <p class="text-4xl font-bold text-yellow-900 dark:text-yellow-200">{{ $pendingApprovalCount }}</p>
                            </div>
                            <div class="p-3 bg-yellow-200 dark:bg-yellow-700 rounded-full">
                                <svg class="w-8 h-8 text-yellow-700 dark:text-yellow-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <a href="{{ route('admin.notifications.index') }}"
                           class="mt-4 inline-flex items-center text-sm font-semibold text-yellow-700 dark:text-yellow-300 hover:underline">
                            Review Notifications
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Total Users -->
                <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 dark:from-indigo-900/20 dark:to-indigo-800/20 overflow-hidden shadow-sm sm:rounded-lg border border-indigo-200 dark:border-indigo-800">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-indigo-800 dark:text-indigo-300 mb-1">Total Users</p>
                                <p class="text-4xl font-bold text-indigo-900 dark:text-indigo-200">{{ $userCount }}</p>
                            </div>
                            <div class="p-3 bg-indigo-200 dark:bg-indigo-700 rounded-full">
                                <svg class="w-8 h-8 text-indigo-700 dark:text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5V4H2v16h5m10 0V10M7 20V10m5 10V10"></path>
                                </svg>
                            </div>
                        </div>
                        <a href="{{ route('admin.users.index') }}"
                           class="mt-4 inline-flex items-center text-sm font-semibold text-indigo-700 dark:text-indigo-300 hover:underline">
                            Manage Users
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            @php
                $pendingApprovals = class_exists('App\\Models\\Reservation')
                    ? \App\Models\Reservation::where('status', 'adviser_approved')->latest()->take(2)->get()
                    : collect();
                $recentUsers = class_exists('App\\Models\\User')
                    ? \App\Models\User::orderBy('created_at', 'desc')->take(2)->get()
                    : collect();
                $processedToday = class_exists('App\\Models\\Reservation')
                    ? \App\Models\Reservation::whereDate('updated_at', now()->toDateString())->whereIn('status', ['approved','rejected','cancelled','admin_approved'])->latest()->take(2)->get()
                    : collect();
                $recentNotifications = class_exists('App\\Models\\Notification')
                    ? \App\Models\Notification::where('user_id', auth()->id())->orderBy('sent_at','desc')->take(2)->get()
                    : collect();
            @endphp

            <!-- Reference-styled Cards Grid (Admin) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Approval Queue -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <h4 class="text-xl font-semibold">Approval Queue</h4>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">Requests waiting for admin action</p>

                        @forelse($pendingApprovals as $r)
                            <div class="mb-3 flex items-center justify-between rounded-lg border border-gray-200 dark:border-gray-700 px-4 py-3 bg-gray-50 dark:bg-gray-900/40">
                                <div>
                                    <div class="font-medium text-gray-800 dark:text-gray-100">{{ $r->service_type ?? 'Reservation' }}</div>
                                    <div class="text-xs text-gray-500">Submitted {{ optional($r->created_at)->diffForHumans() }}</div>
                                </div>
                                <span class="text-xs px-2 py-1 rounded-full bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300">Awaiting</span>
                            </div>
                        @empty
                            <div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-700 p-4 text-sm text-gray-500">No pending approvals.</div>
                        @endforelse

                        <a href="{{ route('admin.notifications.index') }}" class="mt-4 inline-flex w-full justify-center items-center rounded-md bg-[var(--er-green)] text-white py-2.5 font-semibold hover:opacity-95">Review Notifications</a>
                    </div>
                </div>

                <!-- User Accounts -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5V4H2v16h5m10 0V10M7 20V10m5 10V10"/></svg>
                            <h4 class="text-xl font-semibold">User Accounts</h4>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">Recently added users</p>

                        @forelse($recentUsers as $u)
                            <div class="mb-3 rounded-lg border border-gray-200 dark:border-gray-700 px-4 py-3 bg-gray-50 dark:bg-gray-900/40">
                                <div class="font-medium text-gray-800 dark:text-gray-100">{{ $u->full_name ?? $u->name ?? $u->email }}</div>
                                <div class="text-xs text-gray-500">Joined {{ optional($u->created_at)->diffForHumans() }}</div>
                            </div>
                        @empty
                            <div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-700 p-4 text-sm text-gray-500">No users yet.</div>
                        @endforelse

                        <a href="{{ route('admin.users.index') }}" class="mt-4 inline-flex w-full justify-center items-center rounded-md bg-[var(--er-green)] text-white py-2.5 font-semibold hover:opacity-95">Manage Users</a>
                    </div>
                </div>

                <!-- Event Calendar -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <h4 class="text-xl font-semibold">Event Calendar</h4>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">Upcoming services and events</p>
                        <div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-700 p-4 text-sm text-gray-500">Calendar integration coming soon.</div>
                        <a href="#" class="mt-4 inline-flex w-full justify-center items-center rounded-md bg-[var(--er-green)] text-white py-2.5 font-semibold hover:opacity-95">View Calendar</a>
                    </div>
                </div>

                <!-- Processed Today -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <h4 class="text-xl font-semibold">Processed Today</h4>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">Recently acted-on requests</p>

                        @forelse($processedToday as $r)
                            <div class="mb-3 flex items-center justify-between rounded-lg border border-gray-200 dark:border-gray-700 px-4 py-3 bg-gray-50 dark:bg-gray-900/40">
                                <div>
                                    <div class="font-medium text-gray-800 dark:text-gray-100">{{ $r->service_type ?? 'Reservation' }}</div>
                                    <div class="text-xs text-gray-500">{{ ucfirst($r->status ?? 'processed') }} • {{ optional($r->updated_at)->diffForHumans() }}</div>
                                </div>
                                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                        @empty
                            <div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-700 p-4 text-sm text-gray-500">Nothing processed yet today.</div>
                        @endforelse

                        <a href="{{ route('admin.reservations.index') }}" class="mt-4 inline-flex w-full justify-center items-center rounded-md bg-[var(--er-green)] text-white py-2.5 font-semibold hover:opacity-95">View All</a>
                    </div>
                </div>

                <!-- Notifications -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <h4 class="text-xl font-semibold">Notifications</h4>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">Recent system updates</p>

                        @forelse($recentNotifications as $n)
                            <div class="mb-3 rounded-lg border border-gray-200 dark:border-gray-700 px-4 py-3 bg-gray-50 dark:bg-gray-900/40">
                                <div class="font-medium text-gray-800 dark:text-gray-100">{!! $n->message ?? ($n->type ?? 'Notification') !!}</div>
                                <div class="text-xs text-gray-500">{{ optional($n->sent_at ?? $n->created_at)->diffForHumans() }}</div>
                            </div>
                        @empty
                            <div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-700 p-4 text-sm text-gray-500">No notifications yet.</div>
                        @endforelse

                        <a href="{{ route('admin.notifications.index') }}" class="mt-4 inline-flex w-full justify-center items-center rounded-md bg-[var(--er-green)] text-white py-2.5 font-semibold hover:opacity-95">View All</a>
                    </div>
                </div>

                <!-- Your Profile -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.89 0 5.558.915 7.879 2.463M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <h4 class="text-xl font-semibold">Your Profile</h4>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">Update profile and password</p>
                        <div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-700 p-4 text-sm text-gray-500">Keep your details up to date.</div>
                        <a href="{{ route('profile.edit') }}" class="mt-4 inline-flex w-full justify-center items-center rounded-md bg-[var(--er-green)] text-white py-2.5 font-semibold hover:opacity-95">Manage Profile</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
