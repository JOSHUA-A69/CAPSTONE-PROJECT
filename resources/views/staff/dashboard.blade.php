<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">CREaM Staff Dashboard</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Welcome Card -->
            <div class="mb-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @php
                        $user = auth()->user();
                        $displayName = $user->first_name ?? $user->name ?? $user->email ?? 'User';
                        $pendingCount = class_exists('App\\Models\\Reservation') ? \App\Models\Reservation::where('status', 'pending')->count() : 0;
                        $processedToday = class_exists('App\\Models\\Reservation') ? \App\Models\Reservation::whereDate('updated_at', now()->toDateString())->whereIn('status', ['approved','rejected','cancelled','admin_approved'])->count() : 0;
                    @endphp

                    <h3 class="text-2xl font-bold mb-2">Welcome, {{ $displayName }}!</h3>
                    <p class="text-gray-600 dark:text-gray-400">Review and triage reservation requests and manage organizations.</p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 items-stretch">
                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-800/20 overflow-hidden shadow-sm sm:rounded-lg border border-yellow-200 dark:border-yellow-800 h-full min-h-[160px]">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-yellow-800 dark:text-yellow-300 mb-1">Pending Reservations</p>
                                <p class="text-4xl font-bold text-yellow-900 dark:text-yellow-200">{{ $pendingCount }}</p>
                            </div>
                            <div class="p-3 bg-yellow-200 dark:bg-yellow-700 rounded-full">
                                <svg class="w-8 h-8 text-yellow-700 dark:text-yellow-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <a href="{{ route('staff.reservations.index', ['status' => 'pending']) }}" class="mt-4 inline-flex items-center text-sm font-semibold text-yellow-700 dark:text-yellow-300 hover:underline">
                            View Pending
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 overflow-hidden shadow-sm sm:rounded-lg border border-green-200 dark:border-green-800 h-full min-h-[160px]">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-green-800 dark:text-green-300 mb-1">Processed Today</p>
                                <p class="text-4xl font-bold text-green-900 dark:text-green-200">{{ $processedToday }}</p>
                            </div>
                            <div class="p-3 bg-green-200 dark:bg-green-700 rounded-full">
                                <svg class="w-8 h-8 text-green-700 dark:text-green-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                        </div>
                        <a href="{{ route('staff.reservations.index') }}" class="mt-4 inline-flex items-center text-sm font-semibold text-green-700 dark:text-green-300 hover:underline">
                            View All
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            @php
                $pendingReservations = class_exists('App\\Models\\Reservation')
                    ? \App\Models\Reservation::where('status', 'pending')->latest()->take(2)->get()
                    : collect();
                $processedReservations = class_exists('App\\Models\\Reservation')
                    ? \App\Models\Reservation::whereIn('status', ['approved','rejected','cancelled','admin_approved'])->latest()->take(2)->get()
                    : collect();
                $organizations = class_exists('App\\Models\\Organization')
                    ? (\App\Models\Organization::latest()->take(2)->get())
                    : collect();

                // Recent notifications: if not provided by the route, fall back to custom Notification model
                if (!isset($recentNotifications) || ($recentNotifications instanceof \Illuminate\Support\Collection && $recentNotifications->isEmpty())) {
                    try {
                        if (class_exists('App\\Models\\Notification')) {
                            $recentNotifications = App\Models\Notification::where('user_id', optional($user)->id)
                                ->orderBy('sent_at', 'desc')
                                ->take(5)
                                ->get();
                        } else {
                            $recentNotifications = collect();
                        }
                    } catch (\Throwable $e) {
                        // Swallow errors to avoid breaking the dashboard if schema differs
                        $recentNotifications = collect();
                    }
                }
            @endphp

            <!-- Reference-styled Cards Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-stretch">
                <!-- Reservation Queue -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg h-full min-h-[220px]">
                    <div class="p-6">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <h4 class="text-xl font-semibold">Reservation Queue</h4>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">Incoming requests awaiting review</p>

                        @forelse($pendingReservations as $r)
                            <div class="mb-3 flex items-center justify-between rounded-lg border border-gray-200 dark:border-gray-700 px-4 py-3 bg-gray-50 dark:bg-gray-900/40">
                                <div>
                                    <div class="font-medium text-gray-800 dark:text-gray-100">{{ $r->service_type ?? 'Service Request' }}</div>
                                    <div class="text-xs text-gray-500">Submitted {{ optional($r->created_at)->diffForHumans() }}</div>
                                </div>
                                <span class="text-xs px-2 py-1 rounded-full bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300">Pending</span>
                            </div>
                        @empty
                            <div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-700 p-4 text-sm text-gray-500">No pending reservations.</div>
                        @endforelse

                        <a href="{{ route('staff.reservations.index', ['status' => 'pending']) }}" class="mt-4 inline-flex w-full justify-center items-center rounded-md bg-[var(--er-green)] text-white py-2.5 font-semibold hover:opacity-95">View All Pending</a>
                    </div>
                </div>

                <!-- Organizations -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg h-full min-h-[220px]">
                    <div class="p-6">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <h4 class="text-xl font-semibold">Organizations</h4>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">Manage groups and adviser assignments</p>

                        @forelse($organizations as $org)
                            <div class="mb-3 rounded-lg border border-gray-200 dark:border-gray-700 px-4 py-3 bg-gray-50 dark:bg-gray-900/40">
                                <div class="font-medium text-gray-800 dark:text-gray-100">{{ $org->org_name ?? 'Organization' }}</div>
                                @if($org->adviser)
                                    <div class="text-xs text-gray-500">Adviser: {{ $org->adviser->full_name ?? $org->adviser->name ?? $org->adviser->email }}</div>
                                @endif
                            </div>
                        @empty
                            <div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-700 p-4 text-sm text-gray-500">No organizations yet.</div>
                        @endforelse

                        <a href="{{ route('staff.organizations.index') }}" class="mt-4 inline-flex w-full justify-center items-center rounded-md bg-[var(--er-green)] text-white py-2.5 font-semibold hover:opacity-95">Manage Organizations</a>
                    </div>
                </div>

                <!-- Event Calendar -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg h-full min-h-[220px]">
                    <div class="p-6">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <h4 class="text-xl font-semibold">Event Calendar</h4>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">Upcoming liturgical services and events</p>

                        <div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-700 p-4 text-sm text-gray-500">Calendar integration coming soon.</div>

                        <a href="#" class="mt-4 inline-flex w-full justify-center items-center rounded-md bg-[var(--er-green)] text-white py-2.5 font-semibold hover:opacity-95">View Calendar</a>
                    </div>
                </div>

                <!-- Processed Today -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg h-full min-h-[220px]">
                    <div class="p-6">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <h4 class="text-xl font-semibold">Processed Today</h4>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">Latest approved or finalized requests</p>

                        @forelse($processedReservations as $r)
                            <div class="mb-3 flex items-center justify-between rounded-lg border border-gray-200 dark:border-gray-700 px-4 py-3 bg-gray-50 dark:bg-gray-900/40">
                                <div>
                                    <div class="font-medium text-gray-800 dark:text-gray-100">{{ $r->service_type ?? 'Service' }}</div>
                                    <div class="text-xs text-gray-500">{{ ucfirst($r->status ?? 'processed') }} • {{ optional($r->updated_at)->diffForHumans() }}</div>
                                </div>
                                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                        @empty
                            <div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-700 p-4 text-sm text-gray-500">Nothing processed yet today.</div>
                        @endforelse

                        <a href="{{ route('staff.reservations.index') }}" class="mt-4 inline-flex w-full justify-center items-center rounded-md bg-[var(--er-green)] text-white py-2.5 font-semibold hover:opacity-95">View All</a>
                    </div>
                </div>

                <!-- Notifications -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg h-full min-h-[220px]">
                    <div class="p-6">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <h4 class="text-xl font-semibold">Notifications</h4>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">Recent updates and announcements</p>

                        @forelse($recentNotifications as $n)
                            @php
                                // Normalize data payload if stored as JSON string
                                $data = is_string($n->data ?? null) ? (json_decode($n->data, true) ?: []) : ($n->data ?? []);
                                $message = $n->message ?? ($data['title'] ?? ($n->type ?? 'Notification'));
                                $timeAgo = $n->sent_at ? optional($n->sent_at)->diffForHumans() : optional($n->created_at)->diffForHumans();
                            @endphp
                            <div class="mb-3 rounded-lg border border-gray-200 dark:border-gray-700 px-4 py-3 bg-gray-50 dark:bg-gray-900/40">
                                <div class="font-medium text-gray-800 dark:text-gray-100">{!! $message !!}</div>
                                <div class="text-xs text-gray-500">{{ $timeAgo }}</div>
                            </div>
                        @empty
                            <div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-700 p-4 text-sm text-gray-500">No notifications yet.</div>
                        @endforelse

                        <a href="{{ route('staff.notifications.index') }}" class="mt-4 inline-flex w-full justify-center items-center rounded-md bg-[var(--er-green)] text-white py-2.5 font-semibold hover:opacity-95">View All</a>
                    </div>
                </div>

                <!-- Your Profile -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg h-full min-h-[220px]">
                    <div class="p-6">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.89 0 5.558.915 7.879 2.463M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <h4 class="text-xl font-semibold">Your Profile</h4>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">Update your personal information and password</p>

                        <div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-700 p-4 text-sm text-gray-500">Keep your details up to date.</div>

                        <a href="{{ route('profile.edit') }}" class="mt-4 inline-flex w-full justify-center items-center rounded-md bg-[var(--er-green)] text-white py-2.5 font-semibold hover:opacity-95">Manage Profile</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
