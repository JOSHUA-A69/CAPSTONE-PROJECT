<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    ⛪ My Service Assignments
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Manage your priest assignments and service schedule
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.services.calendar') }}"
                   class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Calendar View
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Toast Container -->
            <div id="toast-root" class="fixed top-4 right-4 z-[9999] space-y-3"></div>

            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="mb-6 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border-l-4 border-green-500 rounded-lg shadow-md overflow-hidden animate-fade-in">
                    <div class="p-4">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0">
                                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <p class="text-green-800 dark:text-green-300 font-medium">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-red-800 dark:text-red-300 font-medium">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            <!-- Filter Tabs -->
            <div class="mb-6 card">
                <div class="card-body">
                    <div class="flex flex-wrap gap-3 items-center justify-between">
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('admin.services.index') }}"
                               class="inline-flex items-center px-4 py-2.5 rounded-lg font-medium transition-all {{ !request('status') && !request('time') ? 'bg-indigo-600 text-white shadow-md' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                                </svg>
                                All Services
                            </a>

                            <a href="{{ route('admin.services.index', ['status' => 'pending_priest_confirmation']) }}"
                               class="inline-flex items-center px-4 py-2.5 rounded-lg font-medium transition-all {{ request('status') === 'pending_priest_confirmation' ? 'bg-yellow-500 text-white shadow-md' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Pending
                                @if($pendingConfirmationCount > 0)
                                    <span class="ml-2 px-2 py-0.5 bg-white text-yellow-700 text-xs font-bold rounded-full">{{ $pendingConfirmationCount }}</span>
                                @endif
                            </a>

                            <a href="{{ route('admin.services.index', ['time' => 'upcoming']) }}"
                               class="inline-flex items-center px-4 py-2.5 rounded-lg font-medium transition-all {{ request('time') === 'upcoming' ? 'bg-green-600 text-white shadow-md' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                                Upcoming
                                @if($upcomingCount > 0)
                                    <span class="ml-2 px-2 py-0.5 bg-white text-green-700 text-xs font-bold rounded-full">{{ $upcomingCount }}</span>
                                @endif
                            </a>

                            <a href="{{ route('admin.services.index', ['time' => 'past']) }}"
                               class="inline-flex items-center px-4 py-2.5 rounded-lg font-medium transition-all {{ request('time') === 'past' ? 'bg-gray-600 text-white shadow-md' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Past Services
                            </a>
                        </div>

                        <a href="{{ route('admin.services.declined') }}"
                           class="inline-flex items-center px-4 py-2.5 bg-red-100 dark:bg-red-900/30 hover:bg-red-200 dark:hover:bg-red-900/50 text-red-700 dark:text-red-300 rounded-lg font-medium transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Declined Services
                        </a>
                    </div>
                </div>
            </div>

            <!-- Reservations List -->
            <div class="card">
                <div class="card-body">

                    @if($reservations->isEmpty())
                        <div class="text-center py-16">
                            <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded-full mb-4">
                                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">No service assignments</h3>
                            <p class="text-gray-600 dark:text-gray-400 max-w-md mx-auto">
                                @if(request('status') === 'pending_priest_confirmation')
                                    You have no pending confirmations at this time. Great job staying on top of your schedule!
                                @elseif(request('time') === 'upcoming')
                                    You have no upcoming services scheduled yet.
                                @elseif(request('time') === 'past')
                                    No past service records found.
                                @else
                                    You have not been assigned as a priest for any services yet.
                                @endif
                            </p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($reservations as $reservation)
                                <div id="card-{{ $reservation->reservation_id }}" class="border dark:border-gray-700 rounded-xl p-5 hover:shadow-lg hover:border-indigo-300 dark:hover:border-indigo-600 transition-all duration-200 bg-gradient-to-r from-white to-gray-50 dark:from-gray-800 dark:to-gray-800/50">
                                    <div class="flex items-start justify-between gap-4 mb-3">
                                        <div class="flex-1">
                                            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-1">
                                                {{ $reservation->activity_name ?? $reservation->service->service_name }}
                                            </h3>
                                            <div class="flex flex-wrap items-center gap-2">
                                                <!-- Status Badge -->
                                                @php
                                                    $statusConfig = [
                                                        'pending' => ['bg' => 'bg-yellow-100 dark:bg-yellow-900/50', 'text' => 'text-yellow-800 dark:text-yellow-200', 'label' => '⏳ Pending', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                                                        'pending_adviser_approval' => ['bg' => 'bg-yellow-100 dark:bg-yellow-900/50', 'text' => 'text-yellow-800 dark:text-yellow-200', 'label' => '⏳ Pending Approval', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                                                        'adviser_approved' => ['bg' => 'bg-blue-100 dark:bg-blue-900/50', 'text' => 'text-blue-800 dark:text-blue-200', 'label' => '📋 Awaiting Admin Approval', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                                                        'pending_priest_confirmation' => ['bg' => 'bg-purple-100 dark:bg-purple-900/50', 'text' => 'text-purple-800 dark:text-purple-200', 'label' => '⏳ Awaiting Your Confirmation', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                                                        'admin_approved' => ['bg' => 'bg-indigo-100 dark:bg-indigo-900/50', 'text' => 'text-indigo-800 dark:text-indigo-200', 'label' => '🆕 New Assignment', 'icon' => 'M12 4v16m8-8H4'],
                                                        'confirmed' => ['bg' => 'bg-green-100 dark:bg-green-900/50', 'text' => 'text-green-800 dark:text-green-200', 'label' => '✅ Confirmed', 'icon' => 'M5 13l4 4L19 7'],
                                                        'completed' => ['bg' => 'bg-gray-100 dark:bg-gray-700', 'text' => 'text-gray-700 dark:text-gray-300', 'label' => '✓ Completed', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                                                    ];
                                                    $config = $statusConfig[$reservation->status] ?? ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'label' => ucfirst($reservation->status), 'icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'];
                                                @endphp

                                                <span id="status-label-{{ $reservation->reservation_id }}" class="inline-flex items-center px-3 py-1.5 text-xs font-bold rounded-full {{ $config['bg'] }} {{ $config['text'] }} shadow-sm">
                                                    {{ $config['label'] }}
                                                </span>

                                                @if($reservation->organization)
                                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                        </svg>
                                                        {{ $reservation->organization->org_name }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Action Buttons -->
                                        <div class="flex flex-wrap gap-2">
                                            <!-- Admin Approve/Reject Buttons (for initial admin handling) -->
                                            @if(in_array($reservation->status, ['pending', 'pending_adviser_approval', 'adviser_approved']))
                                                <button type="button"
                                                        onclick="showApproveModal{{ $reservation->reservation_id }}()"
                                                        class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition-all shadow-sm hover:shadow-md">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    Approve & Assign
                                                </button>

                                                <button type="button"
                                                        onclick="showRejectModal{{ $reservation->reservation_id }}()"
                                                        class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition-all shadow-sm hover:shadow-md">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                    Reject
                                                </button>
                                            @endif

                                            <!-- Priest Confirmation Button (for pending_priest_confirmation status) -->
                                            @if($reservation->status === 'pending_priest_confirmation')
                                                <!-- Approve (Confirm) - opens a sleek confirmation modal -->
                                                <button type="button"
                                                        onclick="openModal('approveConfirmModal{{ $reservation->reservation_id }}')"
                                                        class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition-all shadow-sm hover:shadow-md">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                    Approve
                                                </button>

                                                <!-- Reject (Decline & Reassign) -->
                                                <button type="button"
                                                        onclick="showPriestDeclineModal{{ $reservation->reservation_id }}()"
                                                        class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition-all shadow-sm hover:shadow-md">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                    Reject
                                                </button>
                                            @endif

                                            <a href="{{ route('admin.services.show', $reservation->reservation_id) }}"
                                               class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition-all shadow-sm hover:shadow-md">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                View Details
                                            </a>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                        <div class="flex items-center text-gray-700 dark:text-gray-300">
                                            <div class="flex-shrink-0 w-8 h-8 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center mr-3">
                                                <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <div class="font-semibold">{{ $reservation->schedule_date->format('M d, Y') }}</div>
                                                <div class="text-xs text-gray-500">{{ date('g:i A', strtotime($reservation->schedule_time)) }}</div>
                                            </div>
                                        </div>

                                        <div class="flex items-center text-gray-700 dark:text-gray-300">
                                            <div class="flex-shrink-0 w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center mr-3">
                                                <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <div class="font-medium">
                                                    @if($reservation->custom_venue_name)
                                                        {{ $reservation->custom_venue_name }}
                                                    @else
                                                        {{ $reservation->venue->name }}
                                                    @endif
                                                </div>
                                                <div class="text-xs text-gray-500">Venue</div>
                                            </div>
                                        </div>

                                        <div class="flex items-center text-gray-700 dark:text-gray-300">
                                            <div class="flex-shrink-0 w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center mr-3">
                                                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <div class="font-medium">{{ $reservation->user->full_name }}</div>
                                                <div class="text-xs text-gray-500">Requestor</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modals for Approve/Reject (placed AFTER card but INSIDE foreach) -->
                                @if(in_array($reservation->status, ['pending', 'pending_adviser_approval', 'adviser_approved']))
                                    <!-- Approve Modal -->
                                    <div id="approveModal{{ $reservation->reservation_id }}" class="hidden fixed inset-0 bg-gray-900 bg-opacity-75 overflow-y-auto h-full w-full z-50 p-4" onclick="if(event.target === this) closeApproveModal{{ $reservation->reservation_id }}()">
                                        <div class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-2xl transform transition-all">
                                            <div class="p-6">
                                                <div class="flex items-center justify-between mb-6">
                                                    <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Approve & Assign</h3>
                                                    <button type="button" onclick="closeApproveModal{{ $reservation->reservation_id }}()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                                <form method="POST" action="{{ route('admin.reservations.assign-priest', $reservation->reservation_id) }}" class="ajax-form" data-after="toast" data-modal="approveModal{{ $reservation->reservation_id }}">
                                                    @csrf
                                                    <div class="mb-6">
                                                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">Select Priest *</label>
                                                        <select name="officiant_id" required class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent dark:bg-gray-700 dark:text-gray-200 font-medium">
                                                            <option value="">-- Select a Priest --</option>
                                                            @php
                                                                $priests = \App\Models\User::where('role', 'priest')->get();
                                                            @endphp
                                                            @foreach($priests as $priest)
                                                                <option value="{{ $priest->id }}" {{ $reservation->officiant_id == $priest->id ? 'selected' : '' }}>Fr. {{ $priest->first_name }} {{ $priest->last_name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="flex gap-3">
                                                        <button type="button" onclick="closeApproveModal{{ $reservation->reservation_id }}()"
                                                                class="flex-1 px-5 py-3 bg-gray-200 hover:bg-gray-300 dark:bg-gray-600 dark:hover:bg-gray-500 text-gray-800 dark:text-gray-200 font-bold rounded-lg transition-colors">
                                                            Cancel
                                                        </button>
                                                        <button type="submit"
                                                                class="flex-1 px-5 py-3 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-bold rounded-lg transition-all shadow-lg">
                                                            Approve & Assign
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Reject Modal (Admin) -->
                                    <div id="rejectModal{{ $reservation->reservation_id }}" class="hidden fixed inset-0 bg-gray-900 bg-opacity-75 overflow-y-auto h-full w-full z-50 p-4" onclick="if(event.target === this) closeRejectModal{{ $reservation->reservation_id }}()">
                                        <div class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-2xl transform transition-all ring-1 ring-gray-200 dark:ring-gray-700">
                                            <div class="p-6">
                                                <div class="flex items-center justify-between mb-3">
                                                    <div class="flex items-center gap-2">
                                                        <div class="w-8 h-8 rounded-full bg-red-100 text-red-600 flex items-center justify-center">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                        </div>
                                                        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Reject Reservation</h3>
                                                    </div>
                                                    <button type="button" onclick="closeRejectModal{{ $reservation->reservation_id }}()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Your reason will be sent to the requestor. Please be concise and professional.</p>
                                                <form method="POST" action="{{ route('admin.reservations.reject', $reservation->reservation_id) }}" class="ajax-form" data-after="toast" data-modal="rejectModal{{ $reservation->reservation_id }}">
                                                    @csrf
                                                    <div class="mb-4">
                                                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Reason for Rejection *</label>
                                                        <textarea name="reason" rows="5" maxlength="500" required data-counter="reject-count-{{ $reservation->reservation_id }}"
                                                                  class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-gray-200 resize-none"
                                                                  placeholder="Please provide a clear reason for rejecting this reservation..."></textarea>
                                                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400 text-right">
                                                            <span id="reject-count-{{ $reservation->reservation_id }}">0/500</span>
                                                        </div>
                                                    </div>
                                                    <div class="flex gap-3">
                                                        <button type="button" onclick="closeRejectModal{{ $reservation->reservation_id }}()"
                                                                class="flex-1 px-5 py-3 bg-gray-200 hover:bg-gray-300 dark:bg-gray-600 dark:hover:bg-gray-500 text-gray-800 dark:text-gray-200 font-bold rounded-lg transition-colors">
                                                            Cancel
                                                        </button>
                                                        <button type="submit"
                                                                class="flex-1 px-5 py-3 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-bold rounded-lg transition-all shadow-lg">
                                                            Reject Reservation
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <script>
                                        function showApproveModal{{ $reservation->reservation_id }}() {
                                            const m = document.getElementById('approveModal{{ $reservation->reservation_id }}');
                                            m.classList.remove('hidden');
                                            m.classList.add('flex');
                                            document.body.style.overflow = 'hidden';
                                        }
                                        function closeApproveModal{{ $reservation->reservation_id }}() {
                                            const m = document.getElementById('approveModal{{ $reservation->reservation_id }}');
                                            m.classList.add('hidden');
                                            m.classList.remove('flex');
                                            document.body.style.overflow = 'auto';
                                        }
                                        function showRejectModal{{ $reservation->reservation_id }}() {
                                            const m = document.getElementById('rejectModal{{ $reservation->reservation_id }}');
                                            m.classList.remove('hidden');
                                            m.classList.add('flex');
                                            document.body.style.overflow = 'hidden';
                                        }
                                        function closeRejectModal{{ $reservation->reservation_id }}() {
                                            const m = document.getElementById('rejectModal{{ $reservation->reservation_id }}');
                                            m.classList.add('hidden');
                                            m.classList.remove('flex');
                                            document.body.style.overflow = 'auto';
                                        }
                                    </script>
                                @endif

                                <!-- Approve Confirmation Modal (for pending_priest_confirmation) -->
                                @if($reservation->status === 'pending_priest_confirmation')
                                    <div id="approveConfirmModal{{ $reservation->reservation_id }}" class="hidden fixed inset-0 bg-gray-900 bg-opacity-75 overflow-y-auto h-full w-full z-50 p-4" onclick="if(event.target === this) closeModal('approveConfirmModal{{ $reservation->reservation_id }}')">
                                        <div class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-2xl transform transition-all ring-1 ring-gray-200 dark:ring-gray-700">
                                            <div class="p-6">
                                                <div class="flex items-center justify-between mb-3">
                                                    <div class="flex items-center gap-2">
                                                        <div class="w-8 h-8 rounded-full bg-green-100 text-green-600 flex items-center justify-center">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                        </div>
                                                        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Confirm Availability</h3>
                                                    </div>
                                                    <button type="button" onclick="closeModal('approveConfirmModal{{ $reservation->reservation_id }}')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    </button>
                                                </div>
                                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">Confirm your availability for this service. The requestor will be notified and the status will be updated to <span class="font-semibold">Confirmed</span>.</p>
                                                <div class="flex gap-3">
                                                    <button type="button" onclick="closeModal('approveConfirmModal{{ $reservation->reservation_id }}')" class="flex-1 px-5 py-3 bg-gray-200 hover:bg-gray-300 dark:bg-gray-600 dark:hover:bg-gray-500 text-gray-800 dark:text-gray-200 font-bold rounded-lg transition-colors">Cancel</button>
                                                    <button type="button" onclick="confirmService('{{ route('admin.services.confirm', $reservation->reservation_id) }}', {{ $reservation->reservation_id }}, 'approveConfirmModal{{ $reservation->reservation_id }}')" class="flex-1 px-5 py-3 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-bold rounded-lg transition-all shadow-lg">Confirm</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- Priest Decline & Reassign Modal (only for pending_priest_confirmation) -->
                                @if($reservation->status === 'pending_priest_confirmation')
                                    <div id="priestDeclineModal{{ $reservation->reservation_id }}" class="hidden fixed inset-0 bg-gray-900 bg-opacity-75 overflow-y-auto h-full w-full z-50 p-4" onclick="if(event.target === this) closePriestDeclineModal{{ $reservation->reservation_id }}()">
                                        <div class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-2xl transform transition-all">
                                            <div class="p-6">
                                                <div class="flex items-center justify-between mb-6">
                                                    <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Decline & Reassign</h3>
                                                    <button type="button" onclick="closePriestDeclineModal{{ $reservation->reservation_id }}()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                    </button>
                                                </div>

                                                <form method="POST" action="{{ route('admin.services.decline', $reservation->reservation_id) }}" class="ajax-form" data-after="remove-card" data-modal="priestDeclineModal{{ $reservation->reservation_id }}" data-card="card-{{ $reservation->reservation_id }}">
                                                    @csrf
                                                    <div class="space-y-4 mb-6">
                                                        <div>
                                                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Reassign to Priest *</label>
                                                            <select name="new_priest_id" required class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-gray-200 font-medium">
                                                                <option value="">-- Select a Priest --</option>
                                                                @php
                                                                    $priests = \App\Models\User::whereIn('role', ['priest', 'admin'])->where('id', '!=', auth()->id())->get();
                                                                @endphp
                                                                @foreach($priests as $priest)
                                                                    <option value="{{ $priest->id }}">Fr. {{ $priest->first_name }} {{ $priest->last_name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div>
                                                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Reason (optional)</label>
                                                            <textarea name="reason" rows="4" class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-gray-200 resize-none" placeholder="Provide a reason (e.g., schedule conflict)"></textarea>
                                                        </div>
                                                    </div>

                                                    <div class="flex gap-3">
                                                        <button type="button" onclick="closePriestDeclineModal{{ $reservation->reservation_id }}()" class="flex-1 px-5 py-3 bg-gray-200 hover:bg-gray-300 dark:bg-gray-600 dark:hover:bg-gray-500 text-gray-800 dark:text-gray-200 font-bold rounded-lg transition-colors">Cancel</button>
                                                        <button type="submit" class="flex-1 px-5 py-3 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-bold rounded-lg transition-all shadow-lg">Decline & Reassign</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <script>
                                        function showPriestDeclineModal{{ $reservation->reservation_id }}() {
                                            const m = document.getElementById('priestDeclineModal{{ $reservation->reservation_id }}');
                                            m.classList.remove('hidden');
                                            m.classList.add('flex');
                                            document.body.style.overflow = 'hidden';
                                        }
                                        function closePriestDeclineModal{{ $reservation->reservation_id }}() {
                                            const m = document.getElementById('priestDeclineModal{{ $reservation->reservation_id }}');
                                            m.classList.add('hidden');
                                            m.classList.remove('flex');
                                            document.body.style.overflow = 'auto';
                                        }
                                    </script>
                                @endif
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        @if($reservations->hasPages())
                            <div class="mt-6 border-t dark:border-gray-700 pt-6">
                                {{ $reservations->links() }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    // CSRF helper
    function csrfToken() {
        const el = document.querySelector('meta[name="csrf-token"]');
        return el ? el.getAttribute('content') : '{{ csrf_token() }}';
    }

    // Toasts
    function showToast(message, type = 'success') {
        const root = document.getElementById('toast-root');
        if (!root) return alert(message);
        const colors = type === 'success' ? 'bg-green-600 text-white' : (type === 'error' ? 'bg-red-600 text-white' : 'bg-gray-800 text-white');
        const toast = document.createElement('div');
        toast.className = `${colors} shadow-lg rounded-lg px-4 py-3 flex items-center gap-2`;
        toast.innerHTML = `
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${type==='success' ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12'}"></path>
            </svg>
            <span class="font-medium">${message}</span>
        `;
        root.appendChild(toast);
        setTimeout(() => toast.remove(), 3500);
    }

    // Open/Close modal helpers
    function openModal(id) {
        const el = document.getElementById(id);
        if (el) el.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    // Close modal by id
    function closeModal(id) {
        const el = document.getElementById(id);
        if (el) el.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Intercept AJAX forms
    document.addEventListener('submit', async (e) => {
        const form = e.target.closest('form.ajax-form');
        if (!form) return;
        e.preventDefault();
        const url = form.getAttribute('action');
        const modalId = form.dataset.modal;
        const after = form.dataset.after || 'toast';
        const cardId = form.dataset.card;
        try {
            const fd = new FormData(form);
            const res = await fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' }, body: fd });
            const data = await res.json().catch(() => ({ success: res.ok }));
            if (!res.ok) throw new Error(data.message || 'Request failed');
            if (modalId) closeModal(modalId);
            if (after === 'remove-card' && cardId) {
                const node = document.getElementById(cardId);
                if (node) node.remove();
            }
            showToast(data.message || 'Action completed successfully');
        } catch (err) {
            console.error(err);
            showToast(err.message || 'Something went wrong', 'error');
        }
    });

    // Confirm service (pending_priest_confirmation)
    async function confirmService(url, reservationId, modalId) {
        try {
            const res = await fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' } });
            const data = await res.json().catch(() => ({ success: res.ok }));
            if (!res.ok) throw new Error(data.message || 'Request failed');
            if (modalId) closeModal(modalId);

            const badge = document.getElementById(`status-label-${reservationId}`);
            if (badge) {
                badge.textContent = '✅ Confirmed';
                badge.className = 'inline-flex items-center px-3 py-1.5 text-xs font-bold rounded-full bg-green-100 text-green-800 shadow-sm';
            }

            // Remove approve/reject buttons inside this card
            const card = document.getElementById(`card-${reservationId}`);
            if (card) {
                card.querySelectorAll('button').forEach(btn => {
                    if (/Approve|Reject/i.test(btn.textContent)) btn.remove();
                });
            }

            showToast(data.message || 'Reservation approved successfully');
        } catch (err) {
            console.error(err);
            showToast(err.message || 'Failed to approve reservation', 'error');
        }
    }

    // Character counter for any textarea with data-counter
    document.addEventListener('input', (e) => {
        const t = e.target;
        if (t && t.matches('textarea[data-counter]')) {
            const target = document.getElementById(t.dataset.counter);
            if (target) target.textContent = `${t.value.length}/${t.getAttribute('maxlength') || 500}`;
        }
    });
</script>
