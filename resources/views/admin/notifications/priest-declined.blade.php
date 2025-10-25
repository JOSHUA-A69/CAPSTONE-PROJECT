<x-app-layout>
    @php
        // Compute dynamic header title using multiple fallbacks
        $__data = $notification->data ?? null;
        if (is_string($__data)) {
            $__data = json_decode($__data, true);
        } elseif (is_object($__data)) {
            $__data = (array) $__data;
        }

        $__name = null;

        // 1) From notification data JSON (ignore placeholders like 'Unknown' or 'A priest')
        if (is_array($__data) && !empty(trim($__data['priest_name'] ?? ''))) {
            $___candidate = trim($__data['priest_name']);
            $___lc = strtolower($___candidate);
            if ($___lc !== 'unknown' && $___lc !== 'fr. unknown' && $___lc !== 'a priest') {
                $__name = $___candidate;
            }
        }

        // 2) Parse from message: <strong>Fr. Name</strong>
        if (!$__name && !empty($notification->message)) {
            if (preg_match('/<strong>([^<]+)<\\/strong>/', $notification->message, $m)) {
                $__name = trim($m[1]);
            }
        }

        // 3) Fallback to reservation history performer
        if (!$__name && $notification->reservation) {
            $___hist = $notification->reservation->history()
                ->where('action', 'priest_declined')
                ->with('performer')
                ->latest()
                ->first();
            if ($___hist && $___hist->performer) {
                $__name = trim($___hist->performer->first_name . ' ' . $___hist->performer->last_name);
            }
        }

        // Normalize with Fr. prefix only if missing
        if ($__name) {
            $__displayName = stripos($__name, 'fr.') === 0 ? $__name : ('Fr. ' . $__name);
            $__headerTitle = $__displayName . ' declined the reservation';
        } else {
            $__headerTitle = 'Priest Declined Notification';
        }
    @endphp
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $__headerTitle }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Alert Header -->
            <div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-600 p-6 mb-6 rounded-lg">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-bold text-red-800 dark:text-red-300">
                            ⚠️ Priest Declined Assignment
                        </h3>
                        <p class="text-sm text-red-700 dark:text-red-400 mt-1">
                            Action Required: Reassignment Needed
                        </p>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <!-- Introduction -->
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <p class="text-gray-700 dark:text-gray-300">
                        Dear CREaM Administrator,
                    </p>
                    <div class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 rounded-md border border-red-200 dark:border-red-800">
                        <p class="text-sm text-red-800 dark:text-red-300 font-medium">
                            🚨 A priest has declined their assignment for a reservation.<br>
                            You need to assign another presider for this service.
                        </p>
                    </div>
                </div>

                <!-- Reservation Details -->
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Reservation Details</h3>
                    <dl class="space-y-3">
                        <div class="flex">
                            <dt class="font-medium text-gray-500 dark:text-gray-400 w-40">Reservation ID:</dt>
                            <dd class="text-gray-900 dark:text-gray-100 font-mono">#{{ $notification->reservation->reservation_id }}</dd>
                        </div>
                        <div class="flex">
                            <dt class="font-medium text-gray-500 dark:text-gray-400 w-40">Service Type:</dt>
                            <dd class="text-gray-900 dark:text-gray-100">{{ $notification->reservation->service->service_name }}</dd>
                        </div>
                        <div class="flex">
                            <dt class="font-medium text-gray-500 dark:text-gray-400 w-40">Requestor:</dt>
                            <dd class="text-gray-900 dark:text-gray-100">
                                {{ $notification->reservation->user->first_name }} {{ $notification->reservation->user->last_name }}
                            </dd>
                        </div>
                        <div class="flex">
                            <dt class="font-medium text-gray-500 dark:text-gray-400 w-40">Date & Time:</dt>
                            <dd class="text-gray-900 dark:text-gray-100">
                                {{ $notification->reservation->schedule_date->format('l, F d, Y - h:i A') }}
                            </dd>
                        </div>
                        <div class="flex">
                            <dt class="font-medium text-gray-500 dark:text-gray-400 w-40">Venue:</dt>
                            <dd class="text-gray-900 dark:text-gray-100">
                                @if($notification->reservation->custom_venue_name)
                                    📍 {{ $notification->reservation->custom_venue_name }} <em class="text-sm text-gray-500">(Custom Location)</em>
                                @else
                                    📍 {{ $notification->reservation->venue->name ?? 'N/A' }}
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- Declined By -->
                @php
                    $data = null;
                    if ($notification->data) {
                        $data = is_string($notification->data) ? json_decode($notification->data, true) : $notification->data;
                    }

                    // Fallback: Get priest info from reservation history if data not available
                    if (!$data && $notification->reservation) {
                        $priestDeclineHistory = $notification->reservation->history()
                            ->where('action', 'priest_declined')
                            ->with('performer')
                            ->latest()
                            ->first();

                        if ($priestDeclineHistory && $priestDeclineHistory->performer) {
                            $data = [
                                'priest_name' => $priestDeclineHistory->performer->first_name . ' ' . $priestDeclineHistory->performer->last_name,
                                'reason' => $priestDeclineHistory->remarks ? str_replace('Priest declined availability. Reason: ', '', $priestDeclineHistory->remarks) : 'No reason provided',
                            ];
                        }
                    }
                @endphp

                @if($data && isset($data['priest_name']))
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Declined By</h3>
                    <dl class="space-y-3">
                        <div class="flex">
                            <dt class="font-medium text-gray-500 dark:text-gray-400 w-40">Priest:</dt>
                            <dd class="text-gray-900 dark:text-gray-100">{{ $data['priest_name'] }}</dd>
                        </div>
                    </dl>
                </div>
                @endif

                <!-- Reason for Decline -->
                @if($data && isset($data['reason']))
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Reason for Decline:</h3>
                    <div class="p-4 bg-red-50 dark:bg-red-900/20 rounded-md border border-red-200 dark:border-red-800">
                        <p class="text-gray-800 dark:text-gray-200">{{ $data['reason'] }}</p>
                    </div>
                </div>
                @endif

                <!-- Action Button -->
                <div class="p-6 bg-gray-50 dark:bg-gray-900/50">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 text-center">Assign New Priest</h3>

                    <form method="POST" action="{{ route('admin.reservations.assign-priest', $notification->reservation_id) }}" class="max-w-2xl mx-auto">
                        @csrf

                        <!-- Priest Dropdown -->
                        <div class="mb-4">
                            <label for="officiant_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Select Priest <span class="text-red-500">*</span>
                            </label>
                            <select id="officiant_id" name="officiant_id" required
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                <option value="">-- Select a Priest --</option>
                                @foreach($availablePriests as $priest)
                                    <option value="{{ $priest->id }}"
                                            @if(old('officiant_id', $notification->reservation->preferred_officiant_id ?? null) == $priest->id) selected @endif
                                            @if(!$priest->is_available) disabled @endif
                                            class="{{ $priest->is_available ? 'text-gray-900' : 'text-gray-400' }}">
                                        {{ $priest->first_name }} {{ $priest->last_name }}
                                        @if(!$priest->is_available)
                                            (Unavailable - Already Assigned)
                                        @else
                                            ✓ Available
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('officiant_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Date & Time: {{ $notification->reservation->schedule_date->format('l, F d, Y - h:i A') }}
                            </p>
                        </div>

                        <!-- Remarks -->
                        <div class="mb-4">
                            <label for="remarks" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Remarks (Optional)
                            </label>
                            <textarea id="remarks" name="remarks" rows="3"
                                      placeholder="Add any additional notes about this assignment..."
                                      class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-purple-500 focus:ring-purple-500"></textarea>
                            @error('remarks')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <!-- Actions: Primary + Secondary (Consistent styling) -->
                        <div class="flex flex-col sm:flex-row gap-3">
                            <!-- Primary: Send Assignment -->
                            <button type="submit" aria-label="Send Assignment"
                                    class="w-full sm:flex-1 inline-flex items-center justify-center px-6 py-3 rounded-lg text-sm font-semibold
                                      bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border-2 border-gray-300 dark:border-gray-600
                                      hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-400
                                      focus:outline-none focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-700 transition-colors duration-150">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                Send Assignment
                            </button>

                            <!-- Secondary: View Details -->
                            <a href="{{ route('admin.reservations.show', $notification->reservation->id ?? $notification->reservation_id) }}"
                               class="w-full sm:flex-1 inline-flex items-center justify-center px-6 py-3 rounded-lg text-sm font-semibold
                                      bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border-2 border-gray-300 dark:border-gray-600
                                      hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-400
                                      focus:outline-none focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-700 transition-colors duration-150">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14m-6 0l-4.553 2.276A1 1 0 013 15.382V8.618a1 1 0 011.447-.894L9 10m0 0l6 4m-6-4l6-4"/>
                                </svg>
                                View Details
                            </a>
                        </div>
                    </form>

                    @if(session('status') === 'priest-assigned')
                        <div class="mt-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-md">
                            <p class="text-sm text-green-800 dark:text-green-300 font-medium text-center">
                                ✅ {{ session('message', 'Priest assigned successfully!') }}
                            </p>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md">
                            <p class="text-sm text-red-800 dark:text-red-300 font-medium text-center">
                                ❌ {{ session('error') }}
                            </p>
                        </div>
                    @endif
                </div>

                <!-- Next Steps -->
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Next Steps:</h3>
                    <ol class="list-decimal list-inside space-y-2 text-gray-700 dark:text-gray-300">
                        <li>Review the reservation details</li>
                        <li>Identify an available priest for this date and time</li>
                        <li>Assign the new presider through the CREaM system</li>
                        <li>The new priest will be notified automatically</li>
                    </ol>
                </div>

                <!-- Footer -->
                <div class="p-6 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700 text-center text-sm text-gray-600 dark:text-gray-400">
                    <p class="font-semibold">CREaM - eReligiousServices Management System</p>
                    <p>Holy Name University</p>
                    <p class="text-xs mt-2 text-gray-500 dark:text-gray-500">
                        This is an automated notification. Please do not reply to this email.
                    </p>
                </div>
            </div>

            <!-- Back Button -->
            <div class="mt-6">
                <a href="{{ route('admin.notifications.index') }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                    ← Back to All Notifications
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
