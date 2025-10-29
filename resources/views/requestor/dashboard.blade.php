<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-heading font-semibold text-xl leading-tight">
                📊 Requestor Dashboard
            </h2>
            <a href="{{ route('requestor.reservations.create') }}" class="btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New Request
            </a>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Welcome Banner -->
            <div class="card mb-6">
                <div class="card-body">
                    <div class="flex items-center justify-between flex-wrap gap-4">
                        <div>
                            <h3 class="text-2xl sm:text-3xl font-bold mb-2 text-heading">
                                Welcome back, {{ auth()->user()->first_name ?? 'User' }}! 👋
                            </h3>
                            <p class="text-muted text-sm sm:text-base">
                                Manage your spiritual activity requests and track their status
                            </p>
                        </div>
                        @php
                            $nextReservation = \App\Models\Reservation::where('user_id', auth()->id())
                                ->whereIn('status', ['approved', 'confirmed'])
                                ->where('schedule_date', '>=', now())
                                ->orderBy('schedule_date')
                                ->first();
                        @endphp
                        @if($nextReservation)
                            <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-lg p-4 text-center min-w-[120px]">
                                <div class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase mb-1">Next Event</div>
                                <div class="text-lg font-bold text-heading">{{ $nextReservation->schedule_date->format('M d') }}</div>
                                <div class="text-xs text-muted">{{ $nextReservation->schedule_date->diffForHumans() }}</div>
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
            @endphp

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6">
                <!-- Total Requests -->
                <a href="{{ route('requestor.reservations.index') }}" class="card-hover border-l-4 border-indigo-500 transition-transform hover:scale-105 cursor-pointer">
                    <div class="card-body">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-muted mb-2">Total Requests</p>
                                <p class="text-3xl font-bold text-heading">{{ $totalReservations }}</p>
                            </div>
                            <div class="bg-indigo-100 dark:bg-indigo-900/30 rounded-full p-3">
                                <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- Pending Approval -->
                <a href="{{ route('requestor.reservations.index', ['status' => 'pending']) }}" class="card-hover border-l-4 border-yellow-500 transition-transform hover:scale-105 cursor-pointer">
                    <div class="card-body">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-muted mb-2">Pending</p>
                                <p class="text-3xl font-bold text-heading">{{ $pendingCount }}</p>
                            </div>
                            <div class="bg-yellow-100 dark:bg-yellow-900/30 rounded-full p-3">
                                <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- Approved -->
                <a href="{{ route('requestor.reservations.index', ['status' => 'approved']) }}" class="card-hover border-l-4 border-green-500 transition-transform hover:scale-105 cursor-pointer">
                    <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted mb-2">Approved</p>
                            <p class="text-3xl font-bold text-heading">{{ $approvedCount }}</p>
                        </div>
                        <div class="bg-green-100 dark:bg-green-900/30 rounded-full p-3">
                            <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    </div>
                </a>

                <!-- Upcoming Events -->
                <a href="{{ route('requestor.reservations.index', ['status' => 'upcoming']) }}" class="card-hover border-l-4 border-purple-500 transition-transform hover:scale-105 cursor-pointer">
                    <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted mb-2">Upcoming</p>
                            <p class="text-3xl font-bold text-heading">{{ $upcomingCount }}</p>
                        </div>
                        <div class="bg-purple-100 dark:bg-purple-900/30 rounded-full p-3">
                            <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>
                    </div>
                </a>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Recent Activity -->
                <div class="lg:col-span-2 card overflow-hidden">
                    <div class="card-header">
                        <h3 class="text-lg font-semibold text-heading flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Recent Activity
                        </h3>
                    </div>
                    <div class="p-6">
                        @php
                            $recentReservations = \App\Models\Reservation::with(['service', 'history'])
                                ->where('user_id', auth()->id())
                                ->orderByDesc('created_at')
                                ->take(5)
                                ->get();
                        @endphp

                        @if($recentReservations->isEmpty())
                            <div class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                <p class="mt-4 text-sm text-muted">No activity yet</p>
                                <a href="{{ route('requestor.reservations.create') }}" class="btn-primary inline-flex mt-4">
                                    Create Your First Request
                                </a>
                            </div>
                        @else
                            <div class="space-y-4">
                                @foreach($recentReservations as $reservation)
                                    <div class="flex items-start space-x-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                        <div class="flex-shrink-0">
                                            <div class="w-10 h-10 rounded-full flex items-center justify-center
                                                {{ $reservation->status === 'confirmed' ? 'bg-green-100 dark:bg-green-900/30' :
                                                   ($reservation->status === 'cancelled' || $reservation->status === 'rejected' ? 'bg-red-100 dark:bg-red-900/30' : 'bg-yellow-100 dark:bg-yellow-900/30') }}">
                                                @if($reservation->status === 'confirmed')
                                                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                @elseif($reservation->status === 'cancelled' || $reservation->status === 'rejected')
                                                    <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                @else
                                                    <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-heading truncate">
                                                {{ $reservation->activity_name }}
                                            </p>
                                            <p class="text-sm text-muted">
                                                {{ $reservation->service->service_name ?? 'N/A' }} • {{ $reservation->schedule_date->format('M d, Y') }}
                                            </p>
                                            <p class="text-xs text-muted mt-1">
                                                {{ $reservation->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                        <div>
                                            <a href="{{ route('requestor.reservations.show', $reservation->reservation_id) }}" class="btn-ghost btn-sm">
                                                View →
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-4 text-center border-t pt-4">
                                <a href="{{ route('requestor.reservations.index') }}" class="btn-ghost inline-flex">
                                    View All Reservations →
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Important Info -->
                <div class="space-y-6">
                    <div class="bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 rounded-xl shadow-sm p-6 border border-amber-200 dark:border-amber-800">
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-amber-600 dark:text-amber-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <h4 class="font-semibold text-amber-900 dark:text-amber-300 mb-2">Important Reminder</h4>
                                <ul class="text-sm text-amber-800 dark:text-amber-400 space-y-1">
                                    <li>• Submit requests at least 7 days in advance</li>
                                    <li>• Check your email for updates</li>
                                    <li>• Respond to confirmations promptly</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
