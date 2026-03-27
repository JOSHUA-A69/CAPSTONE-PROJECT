<x-app-layout>
    <x-slot name="header">
        <h2 class="text-heading font-semibold text-lg sm:text-xl leading-tight">
            Priest Dashboard
        </h2>
    </x-slot>

    <div class="py-4 sm:py-6 lg:py-12">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">

            <!-- Welcome Message -->
            <div class="card mb-4 sm:mb-6 rounded-2xl overflow-hidden bg-white border border-gray-200 shadow-lg">
                <div class="card-body p-4 sm:p-6">
                    @php
                        $user = auth()->user();
                        $displayName = 'Fr. ' . ($user->first_name ?? $user->name ?? $user->email ?? 'User');

                        // Get pending confirmations count - include all statuses where priest needs to review/confirm
                        // This includes: pending, adviser_approved, pending_priest_confirmation, admin_approved
                        // Check both officiant_id (legacy) and priests relationship (many-to-many)
                        $pendingCount = \App\Models\Reservation::where(function($q) use ($user) {
                                $q->where('officiant_id', $user->id)
                                  ->orWhereHas('priests', function($priestQuery) use ($user) {
                                      $priestQuery->where('users.id', $user->id);
                                  });
                            })
                            ->whereIn('status', ['pending', 'adviser_approved', 'pending_priest_confirmation', 'admin_approved'])
                            ->where(function($q) {
                                $q->where('priest_confirmation', '!=', 'confirmed')
                                  ->orWhereNull('priest_confirmation')
                                  ->orWhere('priest_confirmation', 'pending');
                            })
                            ->count();

                        // Get upcoming confirmed services
                        $upcomingCount = \App\Models\Reservation::where(function($q) use ($user) {
                                $q->where('officiant_id', $user->id)
                                  ->orWhereHas('priests', function($priestQuery) use ($user) {
                                      $priestQuery->where('users.id', $user->id);
                                  });
                            })
                            ->where('priest_confirmation', 'confirmed')
                            ->where('schedule_date', '>=', now())
                            ->count();

                        // Pending Cancellations
                        $cancellationCount = \App\Models\ReservationCancellation::whereHas('reservation', function($q) use ($user) {
                                $q->where('officiant_id', $user->id)
                                  ->orWhereHas('priests', function($pq) use ($user) {
                                      $pq->where('users.id', $user->id);
                                  });
                            })
                            ->whereNotIn('status', ['rejected', 'completed'])
                            ->whereNull('priest_confirmed_at')
                            ->count();
                    @endphp

                    <div>
                        <p class="text-xs sm:text-sm text-gray-500 mb-0.5 sm:mb-1">Welcome,</p>
                        <h3 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-800 break-words">
                            {{ $displayName }}
                        </h3>
                    </div>
                    <p class="text-gray-600 mt-1.5 sm:mt-2 text-sm sm:text-base">Manage your service assignments and schedule.</p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-6 mb-4 sm:mb-6">
                <!-- Pending Confirmations -->
                <a href="{{ route('priest.reservations.index', ['status' => 'pending_priest_confirmation']) }}"
                   class="block rounded-2xl overflow-hidden bg-white border border-gray-200 shadow-lg hover:shadow-xl hover:border-amber-200 transition-all duration-200 transform hover:scale-[1.02] group {{ $pendingCount > 0 ? 'cursor-pointer' : 'cursor-default' }}">
                    <div class="card-body p-3 sm:p-4 lg:p-6">
                        <div class="flex items-center justify-between">
                            <div class="min-w-0 flex-1">
                                <p class="text-[10px] sm:text-sm font-medium text-gray-500 mb-0.5 sm:mb-1 line-clamp-2 group-hover:text-amber-600 transition-colors">Pending Assignments</p>
                                <p data-stat="pending_reservations" class="text-2xl sm:text-4xl font-bold text-gray-800 group-hover:text-amber-700 transition-colors">{{ $pendingCount }}</p>
                            </div>
                            <div class="p-2 sm:p-3 bg-amber-100 rounded-full flex-shrink-0 ml-2 group-hover:bg-amber-200 transition-colors">
                                <svg class="w-5 h-5 sm:w-8 sm:h-8 text-amber-600 group-hover:text-amber-700 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        @if($pendingCount > 0)
                            <div class="mt-3 sm:mt-4 pt-3 sm:pt-4 border-t border-gray-200 group-hover:border-amber-200 transition-colors">
                                <div class="inline-flex bg-amber-100 group-hover:bg-amber-200 text-amber-700 px-3 py-1.5 rounded-lg text-xs sm:text-sm font-medium transition-colors">
                                    Click to view
                                    <svg class="w-3 h-3 sm:w-4 sm:h-4 ml-1 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </div>
                            </div>
                        @else
                            <p class="mt-3 sm:mt-4 text-xs sm:text-sm text-gray-500">No pending confirmations</p>
                        @endif
                    </div>
                </a>

                <!-- Upcoming Services -->
                <a href="{{ route('priest.reservations.index', ['time' => 'upcoming']) }}"
                   class="block rounded-2xl overflow-hidden bg-white border border-gray-200 shadow-lg hover:shadow-xl hover:border-emerald-200 transition-all duration-200 transform hover:scale-[1.02] group cursor-pointer">
                    <div class="card-body p-3 sm:p-4 lg:p-6">
                        <div class="flex items-center justify-between">
                            <div class="min-w-0 flex-1">
                                <p class="text-[10px] sm:text-sm font-medium text-gray-500 mb-0.5 sm:mb-1 line-clamp-2 group-hover:text-emerald-600 transition-colors">Upcoming Services</p>
                                <p data-stat="upcoming_reservations" class="text-2xl sm:text-4xl font-bold text-gray-800 group-hover:text-emerald-700 transition-colors">{{ $upcomingCount }}</p>
                            </div>
                            <div class="p-2 sm:p-3 bg-emerald-100 rounded-full flex-shrink-0 ml-2 group-hover:bg-emerald-200 transition-colors">
                                <svg class="w-5 h-5 sm:w-8 sm:h-8 text-emerald-600 group-hover:text-emerald-700 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="mt-3 sm:mt-4 pt-3 sm:pt-4 border-t border-gray-200 group-hover:border-emerald-200 transition-colors">
                            <div class="inline-flex bg-emerald-100 group-hover:bg-emerald-200 text-emerald-700 px-3 py-1.5 rounded-lg text-xs sm:text-sm font-medium transition-colors">
                                Click to view
                                <svg class="w-3 h-3 sm:w-4 sm:h-4 ml-1 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- Cancellation Requests -->
                <a href="{{ route('priest.cancellations.index') }}"
                   class="block rounded-2xl overflow-hidden bg-white border border-gray-200 shadow-lg hover:shadow-xl hover:border-red-200 transition-all duration-200 transform hover:scale-[1.02] group cursor-pointer">
                    <div class="card-body p-3 sm:p-4 lg:p-6">
                        <div class="flex items-center justify-between">
                            <div class="min-w-0 flex-1">
                                <p class="text-[10px] sm:text-sm font-medium text-gray-500 mb-0.5 sm:mb-1 line-clamp-2 group-hover:text-red-600 transition-colors">Cancellations</p>
                                <p class="text-2xl sm:text-4xl font-bold text-gray-800 group-hover:text-red-700 transition-colors">{{ $cancellationCount }}</p>
                            </div>
                            <div class="p-2 sm:p-3 bg-red-100 rounded-full flex-shrink-0 ml-2 group-hover:bg-red-200 transition-colors">
                                <svg class="w-5 h-5 sm:w-8 sm:h-8 text-red-600 group-hover:text-red-700 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        @if($cancellationCount > 0)
                            <div class="mt-3 sm:mt-4 pt-3 sm:pt-4 border-t border-gray-200 group-hover:border-red-200 transition-colors">
                                <div class="inline-flex bg-red-100 group-hover:bg-red-200 text-red-700 px-3 py-1.5 rounded-lg text-xs sm:text-sm font-medium transition-colors">
                                    Click to review
                                    <svg class="w-3 h-3 sm:w-4 sm:h-4 ml-1 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </div>
                            </div>
                        @else
                            <p class="mt-3 sm:mt-4 text-xs sm:text-sm text-gray-500">No pending cancellations</p>
                        @endif
                    </div>
                </a>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <a href="{{ route('priest.reservations.index') }}" class="card-hover group rounded-2xl overflow-hidden">
                    <div class="card-body p-3 sm:p-4 lg:p-6 bg-white border border-gray-200 shadow-lg hover:shadow-xl transition-shadow">
                        <div class="flex items-center gap-2 sm:gap-3">
                            <div class="p-2 sm:p-3 bg-blue-100 rounded-lg group-hover:scale-110 transition-transform flex-shrink-0">
                                <svg class="w-4 h-4 sm:w-6 sm:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h4 class="font-semibold text-gray-800 text-[11px] sm:text-base line-clamp-2">Assignments</h4>
                                <p class="text-[9px] sm:text-sm text-gray-500 line-clamp-1">View all</p>
                            </div>
                        </div>
                    </div>
                </a>

                <a href="{{ route('priest.reservations.calendar') }}" class="card-hover group rounded-2xl overflow-hidden">
                    <div class="card-body p-3 sm:p-4 lg:p-6 bg-white border border-gray-200 shadow-lg hover:shadow-xl transition-shadow">
                        <div class="flex items-center gap-2 sm:gap-3">
                            <div class="p-2 sm:p-3 bg-purple-100 rounded-lg group-hover:scale-110 transition-transform flex-shrink-0">
                                <svg class="w-4 h-4 sm:w-6 sm:h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h4 class="font-semibold text-gray-800 text-[11px] sm:text-base line-clamp-2">Calendar</h4>
                                <p class="text-[9px] sm:text-sm text-gray-500 line-clamp-1">Schedule</p>
                            </div>
                        </div>
                    </div>
                </a>

                <a href="{{ route('priest.reservations.index', ['time' => 'past']) }}" class="card-hover group rounded-2xl overflow-hidden">
                    <div class="card-body p-3 sm:p-4 lg:p-6 bg-white border border-gray-200 shadow-lg hover:shadow-xl transition-shadow">
                        <div class="flex items-center gap-2 sm:gap-3">
                            <div class="p-2 sm:p-3 bg-gray-200 rounded-lg group-hover:scale-110 transition-transform flex-shrink-0">
                                <svg class="w-4 h-4 sm:w-6 sm:h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h4 class="font-semibold text-gray-800 text-[11px] sm:text-base line-clamp-2">Past</h4>
                                <p class="text-[9px] sm:text-sm text-gray-500 line-clamp-1">History</p>
                            </div>
                        </div>
                    </div>
                </a>

                <a href="{{ route('priest.reservations.declined') }}" class="card-hover group rounded-2xl overflow-hidden">
                    <div class="card-body p-3 sm:p-4 lg:p-6 bg-white border border-gray-200 shadow-lg hover:shadow-xl transition-shadow">
                        <div class="flex items-center gap-2 sm:gap-3">
                            <div class="p-2 sm:p-3 bg-rose-100 rounded-lg group-hover:scale-110 transition-transform flex-shrink-0">
                                <svg class="w-4 h-4 sm:w-6 sm:h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h4 class="font-semibold text-gray-800 text-[11px] sm:text-base line-clamp-2">Declined</h4>
                                <p class="text-[9px] sm:text-sm text-gray-500 line-clamp-1">History</p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
