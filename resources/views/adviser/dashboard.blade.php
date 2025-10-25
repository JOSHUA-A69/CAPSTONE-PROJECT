<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Organization Adviser Dashboard</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Welcome Card -->
            <div class="mb-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @php
                        $user = auth()->user();
                        $displayName = $user->first_name ?? $user->name ?? $user->email ?? 'User';
                        $orgIds = $user->organizations ? $user->organizations->pluck('org_id')->all() : [];
                        $pendingCount = (class_exists('App\\Models\\Reservation') && !empty($orgIds))
                            ? \App\Models\Reservation::whereIn('org_id', $orgIds)->where('status', 'pending')->count()
                            : 0;
                        $upcomingCount = (class_exists('App\\Models\\Reservation') && !empty($orgIds))
                            ? \App\Models\Reservation::whereIn('org_id', $orgIds)->whereIn('status', ['adviser_approved','approved','admin_approved'])->where('schedule_date', '>=', now())->count()
                            : 0;
                    @endphp

                    <h3 class="text-2xl font-bold mb-2">Welcome, {{ $displayName }}!</h3>
                    <p class="text-gray-600 dark:text-gray-400">Review reservations for your assigned organizations.</p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 items-stretch">
                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-800/20 overflow-hidden shadow-sm sm:rounded-lg border border-yellow-200 dark:border-yellow-800 h-full min-h-[160px]">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-yellow-800 dark:text-yellow-300 mb-1">Pending Reviews</p>
                                <p class="text-4xl font-bold text-yellow-900 dark:text-yellow-200">{{ $pendingCount }}</p>
                            </div>
                            <div class="p-3 bg-yellow-200 dark:bg-yellow-700 rounded-full">
                                <svg class="w-8 h-8 text-yellow-700 dark:text-yellow-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        
                    </div>
                </div>
                <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 overflow-hidden shadow-sm sm:rounded-lg border border-green-200 dark:border-green-800 h-full min-h-[160px]">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-green-800 dark:text-green-300 mb-1">Upcoming Approved</p>
                                <p class="text-4xl font-bold text-green-900 dark:text-green-200">{{ $upcomingCount }}</p>
                            </div>
                            <div class="p-3 bg-green-200 dark:bg-green-700 rounded-full">
                                <svg class="w-8 h-8 text-green-700 dark:text-green-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>

            @php
                $pendingPreview = (class_exists('App\\Models\\Reservation') && !empty($orgIds))
                    ? \App\Models\Reservation::whereIn('org_id', $orgIds)->where('status','pending')->latest()->take(2)->get()
                    : collect();
                $assignedOrgs = $user->organizations ?? collect();
                $upcomingPreview = (class_exists('App\\Models\\Reservation') && !empty($orgIds))
                    ? \App\Models\Reservation::whereIn('org_id', $orgIds)->whereIn('status', ['adviser_approved','approved','admin_approved'])->where('schedule_date','>=', now())->orderBy('schedule_date')->take(2)->get()
                    : collect();
                $recentNotifications = class_exists('App\\Models\\Notification')
                    ? \App\Models\Notification::where('user_id', auth()->id())->orderBy('sent_at','desc')->limit(2)->get()
                    : collect();
            @endphp

            <!-- Reference-styled Cards Grid (Adviser) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-stretch">
                <!-- Requests Queue -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg h-full min-h-[220px]">
                    <div class="p-6">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <h4 class="text-xl font-semibold">Requests Queue</h4>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">Pending requests across your orgs</p>

                        @forelse($pendingPreview as $r)
                            <div class="mb-3 flex items-center justify-between rounded-lg border border-gray-200 dark:border-gray-700 px-4 py-3 bg-gray-50 dark:bg-gray-900/40">
                                <div>
                                    <div class="font-medium text-gray-800 dark:text-gray-100">{{ $r->service_type ?? 'Reservation' }}</div>
                                    <div class="text-xs text-gray-500">{{ optional($r->created_at)->diffForHumans() }}</div>
                                </div>
                                <span class="text-xs px-2 py-1 rounded-full bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300">Pending</span>
                            </div>
                        @empty
                            <div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-700 p-4 text-sm text-gray-500">No pending requests.</div>
                        @endforelse

                        <a href="{{ route('adviser.reservations.index', ['status' => 'pending']) }}" class="mt-4 inline-flex w-full justify-center items-center rounded-md bg-[var(--er-green)] text-white py-2.5 font-semibold hover:opacity-95">Review Requests</a>
                    </div>
                </div>

                <!-- Assigned Organizations -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg h-full min-h-[220px]">
                    <div class="p-6">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5V4H2v16h5"/></svg>
                            <h4 class="text-xl font-semibold">Assigned Organizations</h4>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">Your current org assignments</p>

                        @if(($assignedOrgs->count() ?? 0) === 0)
                            <div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-700 p-4 text-sm text-gray-500">No organizations assigned yet.</div>
                        @else
                            @foreach($assignedOrgs->take(2) as $org)
                                <div class="mb-3 rounded-lg border border-gray-200 dark:border-gray-700 px-4 py-3 bg-gray-50 dark:bg-gray-900/40">
                                    <div class="font-medium text-gray-800 dark:text-gray-100">{{ $org->org_name }}</div>
                                    <div class="text-xs text-gray-500">{{ Str::limit($org->org_desc ?? '—', 120) }}</div>
                                </div>
                            @endforeach
                        @endif

                        <a href="{{ route('adviser.reservations.index') }}" class="mt-4 inline-flex w-full justify-center items-center rounded-md bg-[var(--er-green)] text-white py-2.5 font-semibold hover:opacity-95">View All</a>
                    </div>
                </div>

                <!-- Event Calendar -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg h-full min-h-[220px]">
                    <div class="p-6">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <h4 class="text-xl font-semibold">Event Calendar</h4>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">Upcoming events across orgs</p>
                        <div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-700 p-4 text-sm text-gray-500">Calendar integration coming soon.</div>
                        <a href="#" class="mt-4 inline-flex w-full justify-center items-center rounded-md bg-[var(--er-green)] text-white py-2.5 font-semibold hover:opacity-95">View Calendar</a>
                    </div>
                </div>

                <!-- Upcoming Approved -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg h-full min-h-[220px]">
                    <div class="p-6">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <h4 class="text-xl font-semibold">Upcoming Approved</h4>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">Next approved reservations</p>

                        @forelse($upcomingPreview as $r)
                            <div class="mb-3 flex items-center justify-between rounded-lg border border-gray-200 dark:border-gray-700 px-4 py-3 bg-gray-50 dark:bg-gray-900/40">
                                <div>
                                    <div class="font-medium text-gray-800 dark:text-gray-100">{{ $r->service_type ?? 'Reservation' }}</div>
                                    <div class="text-xs text-gray-500">Scheduled {{ optional($r->schedule_date)->format('M d, Y') }}</div>
                                </div>
                                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                        @empty
                            <div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-700 p-4 text-sm text-gray-500">No upcoming reservations.</div>
                        @endforelse

                        <a href="{{ route('adviser.reservations.index', ['time' => 'upcoming']) }}" class="mt-4 inline-flex w-full justify-center items-center rounded-md bg-[var(--er-green)] text-white py-2.5 font-semibold hover:opacity-95">View Schedule</a>
                    </div>
                </div>

                <!-- Notifications -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg h-full min-h-[220px]">
                    <div class="p-6">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <h4 class="text-xl font-semibold">Notifications</h4>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">Recent updates</p>

                        @forelse($recentNotifications as $n)
                            <div class="mb-3 rounded-lg border border-gray-200 dark:border-gray-700 px-4 py-3 bg-gray-50 dark:bg-gray-900/40">
                                <div class="font-medium text-gray-800 dark:text-gray-100">{!! $n->message ?? ($n->type ?? 'Notification') !!}</div>
                                <div class="text-xs text-gray-500">{{ optional($n->sent_at ?? $n->created_at)->diffForHumans() }}</div>
                            </div>
                        @empty
                            <div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-700 p-4 text-sm text-gray-500">No notifications yet.</div>
                        @endforelse

                        <a href="{{ route('adviser.notifications.index') }}" class="mt-4 inline-flex w-full justify-center items-center rounded-md bg-[var(--er-green)] text-white py-2.5 font-semibold hover:opacity-95">View All</a>
                    </div>
                </div>

                <!-- Your Profile -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg h-full min-h-[220px]">
                    <div class="p-6">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.89 0 5.558.915 7.879 2.463M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <h4 class="text-xl font-semibold">Your Profile</h4>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">Update your account details</p>
                        <div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-700 p-4 text-sm text-gray-500">Keep your information current.</div>
                        <a href="{{ route('profile.edit') }}" class="mt-4 inline-flex w-full justify-center items-center rounded-md bg-[var(--er-green)] text-white py-2.5 font-semibold hover:opacity-95">Manage Profile</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
