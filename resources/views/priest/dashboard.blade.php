<x-app-layout>
    <x-slot name="header">
        <h2 class="text-heading font-semibold text-xl leading-tight">
            ⛪ Priest Dashboard
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

                        // Get pending confirmations count - include all statuses where priest needs to review/confirm
                        // This includes: pending (just submitted), pending_priest_confirmation, admin_approved
                        $pendingCount = \App\Models\Reservation::where('officiant_id', $user->id)
                            ->whereIn('status', ['pending', 'pending_priest_confirmation', 'admin_approved'])
                            ->where(function($q) {
                                $q->where('priest_confirmation', '!=', 'confirmed')
                                  ->orWhereNull('priest_confirmation')
                                  ->orWhere('priest_confirmation', 'pending');
                            })
                            ->count();

                        // Get upcoming confirmed services
                        $upcomingCount = \App\Models\Reservation::where('officiant_id', $user->id)
                            ->where('priest_confirmation', 'confirmed')
                            ->where('schedule_date', '>=', now())
                            ->count();
                    @endphp

                    <h3 class="text-2xl font-bold mb-2 text-heading">Welcome, {{ $displayName }}! 🙏</h3>
                    <p class="text-muted">Manage your service assignments and schedule.</p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Pending Confirmations -->
                <div class="card border-l-4 border-yellow-500">
                    <div class="card-body">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-muted mb-1">Pending Confirmations</p>
                                <p class="text-4xl font-bold text-heading">{{ $pendingCount }}</p>
                            </div>
                            <div class="p-3 bg-yellow-100 dark:bg-yellow-900/30 rounded-full">
                                <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        @if($pendingCount > 0)
                        <div class="mt-4 pt-4 border-t">
                            <a href="{{ route('priest.reservations.index', ['status' => 'pending_priest_confirmation']) }}" class="btn-secondary inline-flex">
                                View Pending Assignments
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                        @else
                        <p class="mt-4 text-sm text-muted">No pending confirmations</p>
                        @endif
                    </div>
                </div>

                <!-- Upcoming Services -->
                <div class="card border-l-4 border-green-500">
                    <div class="card-body">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-muted mb-1">Upcoming Services</p>
                                <p class="text-4xl font-bold text-heading">{{ $upcomingCount }}</p>
                            </div>
                            <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-full">
                                <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t">
                            <a href="{{ route('priest.reservations.index', ['time' => 'upcoming']) }}" class="btn-secondary inline-flex">
                                View Schedule
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('priest.reservations.index') }}" class="card-hover group">
                    <div class="card-body">
                        <div class="flex items-center gap-3">
                            <div class="p-3 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-heading">All Assignments</h4>
                                <p class="text-sm text-muted">View all reservations</p>
                            </div>
                        </div>
                    </div>
                </a>

                <a href="{{ route('priest.reservations.calendar') }}" class="card-hover group">
                    <div class="card-body">
                        <div class="flex items-center gap-3">
                            <div class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-lg group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-heading">Calendar View</h4>
                                <p class="text-sm text-muted">See your schedule</p>
                            </div>
                        </div>
                    </div>
                </a>

                <a href="{{ route('priest.reservations.index', ['time' => 'past']) }}" class="card-hover group">
                    <div class="card-body">
                        <div class="flex items-center gap-3">
                            <div class="p-3 bg-gray-100 dark:bg-gray-700 rounded-lg group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-heading">Past Services</h4>
                                <p class="text-sm text-muted">View history</p>
                            </div>
                        </div>
                    </div>
                </a>

                <a href="{{ route('priest.reservations.declined') }}" class="card-hover group border-red-200 dark:border-red-900">
                    <div class="card-body">
                        <div class="flex items-center gap-3">
                            <div class="p-3 bg-red-100 dark:bg-red-900/30 rounded-lg group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-heading">Declined Services</h4>
                                <p class="text-sm text-muted">View declined history</p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
