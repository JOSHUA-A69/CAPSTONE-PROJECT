<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Service Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Toast Container -->
            <div id="toast-root" class="fixed top-4 right-4 z-[9999] space-y-3"></div>

            <!-- Header Actions - Enhanced Layout -->
            <div class="mb-8">
                <!-- Page Title & Action Buttons Row -->
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Service Management</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Manage and track your service assignments</p>
                    </div>

                    <!-- Primary Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-3 lg:flex-shrink-0">
                        <a href="{{ route('admin.services.declined') }}"
                           class="inline-flex items-center justify-center px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 hover:shadow-md transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                            <svg class="w-4 h-4 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            <span class="hidden sm:inline">Declined Services</span>
                            <span class="sm:hidden">Declined</span>
                        </a>

                        <a href="{{ route('admin.calendar.index') }}"
                           class="inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-sm hover:shadow-md transform hover:-translate-y-0.5 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span class="hidden sm:inline">Calendar View</span>
                            <span class="sm:hidden">Calendar</span>
                        </a>
                    </div>
                </div>

                <!-- Filter Tabs - Enhanced Design -->
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm p-1.5">
                    <nav class="flex flex-wrap gap-1" aria-label="Service filters">
                        <a href="{{ route('admin.services.index') }}"
                           class="flex-1 sm:flex-none inline-flex items-center justify-center px-4 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 {{ !request('status') && !request('time') ? 'bg-indigo-600 text-white shadow-md' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}">
                            <svg class="w-4 h-4 mr-2 sm:mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14-4H5m14 8H5m14 4H5"></path>
                            </svg>
                            <span class="hidden sm:inline">All Services</span>
                            <span class="sm:hidden">All</span>
                        </a>

                        <a href="{{ route('admin.services.index', ['status' => 'pending_priest_confirmation']) }}"
                           class="flex-1 sm:flex-none inline-flex items-center justify-center px-4 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 {{ request('status') === 'pending_priest_confirmation' ? 'bg-yellow-500 text-white shadow-md' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}">
                            <svg class="w-4 h-4 mr-2 sm:mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            @if($pendingConfirmationCount > 0)
                                <span class="mr-1.5 px-1.5 py-0.5 bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 text-xs font-bold rounded-full">{{ $pendingConfirmationCount }}</span>
                            @endif
                            <span class="hidden sm:inline">Pending</span>
                            <span class="sm:hidden">Pending</span>
                        </a>

                        <a href="{{ route('admin.services.index', ['time' => 'upcoming']) }}"
                           class="flex-1 sm:flex-none inline-flex items-center justify-center px-4 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 {{ request('time') === 'upcoming' ? 'bg-green-600 text-white shadow-md' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}">
                            <svg class="w-4 h-4 mr-2 sm:mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                            @if($upcomingCount > 0)
                                <span class="mr-1.5 px-1.5 py-0.5 bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 text-xs font-bold rounded-full">{{ $upcomingCount }}</span>
                            @endif
                            <span class="hidden sm:inline">Upcoming</span>
                            <span class="sm:hidden">Upcoming</span>
                        </a>

                        <a href="{{ route('admin.services.index', ['time' => 'past']) }}"
                           class="flex-1 sm:flex-none inline-flex items-center justify-center px-4 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 {{ request('time') === 'past' ? 'bg-gray-600 text-white shadow-md' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}">
                            <svg class="w-4 h-4 mr-2 sm:mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="hidden sm:inline">Past</span>
                            <span class="sm:hidden">Past</span>
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Notifications -->
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/30 border-l-4 border-green-500 rounded-r-lg shadow-sm flex items-start gap-3">
                     <div class="flex-shrink-0 text-green-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div>
                        <p class="font-medium text-green-800 dark:text-green-200">Success</p>
                        <p class="text-sm text-green-600 dark:text-green-300 mt-0.5">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 rounded-r-lg shadow-sm flex items-start gap-3">
                     <div class="flex-shrink-0 text-red-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div>
                        <p class="font-medium text-red-800 dark:text-red-200">Error</p>
                        <p class="text-sm text-red-600 dark:text-red-300 mt-0.5">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            <!-- Services List Container -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 dark:border-gray-700">
                <div class="p-6">
                    @if($reservations->isEmpty())
                        <div class="text-center py-16">
                            <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded-full mb-4">
                                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">No service assignments found</h3>
                            <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                                @if(request('status') === 'pending_priest_confirmation')
                                    No pending confirmation requests at the moment.
                                @elseif(request('time') === 'upcoming')
                                    No upcoming services scheduled.
                                @elseif(request('time') === 'past')
                                    No past service records.
                                @else
                                    It looks like there are no service assignments matching your criteria.
                                @endif
                            </p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($reservations as $reservation)
                                <div id="card-{{ $reservation->reservation_id }}" class="group relative bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-5 hover:shadow-lg transition-all duration-200 hover:border-blue-300 dark:hover:border-blue-700">
                                    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">

                                        <!-- Content -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-3 mb-2">
                                                 <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 truncate">
                                                    {{ $reservation->activity_name ?? $reservation->service->service_name }}
                                                </h3>

                                                <!-- Status Badge -->
                                                @php
                                                    $statusColors = [
                                                        'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
                                                        'pending_adviser_approval' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
                                                        'adviser_approved' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                                                        'pending_priest_confirmation' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
                                                        'admin_approved' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300',
                                                        'confirmed' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                                        'completed' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                                        'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                                                    ];
                                                    $statusLabels = [
                                                        'pending' => 'Pending',
                                                        'pending_adviser_approval' => 'Pending Dept. Approval',
                                                        'adviser_approved' => 'Dept. Approved',
                                                        'pending_priest_confirmation' => 'Needs Confirmation',
                                                        'admin_approved' => 'Admin Approved',
                                                        'confirmed' => 'Confirmed',
                                                        'completed' => 'Completed',
                                                        'cancelled' => 'Cancelled'
                                                    ];
                                                    $statusKey = $reservation->status;
                                                    $badgeClass = $statusColors[$statusKey] ?? 'bg-gray-100 text-gray-800';
                                                    $badgeLabel = $statusLabels[$statusKey] ?? ucfirst($statusKey);
                                                @endphp
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">
                                                    {{ $badgeLabel }}
                                                </span>
                                            </div>

                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2 text-sm text-gray-600 dark:text-gray-400">
                                                <div class="flex items-center gap-2">
                                                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                    {{ \Carbon\Carbon::parse($reservation->reservation_date)->format('F d, Y') }}
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    {{ \Carbon\Carbon::parse($reservation->reservation_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($reservation->reservation_end_time)->format('h:i A') }}
                                                </div>
                                                @if($reservation->organization)
                                                <div class="flex items-center gap-2 sm:col-span-2">
                                                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                                    {{ $reservation->organization->org_name }}
                                                </div>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Actions -->
                                        <div class="flex items-center gap-3 pt-4 md:pt-0 pl-0 md:pl-4 md:border-l border-gray-100 dark:border-gray-700">
                                            @if(($reservation->officiant_id === auth()->id()) && $reservation->status === 'pending_priest_confirmation')
                                                <div class="flex gap-2">
                                                    <form method="POST" action="{{ route('admin.services.confirm', $reservation->reservation_id) }}">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded-md shadow-sm transition-colors">
                                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                            Confirm
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="{{ route('admin.services.decline', $reservation->reservation_id) }}">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-md shadow-sm transition-colors">
                                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                            Decline
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif

                                            <a href="{{ route('admin.services.show', $reservation->reservation_id) }}" class="inline-flex items-center px-3 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 text-xs font-semibold rounded-md shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                                                View Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-6">
                            {{ $reservations->withQueryString()->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
