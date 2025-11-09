<x-app-layout>
    <x-slot name="header">
        <h2 class="text-heading font-semibold text-xl leading-tight">🏫 Adviser Dashboard</h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @php
                $user = auth()->user();
                $displayName = $user->first_name ?? $user->name ?? $user->email ?? 'User';
                $orgIds = $user->organizations->pluck('org_id');

                // Core counts
                $pendingCount = \App\Models\Reservation::whereIn('org_id', $orgIds)
                    ->where('status', 'pending')
                    ->count();

                $adviserApprovedCount = \App\Models\Reservation::whereIn('org_id', $orgIds)
                    ->where('status', 'adviser_approved')
                    ->count();

                $awaitingAdminCount = \App\Models\Reservation::whereIn('org_id', $orgIds)
                    ->whereIn('status', ['adviser_approved'])
                    ->count();

                $approvedUpcomingCount = \App\Models\Reservation::whereIn('org_id', $orgIds)
                    ->whereIn('status', ['admin_approved', 'approved'])
                    ->where('schedule_date', '>=', now())
                    ->count();

                // Unnoticed > 24h old still pending (leveraging local scope if exists)
                $unnoticedCount = \App\Models\Reservation::whereIn('org_id', $orgIds)
                    ->unnoticedByAdviser()
                    ->count();
            @endphp

            <!-- Welcome Card -->
            <div class="card mb-6">
                <div class="card-body">
                    <h3 class="text-2xl sm:text-3xl font-bold mb-2 text-heading">Welcome, {{ $displayName }}! 👋</h3>
                    <p class="text-muted">Review and triage reservation requests for your assigned organizations.</p>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8" aria-label="Reservation status overview">
                <!-- Pending Requests -->
                <div class="card border-l-4 border-yellow-500" aria-live="polite">
                    <div class="card-body">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-muted mb-1">Pending Requests</p>
                                <p class="text-4xl font-bold text-heading">{{ $pendingCount }}</p>
                            </div>
                            <div class="p-3 bg-yellow-100 dark:bg-yellow-900/30 rounded-full" aria-hidden="true">
                                <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t">
                            <a href="{{ route('adviser.reservations.index', ['filter' => 'pending']) }}" class="btn-secondary inline-flex" aria-label="View pending reservation requests">
                                Review Pending
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Adviser Approved -->
                <div class="card border-l-4 border-indigo-500">
                    <div class="card-body">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-muted mb-1">Adviser Approved</p>
                                <p class="text-4xl font-bold text-heading">{{ $adviserApprovedCount }}</p>
                            </div>
                            <div class="p-3 bg-indigo-100 dark:bg-indigo-900/30 rounded-full" aria-hidden="true">
                                <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7 20h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v11a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t">
                            <a href="{{ route('adviser.reservations.index', ['filter' => 'adviser_approved']) }}" class="btn-secondary inline-flex" aria-label="View adviser approved reservations">
                                Awaiting Admin
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Upcoming (Admin Approved / Final Approved) -->
                <div class="card border-l-4 border-green-500">
                    <div class="card-body">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-muted mb-1">Upcoming Approved</p>
                                <p class="text-4xl font-bold text-heading">{{ $approvedUpcomingCount }}</p>
                            </div>
                            <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-full" aria-hidden="true">
                                <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t">
                            <a href="{{ route('adviser.reservations.index', ['filter' => 'upcoming']) }}" class="btn-secondary inline-flex" aria-label="View upcoming approved reservations">
                                View Schedule
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Unnoticed (>24h) -->
                <div class="card border-l-4 border-red-500">
                    <div class="card-body">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-muted mb-1">Unnoticed (24h+)</p>
                                <p class="text-4xl font-bold text-heading">{{ $unnoticedCount }}</p>
                            </div>
                            <div class="p-3 bg-red-100 dark:bg-red-900/30 rounded-full" aria-hidden="true">
                                <svg class="w-8 h-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t">
                            <a href="{{ route('adviser.reservations.index', ['filter' => 'unnoticed']) }}" class="btn-secondary inline-flex" aria-label="View unnoticed reservations older than 24 hours">
                                Review Now
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-10" aria-label="Quick actions">
                <a href="{{ route('adviser.reservations.index') }}" class="card-hover group">
                    <div class="card-body">
                        <div class="flex items-start gap-3">
                            <div class="p-3 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg group-hover:scale-110 transition-transform" aria-hidden="true">
                                <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-heading">All Requests</h4>
                                <p class="text-sm text-muted">Browse every reservation</p>
                            </div>
                        </div>
                    </div>
                </a>

                <a href="{{ route('adviser.reservations.index', ['filter' => 'pending']) }}" class="card-hover group">
                    <div class="card-body">
                        <div class="flex items-start gap-3">
                            <div class="p-3 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg group-hover:scale-110 transition-transform" aria-hidden="true">
                                <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-heading">Pending</h4>
                                <p class="text-sm text-muted">Needs your review</p>
                            </div>
                        </div>
                    </div>
                </a>

                <a href="{{ route('adviser.reservations.index', ['filter' => 'adviser_approved']) }}" class="card-hover group">
                    <div class="card-body">
                        <div class="flex items-start gap-3">
                            <div class="p-3 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg group-hover:scale-110 transition-transform" aria-hidden="true">
                                <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7 20h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v11a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-heading">Adviser Approved</h4>
                                <p class="text-sm text-muted">Awaiting admin actions</p>
                            </div>
                        </div>
                    </div>
                </a>

                <a href="{{ route('adviser.reservations.index', ['filter' => 'rejected']) }}" class="card-hover group">
                    <div class="card-body">
                        <div class="flex items-start gap-3">
                            <div class="p-3 bg-red-100 dark:bg-red-900/30 rounded-lg group-hover:scale-110 transition-transform" aria-hidden="true">
                                <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-heading">Rejected</h4>
                                <p class="text-sm text-muted">Past declined requests</p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Assigned Organizations -->
            <div class="card mb-6" aria-label="Assigned organizations list">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-semibold text-heading">Assigned Organizations</h3>
                        <span class="text-xs px-2 py-1 rounded bg-gray-100 dark:bg-gray-700 text-muted">{{ $user->organizations->count() }} total</span>
                    </div>
                    @if($user->organizations->isEmpty())
                        <p class="text-sm text-muted">You are not assigned to any organization yet.</p>
                        <p class="text-xs text-muted mt-2">Staff can assign you via the Organizations management page.</p>
                    @else
                        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($user->organizations as $org)
                                <li class="py-3 flex items-start gap-3">
                                    <div class="mt-1 p-2 bg-blue-100 dark:bg-blue-900/30 rounded" aria-hidden="true">
                                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-heading mb-0.5">{{ $org->org_name }}</h4>
                                        <p class="text-sm text-muted">{{ $org->org_desc ?? 'No description provided.' }}</p>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <!-- Guidance / Help -->
            <div class="card">
                <div class="card-body">
                    <h3 class="text-lg font-semibold text-heading mb-2">How to Use</h3>
                    <p class="text-sm text-muted mb-2">Use the Pending card to prioritize new requests. Once reviewed, approve or reject from the reservation detail view. Approved requests move to "Adviser Approved" until an administrator finalizes them.</p>
                    <p class="text-xs text-muted">Need improvements? Reach out to CREaM staff for feature requests.</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
