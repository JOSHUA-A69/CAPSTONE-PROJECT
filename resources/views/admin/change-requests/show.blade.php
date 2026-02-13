<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
             Review Change Request #{{ $changeRequest->change_id }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-6">
                <a href="{{ route('admin.change-requests.index') }}" class="inline-flex items-center text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Back to Change Requests
                </a>
            </div>

            @if(session('error'))
            <div class="mb-6 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-4 rounded-r-lg">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <p class="text-sm font-medium text-red-800 dark:text-red-300">{{ session('error') }}</p>
                </div>
            </div>
            @endif
            @if(session('status'))
            <div class="mb-6 bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 p-4 rounded-r-lg">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <p class="text-sm font-medium text-green-800 dark:text-green-300">{{ session('status') }}</p>
                </div>
            </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Reservation Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 border-b border-gray-100 dark:border-gray-700 pb-2">Reservation Details</h3>
                            <dl class="space-y-3">
                                <div class="flex justify-between items-start">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">ID</dt>
                                    <dd class="text-sm font-semibold text-gray-900 dark:text-white">#{{ $changeRequest->reservation->reservation_id ?? '—' }}</dd>
                                </div>
                                <div class="flex justify-between items-start">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Service</dt>
                                    <dd class="text-sm text-gray-900 dark:text-white text-right">{{ optional($changeRequest->reservation->service)->service_name ?? '—' }}</dd>
                                </div>
                                <div class="flex justify-between items-start">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Venue</dt>
                                    <dd class="text-sm text-gray-900 dark:text-white text-right">{{ optional($changeRequest->reservation->venue)->name ?? '—' }}</dd>
                                </div>
                                <div class="flex justify-between items-start">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Organization</dt>
                                    <dd class="text-sm text-gray-900 dark:text-white text-right">{{ optional($changeRequest->reservation->organization)->org_name ?? '—' }}</dd>
                                </div>
                                <div class="flex justify-between items-start">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Requested by</dt>
                                    <dd class="text-sm text-gray-900 dark:text-white text-right">{{ $changeRequest->requestor->full_name ?? $changeRequest->requestor->name ?? '—' }}</dd>
                                </div>
                                <div class="flex justify-between items-center">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                                    <dd>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            {{ $changeRequest->status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300' : 
                                            ($changeRequest->status === 'approved' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 
                                            'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300') }}">
                                            {{ ucfirst($changeRequest->status) }}
                                        </span>
                                    </dd>
                                </div>
                                <div class="flex justify-between items-start">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Requested</dt>
                                    <dd class="text-sm text-gray-900 dark:text-white text-right">{{ \Carbon\Carbon::parse($changeRequest->requested_at)->toDayDateTimeString() }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>

                <!-- Changes Diff -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 border-b border-gray-100 dark:border-gray-700 pb-2">Requested Changes</h3>
                            @php
                                $changes = $changeRequest->changes_requested ?? [];
                            @endphp
                            @if(empty($changes))
                                <div class="text-center py-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                    <p class="text-gray-500 dark:text-gray-400">No change details available.</p>
                                </div>
                            @else
                                <div class="space-y-4">
                                    @foreach($changes as $field => $diff)
                                        @php
                                            $oldVal = $diff['old'] ?? '—';
                                            $newVal = $diff['new'] ?? '—';
                                            
                                            // Resolve Users (Officiant, Requestor, Admin actions)
                                            if (in_array($field, ['officiant_id', 'user_id', 'approved_by', 'rejected_by', 'cancelled_by'])) {
                                                if (is_numeric($oldVal) && $oldVal > 0) {
                                                    $u = \App\Models\User::find($oldVal);
                                                    if ($u) {
                                                        $prefix = ($field === 'officiant_id') ? "Fr. " : "";
                                                        $name = $u->full_name ?? ($u->first_name . ' ' . $u->last_name);
                                                        $oldVal = $prefix . $name;
                                                    }
                                                }
                                                if (is_numeric($newVal) && $newVal > 0) {
                                                    $u = \App\Models\User::find($newVal);
                                                    if ($u) {
                                                        $prefix = ($field === 'officiant_id') ? "Fr. " : "";
                                                        $name = $u->full_name ?? ($u->first_name . ' ' . $u->last_name);
                                                        $newVal = $prefix . $name;
                                                    }
                                                }
                                            }
                                            // Resolve Venue
                                            elseif ($field === 'venue_id') {
                                                if (is_numeric($oldVal) && $oldVal > 0) {
                                                    $v = \App\Models\Venue::find($oldVal);
                                                    $oldVal = $v ? $v->name : $oldVal;
                                                }
                                                if (is_numeric($newVal) && $newVal > 0) {
                                                    $v = \App\Models\Venue::find($newVal);
                                                    $newVal = $v ? $v->name : $newVal;
                                                }
                                            }
                                            // Resolve Service
                                            elseif ($field === 'service_id') {
                                                if (is_numeric($oldVal) && $oldVal > 0) {
                                                    $s = \App\Models\Service::find($oldVal);
                                                    $oldVal = $s ? $s->service_name : $oldVal;
                                                }
                                                if (is_numeric($newVal) && $newVal > 0) {
                                                    $s = \App\Models\Service::find($newVal);
                                                    $newVal = $s ? $s->service_name : $newVal;
                                                }
                                            }
                                            // Resolve Organization
                                            elseif ($field === 'org_id') {
                                                if (is_numeric($oldVal) && $oldVal > 0) {
                                                    $o = \App\Models\Organization::find($oldVal);
                                                    $oldVal = $o ? $o->org_name : $oldVal;
                                                }
                                                if (is_numeric($newVal) && $newVal > 0) {
                                                    $o = \App\Models\Organization::find($newVal);
                                                    $newVal = $o ? $o->org_name : $newVal;
                                                }
                                            }
                                        @endphp
                                        
                                        <!-- Mobile-first stacked layout for each diff -->
                                        <div class="bg-gray-50 dark:bg-gray-700/30 rounded-lg p-4 border border-gray-100 dark:border-gray-700">
                                            <div class="mb-3">
                                                <span class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wide">
                                                    {{ ucwords(str_replace('_',' ', $field)) }}
                                                </span>
                                            </div>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Current Value</p>
                                                    <div class="text-sm text-gray-600 dark:text-gray-400 font-mono bg-white dark:bg-gray-800 p-2 rounded border border-gray-200 dark:border-gray-600">
                                                        {{ is_array($oldVal) ? json_encode($oldVal) : $oldVal }}
                                                    </div>
                                                </div>
                                                <div>
                                                    <p class="text-xs text-emerald-600 dark:text-emerald-400 mb-1 font-semibold">New Requested Value</p>
                                                    <div class="text-sm text-gray-900 dark:text-white font-mono font-medium bg-emerald-50 dark:bg-emerald-900/20 p-2 rounded border border-emerald-100 dark:border-emerald-800">
                                                        {{ is_array($newVal) ? json_encode($newVal) : $newVal }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($changeRequest->status === 'pending')
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 border-b border-gray-100 dark:border-gray-700 pb-2">Actions</h3>
                            <div class="flex flex-col md:flex-row gap-4">
                                <form action="{{ route('admin.change-requests.approve', $changeRequest->change_id) }}" method="POST" class="w-full md:w-auto">
                                    @csrf
                                    <button type="submit" class="w-full justify-center inline-flex items-center px-6 py-3 bg-emerald-600 hover:bg-emerald-700 border border-transparent rounded-lg font-semibold text-white uppercase tracking-widest shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        Approve Changes
                                    </button>
                                </form>

                                <form action="{{ route('admin.change-requests.reject', $changeRequest->change_id) }}" method="POST" class="flex-1 w-full relative">
                                    @csrf
                                    <div class="flex flex-col md:flex-row gap-3">
                                        <div class="flex-grow">
                                            <input type="text" name="rejection_reason" placeholder="Reason for rejection (required)" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-red-500 focus:ring-red-500 shadow-sm" required minlength="10" maxlength="1000" />
                                        </div>
                                        <button type="submit" class="w-full md:w-auto inline-flex justify-center items-center px-6 py-3 bg-red-600 hover:bg-red-700 border border-transparent rounded-lg font-semibold text-white uppercase tracking-widest shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            Reject
                                        </button>
                                    </div>
                                    @error('rejection_reason')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </form>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
