<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Priest Dashboard</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Welcome Message -->
            <div class="mb-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @php
                        $user = auth()->user();
                        $displayName = $user->first_name ?? $user->name ?? $user->email ?? 'User';

                        // Get pending confirmations count
                        $pendingCount = \App\Models\Reservation::where('officiant_id', $user->id)
                            ->where('status', 'pending_priest_confirmation')
                            ->count();

                        // Get upcoming confirmed services
                        $upcomingCount = \App\Models\Reservation::where('officiant_id', $user->id)
                            ->where('priest_confirmation', 'confirmed')
                            ->where('schedule_date', '>=', now())
                            ->count();
                    @endphp

                    <h3 class="text-2xl font-bold mb-2">Welcome, {{ $displayName }}!</h3>
                    <p class="text-gray-600 dark:text-gray-400">Manage your service assignments and schedule.</p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Pending Confirmations -->
                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-800/20 overflow-hidden shadow-sm sm:rounded-lg border border-yellow-200 dark:border-yellow-800">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-yellow-800 dark:text-yellow-300 mb-1">Pending Confirmations</p>
                                <p class="text-4xl font-bold text-yellow-900 dark:text-yellow-200">{{ $pendingCount }}</p>
                            </div>
                            <div class="p-3 bg-yellow-200 dark:bg-yellow-700 rounded-full">
                                <svg class="w-8 h-8 text-yellow-700 dark:text-yellow-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        @if($pendingCount > 0)
                        <a href="{{ route('priest.reservations.index', ['status' => 'pending_priest_confirmation']) }}"
                           class="mt-4 inline-flex items-center text-sm font-semibold text-yellow-700 dark:text-yellow-300 hover:underline">
                            View Pending Assignments
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                        @else
                        <p class="mt-4 text-sm text-yellow-700 dark:text-yellow-300">No pending confirmations</p>
                        @endif
                    </div>
                </div>

                <!-- Upcoming Services -->
                <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 overflow-hidden shadow-sm sm:rounded-lg border border-green-200 dark:border-green-800">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-green-800 dark:text-green-300 mb-1">Upcoming Services</p>
                                <p class="text-4xl font-bold text-green-900 dark:text-green-200">{{ $upcomingCount }}</p>
                            </div>
                            <div class="p-3 bg-green-200 dark:bg-green-700 rounded-full">
                                <svg class="w-8 h-8 text-green-700 dark:text-green-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>
                        <a href="{{ route('priest.reservations.index', ['time' => 'upcoming']) }}"
                           class="mt-4 inline-flex items-center text-sm font-semibold text-green-700 dark:text-green-300 hover:underline">
                            View Schedule
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            @php
                $pendingPreview = class_exists('App\\Models\\Reservation')
                    ? \App\Models\Reservation::where('officiant_id', $user->id)->where('status','pending_priest_confirmation')->latest()->take(2)->get()
                    : collect();
                $upcomingPreview = class_exists('App\\Models\\Reservation')
                    ? \App\Models\Reservation::where('officiant_id', $user->id)->where('priest_confirmation','confirmed')->where('schedule_date','>=', now())->orderBy('schedule_date')->take(2)->get()
                    : collect();
                $recentUpdates = class_exists('App\\Models\\Reservation')
                    ? \App\Models\Reservation::where('officiant_id', $user->id)->whereDate('updated_at', now()->toDateString())->latest('updated_at')->take(2)->get()
                    : collect();
                $recentNotifications = class_exists('App\\Models\\Notification')
                    ? \App\Models\Notification::where('user_id', auth()->id())->orderBy('sent_at','desc')->limit(2)->get()
                    : collect();
            @endphp

            <!-- Reference-styled Cards Grid (Priest) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Pending Confirmations -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <h4 class="text-xl font-semibold">Pending Confirmations</h4>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">Assignments awaiting your response</p>

                        @forelse($pendingPreview as $r)
                            <div class="mb-3 flex items-center justify-between rounded-lg border border-gray-200 dark:border-gray-700 px-4 py-3 bg-gray-50 dark:bg-gray-900/40">
                                <div>
                                    <div class="font-medium text-gray-800 dark:text-gray-100">{{ $r->service_type ?? 'Reservation' }}</div>
                                    <div class="text-xs text-gray-500">Requested {{ optional($r->created_at)->diffForHumans() }}</div>
                                </div>
                                <span class="text-xs px-2 py-1 rounded-full bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300">Pending</span>
                            </div>
                        @empty
                            <div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-700 p-4 text-sm text-gray-500">No pending confirmations.</div>
                        @endforelse

                        <a href="{{ route('priest.reservations.index', ['status' => 'pending_priest_confirmation']) }}" class="mt-4 inline-flex w-full justify-center items-center rounded-md bg-[var(--er-green)] text-white py-2.5 font-semibold hover:opacity-95">Review Pending</a>
                    </div>
                </div>

                <!-- Upcoming Services -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <h4 class="text-xl font-semibold">Upcoming Services</h4>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">Next confirmed services</p>

                        @forelse($upcomingPreview as $r)
                            <div class="mb-3 flex items-center justify-between rounded-lg border border-gray-200 dark:border-gray-700 px-4 py-3 bg-gray-50 dark:bg-gray-900/40">
                                <div>
                                    <div class="font-medium text-gray-800 dark:text-gray-100">{{ $r->service_type ?? 'Reservation' }}</div>
                                    <div class="text-xs text-gray-500">{{ optional($r->schedule_date)->format('M d, Y') }}</div>
                                </div>
                                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                        @empty
                            <div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-700 p-4 text-sm text-gray-500">No upcoming services.</div>
                        @endforelse

                        <a href="{{ route('priest.reservations.index', ['time' => 'upcoming']) }}" class="mt-4 inline-flex w-full justify-center items-center rounded-md bg-[var(--er-green)] text-white py-2.5 font-semibold hover:opacity-95">View Schedule</a>
                    </div>
                </div>

                <!-- Event Calendar -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <h4 class="text-xl font-semibold">Event Calendar</h4>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">See your upcoming schedule</p>
                        <div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-700 p-4 text-sm text-gray-500">Calendar view available.</div>
                        <a href="{{ route('priest.reservations.calendar') }}" class="mt-4 inline-flex w-full justify-center items-center rounded-md bg-[var(--er-green)] text-white py-2.5 font-semibold hover:opacity-95">Open Calendar</a>
                    </div>
                </div>

                <!-- Recent Updates -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <h4 class="text-xl font-semibold">Recent Updates</h4>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">Changes on your assignments today</p>

                        @forelse($recentUpdates as $r)
                            <div class="mb-3 flex items-center justify-between rounded-lg border border-gray-200 dark:border-gray-700 px-4 py-3 bg-gray-50 dark:bg-gray-900/40">
                                <div>
                                    <div class="font-medium text-gray-800 dark:text-gray-100">{{ $r->service_type ?? 'Reservation' }}</div>
                                    <div class="text-xs text-gray-500">{{ ucfirst($r->status ?? 'updated') }} • {{ optional($r->updated_at)->diffForHumans() }}</div>
                                </div>
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </div>
                        @empty
                            <div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-700 p-4 text-sm text-gray-500">No updates yet today.</div>
                        @endforelse

                        <a href="{{ route('priest.reservations.index') }}" class="mt-4 inline-flex w-full justify-center items-center rounded-md bg-[var(--er-green)] text-white py-2.5 font-semibold hover:opacity-95">View All Assignments</a>
                    </div>
                </div>

                <!-- Notifications -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <h4 class="text-xl font-semibold">Notifications</h4>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">Recent updates & alerts</p>

                        @forelse($recentNotifications as $n)
                            <div class="mb-3 rounded-lg border border-gray-200 dark:border-gray-700 px-4 py-3 bg-gray-50 dark:bg-gray-900/40">
                                <div class="font-medium text-gray-800 dark:text-gray-100">{!! $n->message ?? ($n->type ?? 'Notification') !!}</div>
                                <div class="text-xs text-gray-500">{{ optional($n->sent_at ?? $n->created_at)->diffForHumans() }}</div>
                            </div>
                        @empty
                            <div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-700 p-4 text-sm text-gray-500">No notifications yet.</div>
                        @endforelse

                        <a href="{{ route('priest.notifications.index') }}" class="mt-4 inline-flex w-full justify-center items-center rounded-md bg-[var(--er-green)] text-white py-2.5 font-semibold hover:opacity-95">View All</a>
                    </div>
                </div>

                <!-- Your Profile -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
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
