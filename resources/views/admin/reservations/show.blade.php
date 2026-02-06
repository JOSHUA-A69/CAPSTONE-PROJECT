<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 items-start">
            <h2 class="text-heading text-2xl font-bold text-gray-800 dark:text-gray-200 whitespace-nowrap">
                Reservation Details
            </h2>

            <a href="{{ route('admin.reservations.index') }}" class="btn-ghost whitespace-nowrap self-start">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Reservations
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Success/Error Messages -->
            @if(session('status'))
                <div class="mb-6">
                    <span class="badge-success">
                        <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        {{ session('message', session('status')) }}
                    </span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6">
                    <span class="badge-danger">
                        <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        {{ session('error') }}
                    </span>
                </div>
            @endif

            <!-- Status Banner -->
            @php
                $statusColor = match($reservation->status) {
                    'approved', 'confirmed', 'completed' => 'border-green-500',
                    'rejected', 'cancelled' => 'border-red-500',
                    'admin_approved' => 'border-blue-500', // Ready for final approval
                    default => 'border-yellow-500'
                };
                
                $statusLabel = match($reservation->status) {
                    'approved' => 'Approved by Admin',
                    'admin_approved' => ($reservation->priest_confirmation === 'confirmed' || $reservation->allPriestsConfirmed()) 
                        ? 'Ready for Final Approval' 
                        : 'Awaiting Priest Confirmation',
                    'adviser_approved' => ($reservation->priest_confirmation === 'confirmed' || $reservation->allPriestsConfirmed())
                        ? 'Ready for Final Approval (Status Pending)' 
                        : 'Awaiting Priest',
                    'pending' => 'Awaiting Adviser',
                    'confirmed' => 'Confirmed',
                    'completed' => 'Completed',
                    'rejected' => 'Rejected',
                    'cancelled' => 'Cancelled',
                    default => ucfirst(str_replace('_', ' ', $reservation->status))
                };
            @endphp
            <div class="card border-l-4 {{ $statusColor }} mb-6">
                <div class="card-body">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h3 class="form-label mb-2">Current Status</h3>
                            @if(in_array($reservation->status, ['approved', 'confirmed', 'completed']))
                                <span class="badge-success text-lg">{{ $statusLabel }}</span>
                            @elseif(in_array($reservation->status, ['rejected', 'cancelled']))
                                <span class="badge-danger text-lg">{{ $statusLabel }}</span>
                            @elseif($reservation->status === 'admin_approved')
                                <span class="badge-info text-lg">{{ $statusLabel }}</span>
                            @else
                                <span class="badge-warning text-lg">{{ $statusLabel }}</span>
                            @endif
                        </div>
                        <div class="text-left sm:text-right">
                            <p class="text-muted text-sm">Submitted on</p>
                            <p class="text-heading font-medium">{{ $reservation->created_at->format('M d, Y h:i A') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column: Main Details -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Reservation Details Card -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="flex items-center">
                                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Reservation Details
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="form-label">Service</label>
                                    <p class="text-heading">{{ $reservation->service->service_name ?? '—' }}</p>
                                </div>

                                <div>
                                    <label class="form-label">Venue</label>
                                    <p class="text-heading">
                                        @if($reservation->custom_venue_name)
                                            {{ $reservation->custom_venue_name }}
                                            <span class="badge-info ml-2">Custom</span>
                                        @else
                                            {{ $reservation->venue->name ?? '—' }}
                                        @endif
                                    </p>
                                </div>

                                <div>
                                    <label class="form-label">Schedule</label>
                                    <p class="text-heading text-indigo-600">
                                        {{ optional($reservation->schedule_date)->format('M d, Y') }}<br>
                                        <span class="text-sm">{{ optional($reservation->schedule_date)->format('h:i A') }}</span>
                                    </p>
                                </div>

                                <div>
                                    <label class="form-label">Participants</label>
                                    <p class="text-heading">{{ $reservation->participants_count ?? '—' }} people</p>
                                </div>

                                @if($reservation->activity_name)
                                <div class="col-span-2">
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Activity Name</label>
                                    <p class="mt-1 text-base font-semibold">{{ $reservation->activity_name }}</p>
                                </div>
                                @endif

                                @if($reservation->theme)
                                <div class="col-span-2">
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Theme</label>
                                    <p class="mt-1 text-base">{{ $reservation->theme }}</p>
                                </div>
                                @endif

                                @if($reservation->purpose)
                                <div class="col-span-2">
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Purpose</label>
                                    <p class="mt-1 text-base">{{ $reservation->purpose }}</p>
                                </div>
                                @endif

                                @if($reservation->details)
                                <div class="col-span-2">
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Additional Details</label>
                                    <p class="mt-1 text-base">{{ $reservation->details }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Requester Information -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-semibold mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Requester Information
                            </h3>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</label>
                                    <p class="mt-1 text-base">{{ $reservation->user->name ?? '—' }}</p>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</label>
                                    <p class="mt-1 text-base">{{ $reservation->user->email ?? '—' }}</p>
                                </div>

                                @if($reservation->organization)
                                <div class="col-span-2">
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Organization</label>
                                    <p class="mt-1 text-base font-semibold">{{ $reservation->organization->org_name }}</p>
                                    @if($reservation->organization->adviser)
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                            Adviser: {{ $reservation->organization->adviser->name }}
                                        </p>
                                    @endif
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Right Column: Priest Assignment & Actions -->
                <div class="lg:col-span-1 space-y-6">

                    <!-- External Priest Info -->
                    @if($reservation->priest_selection_type === 'external' && $reservation->external_priest_name)
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4 flex items-center text-gray-900 dark:text-gray-100">
                                <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                External Priest
                            </h3>

                            <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-200 dark:border-blue-700">
                                <div class="space-y-3">
                                    <div>
                                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Priest Name</label>
                                        <p class="font-semibold text-lg text-gray-900 dark:text-gray-100">{{ $reservation->external_priest_name }}</p>
                                    </div>
                                    
                                    @if($reservation->external_priest_contact)
                                    <div>
                                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Contact Information</label>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $reservation->external_priest_contact }}</p>
                                    </div>
                                    @else
                                    <div>
                                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Contact Information</label>
                                        <p class="text-sm text-gray-500 dark:text-gray-500 italic">Not provided</p>
                                    </div>
                                    @endif

                                    <div class="pt-2 border-t border-blue-200 dark:border-blue-700">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                            </svg>
                                            External (Non-SVD)
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Assigned Priest Info (for SVD priests) -->
                    @php
                        $assignedPriests = isset($reservation->priests) ? $reservation->priests : collect();
                    @endphp
                    @if($assignedPriests->count() > 0 && $reservation->priest_selection_type !== 'external')
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4 flex items-center text-gray-900 dark:text-gray-100">
                                <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Assigned Priest{{ $assignedPriests->count() > 1 ? 's' : '' }}
                            </h3>

                            <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg border border-purple-200 dark:border-purple-700 space-y-3">
                                @foreach($assignedPriests as $p)
                                <div>
                                    <p class="font-semibold text-lg text-gray-900 dark:text-gray-100">{{ $p->full_name ?? $p->name }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $p->email }}</p>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @elseif($reservation->officiant && $reservation->priest_selection_type !== 'external')
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4 flex items-center text-gray-900 dark:text-gray-100">
                                <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Assigned Priest
                            </h3>

                            <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg border border-purple-200 dark:border-purple-700">
                                <p class="font-semibold text-lg text-gray-900 dark:text-gray-100">{{ $reservation->officiant->full_name ?? $reservation->officiant->name }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $reservation->officiant->email }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Admin/Priest Decline Action -->
                    @php 
                        $authIsAssignedPriest = auth()->id() === optional($reservation->officiant)->id 
                                             || $reservation->priests->contains('id', auth()->id());
                        
                        // Check if I have already declined
                        $myPivot = $reservation->priests->where('id', auth()->id())->first();
                        $hasDeclined = $reservation->status === 'priest_declined' 
                                    || ($myPivot && $myPivot->pivot->confirmation_status === 'declined');
                    @endphp

                    @if($authIsAssignedPriest && !$hasDeclined && !in_array($reservation->status, ['cancelled', 'rejected', 'completed', 'approved']))
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6 border-l-4 border-red-500">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Decline Assignment
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                You are currently assigned to this reservation. If you cannot make it, please decline to allow reassignment.
                            </p>
                            
                            <form action="{{ route('admin.reservations.decline-assignment', $reservation->reservation_id) }}" method="POST"
                                  onsubmit="return confirm('Are you sure you want to decline this assignment?');">
                                @csrf
                                <div class="mb-4">
                                    <label for="decline_reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Reason (Required)</label>
                                    <textarea name="reason" id="decline_reason" rows="2" required 
                                              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500"
                                              placeholder="Why are you declining?"></textarea>
                                    @error('reason')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="mb-4">
                                    <label for="replacement_priest_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Assign Replacement (Optional)</label>
                                    <select name="replacement_priest_id" id="replacement_priest_id" 
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">-- I will assign later --</option>
                                        @foreach($availablePriests as $priest)
                                            @if($priest->is_available && $priest->id !== auth()->id())
                                                <option value="{{ $priest->id }}">Fr. {{ $priest->first_name }} {{ $priest->last_name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                    @error('replacement_priest_id')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">If you select a replacement, they will be assigned immediately.</p>
                                </div>
                                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md text-sm font-semibold hover:bg-red-700 transition">
                                    Decline & Reassign
                                </button>
                            </form>
                        </div>
                    </div>
                    @endif

                    <!-- Assign Priest Form (only shown when priest has declined/rejected OR when pending assignment) -->
                    @if($reservation->priest_selection_type !== 'external'
                        && (in_array($reservation->status, ['priest_declined', 'pending_priest_reassignment'])
                            || ($reservation->status === 'adviser_approved' && !$reservation->officiant_id))
                        )
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">
                                Assign Priest
                            </h3>
                            @if(in_array($reservation->status, ['priest_declined','pending_priest_reassignment']))
                                <div class="mb-4 p-3 bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-700 rounded text-sm text-purple-800 dark:text-purple-300">
                                    This reservation's priest slot is open due to a prior decline. Please assign a new priest.
                                </div>
                            @elseif($reservation->status === 'adviser_approved')
                                <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded text-sm text-blue-800 dark:text-blue-300">
                                    Requestor selected "Any Available Priest". Please assign a priest to proceed.
                                </div>
                            @endif

                            <form action="{{ route('admin.reservations.assign-priest', $reservation->reservation_id) }}" method="POST">
                                @csrf

                                <div class="mb-4">
                                    <label for="officiant_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Select Available Priest
                                    </label>
                                    <select name="officiant_id" id="officiant_id" required
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                        <option value="">-- Select Priest --</option>
                                        @foreach($availablePriests as $priest)
                                            <option value="{{ $priest->id }}"
                                                {{ old('officiant_id') == $priest->id ? 'selected' : '' }}>
                                                {{ $priest->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('officiant_id')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <button type="submit"
                                        class="w-full px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 transition">
                                    Assign Priest & Approve
                                </button>
                            </form>

                            @if($availablePriests->isEmpty())
                                <div class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded text-sm text-yellow-800 dark:text-yellow-400">
                                    ⚠️ No priests available for this schedule. All priests may have conflicting reservations.
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Self Approve & Confirm block for admin who is already the assigned priest -->
                    @if($authIsAssignedPriest && in_array($reservation->status, ['adviser_approved','pending']))
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Approve & Confirm (You Are Assigned)</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">You are already the assigned priest for this reservation. Approve and confirm your availability in one step.</p>
                            <form action="{{ route('admin.reservations.assign-priest', $reservation->reservation_id) }}" method="POST">
                                @csrf
                                <button type="submit"
                                        class="w-full px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 transition"
                                        onclick="return confirm('Approve and confirm your availability for this reservation?');">
                                    Approve & Confirm Availability
                                </button>
                            </form>
                        </div>
                    </div>
                    @endif

                    <!-- Confirm External Priest Reservation -->
                    @if($reservation->priest_selection_type === 'external' && in_array($reservation->status, ['pending', 'adviser_approved']))
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Confirm External Priest</h3>

                            <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded text-sm text-blue-800 dark:text-blue-400">
                                <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                The requestor has provided an external priest. Review the priest details above and confirm to approve this reservation.
                            </div>

                            <form action="{{ route('admin.reservations.confirm-external', $reservation->reservation_id) }}" method="POST">
                                @csrf

                                <div class="mb-4">
                                    <label for="admin_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Admin Notes (optional)
                                    </label>
                                    <textarea name="admin_notes" id="admin_notes" rows="3"
                                              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-green-500 focus:ring-green-500"
                                              placeholder="Add any notes about this external priest confirmation...">{{ old('admin_notes') }}</textarea>
                                    @error('admin_notes')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <button type="submit"
                                        class="w-full px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 transition"
                                        onclick="return confirm('Confirm this reservation with the external priest?');">
                                    <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    Confirm & Approve Reservation
                                </button>
                            </form>
                        </div>
                    </div>
                    @endif

                    <!-- Final Approve Button - When all priests have confirmed and reservation is awaiting admin final approval -->
                    @if($reservation->status === 'admin_approved' || ($reservation->status === 'adviser_approved' && ($reservation->priest_confirmation === 'confirmed' || $reservation->allPriestsConfirmed() || $reservation->officiant_id || $reservation->priests->isNotEmpty())))
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border-2 border-green-500 dark:border-green-400">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4 text-green-700 dark:text-green-300 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Final Approval {{ $reservation->status === 'adviser_approved' ? '(Override)' : 'Required' }}
                            </h3>

                            @if(!($reservation->priest_confirmation === 'confirmed' || $reservation->allPriestsConfirmed()))
                            <div class="mb-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded text-sm text-yellow-800 dark:text-yellow-300">
                                <p class="font-medium mb-1">⚠️ Note: Priest confirmation is still pending.</p>
                                <p>You can force-approve this reservation if you have confirmed the schedule offline.</p>
                            </div>
                            @elseif($reservation->status === 'admin_approved' || ($reservation->priest_confirmation === 'confirmed' || $reservation->allPriestsConfirmed()))
                            <div class="mb-4 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded text-sm text-green-800 dark:text-green-300">
                                <p class="font-medium mb-2">✅ All priests have confirmed their availability</p>
                                <p>This reservation is ready for your final approval. Review the details and click the button below.</p>
                            </div>
                            @endif

                            @if($reservation->priests->isNotEmpty())
                            <div class="mb-4 p-3 bg-gray-50 dark:bg-gray-700 rounded">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Confirmed Priests:</p>
                                <ul class="list-disc list-inside text-sm text-gray-600 dark:text-gray-400">
                                    @foreach($reservation->priests as $priest)
                                        <li>Fr. {{ $priest->first_name }} {{ $priest->last_name }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif

                            <form action="{{ route('admin.reservations.final-approve', $reservation->reservation_id) }}" method="POST"
                                  onsubmit="return confirm('Give final approval to this reservation? This will confirm the reservation and notify all parties.');">
                                @csrf

                                <div class="mb-4">
                                    <label for="final_remarks" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Approval Remarks (optional)
                                    </label>
                                    <textarea name="remarks" id="final_remarks" rows="2"
                                              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-green-500 focus:ring-green-500"
                                              placeholder="Add any remarks (optional)...">{{ old('remarks') }}</textarea>
                                </div>

                                <button type="submit"
                                        class="w-full px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-green-700 transition flex items-center justify-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Give Final Approval
                                </button>
                            </form>
                        </div>
                    </div>
                    @endif

                    <!-- Reject Button -->
                    @if($reservation->status === 'pending_priest_assignment' || $reservation->status === 'adviser_approved')
                    {{-- Only show Reject button if not waiting for final approval --}}
                    @if(!($reservation->priest_confirmation === 'confirmed' || $reservation->allPriestsConfirmed()))
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Reject Reservation</h3>

                            <form action="{{ route('admin.reservations.reject', $reservation->reservation_id) }}" method="POST"
                                  onsubmit="return confirm('Are you sure you want to reject this reservation?');">
                                @csrf

                                <div class="mb-4">
                                    <label for="reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Rejection Reason (required)
                                    </label>
                                    <textarea name="reason" id="reason" rows="3"
                                              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500"
                                              placeholder="Provide reason for rejection...">{{ old('reason') }}</textarea>
                                    @error('reason')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <button type="submit"
                                        class="w-full px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 transition">
                                    Reject Reservation
                                </button>
                            </form>
                        </div>
                    </div>
                    @endif
                    @endif

                    <!-- Cancel Reservation -->
                    @if(!in_array($reservation->status, ['cancelled','rejected']))
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Cancel Reservation</h3>

                            <form action="{{ route('admin.reservations.cancel', $reservation->reservation_id) }}" method="POST"
                                  onsubmit="return confirm('Are you sure you want to cancel this reservation?');">
                                @csrf

                                <div class="mb-4">
                                    <label for="cancel_reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Reason for Cancellation (required)
                                    </label>
                                    <textarea name="reason" id="cancel_reason" rows="3" required
                                              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500"
                                              placeholder="Provide reason for cancellation...">{{ old('reason') }}</textarea>
                                    @error('reason')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <button type="submit"
                                        class="w-full px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 transition">
                                    Cancel Reservation
                                </button>
                            </form>
                        </div>
                    </div>
                    @endif

                </div>
                <!-- End of Right Column -->
            </div>
            <!-- End of Main Content Grid -->
        </div>
    </div>
</x-app-layout>
