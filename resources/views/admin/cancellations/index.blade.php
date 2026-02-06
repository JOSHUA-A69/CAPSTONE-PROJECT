<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-xl lg:text-2xl text-gray-800 dark:text-gray-200 leading-tight">
                    Cancellation Management
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Review and manage cancellation requests
                </p>
            </div>
            
            <!-- Status Toggle Buttons -->
            <div class="flex bg-gray-100 dark:bg-gray-700 rounded-lg p-1 shadow-sm">
                <a href="{{ route('admin.cancellations.index', ['status' => 'pending']) }}" 
                   class="px-4 py-2 text-sm font-medium rounded-md transition-all duration-200 
                          {{ ($status ?? 'pending') === 'pending' 
                             ? 'bg-emerald-500 text-white shadow-sm' 
                             : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-white dark:hover:bg-gray-600' }}">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Pending</span>
                        @if($pendingCount ?? false)
                        <span class="bg-red-100 text-red-800 text-xs font-bold px-1.5 py-0.5 rounded-full">{{ $pendingCount }}</span>
                        @endif
                    </div>
                </a>
                <a href="{{ route('admin.cancellations.index', ['status' => 'completed']) }}" 
                   class="px-4 py-2 text-sm font-medium rounded-md transition-all duration-200
                          {{ ($status ?? 'pending') === 'completed' 
                             ? 'bg-emerald-500 text-white shadow-sm' 
                             : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-white dark:hover:bg-gray-600' }}">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Completed</span>
                    </div>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 lg:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Main Content Card -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                
                <!-- Card Header -->
                <div class="bg-gradient-to-r from-emerald-50 to-green-50 dark:from-gray-900 dark:to-gray-800 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                    {{ ($status ?? 'pending') === 'completed' ? 'Completed' : 'Pending' }} Requests
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ $cancellations->total() }} total request{{ $cancellations->total() !== 1 ? 's' : '' }}
                                </p>
                            </div>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="flex items-center gap-3">
                            @if(($status ?? 'pending') === 'pending' && $cancellations->count() > 0)
                            <button class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-100 hover:bg-emerald-200 dark:bg-emerald-900/30 dark:hover:bg-emerald-900/50 text-emerald-700 dark:text-emerald-400 rounded-lg text-sm font-medium transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Export Data
                            </button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Desktop Table View -->
                <div class="hidden lg:block">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                                <tr>
                                    <th class="px-4 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Request</th>
                                    <th class="px-4 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Reservation Details</th>
                                    <th class="px-4 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Requestor</th>
                                    <th class="px-4 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                    <th class="px-4 py-4 text-right text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @forelse($cancellations as $c)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                                                <span class="text-red-600 dark:text-red-400 font-bold text-sm">#{{ $c->cancellation_id }}</span>
                                            </div>
                                            <div>
                                                <div class="text-sm text-gray-600 dark:text-gray-400">{{ $c->created_at->diffForHumans() }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-500">{{ $c->created_at->format('M d, Y h:i A') }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="max-w-sm">
                                            <div class="font-semibold text-gray-900 dark:text-white truncate">
                                                {{ $c->reservation->activity_name ?? 'Reservation #'.$c->reservation_id }}
                                            </div>
                                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                                {{ optional($c->reservation->schedule_date)->format('M d, Y h:i A') }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                                                <span class="text-blue-600 dark:text-blue-400 font-bold text-xs">
                                                    {{ strtoupper(substr($c->requestor->name, 0, 1)) }}
                                                </span>
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-900 dark:text-white">{{ $c->requestor->name }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $c->requestor->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="grid grid-cols-2 gap-1">
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium
                                                {{ $c->isStaffConfirmed() ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="{{ $c->isStaffConfirmed() ? 'M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z' : 'M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z' }}" clip-rule="evenodd"/>
                                                </svg>
                                                Staff
                                            </span>
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium
                                                {{ $c->isAdminConfirmed() ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="{{ $c->isAdminConfirmed() ? 'M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z' : 'M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z' }}" clip-rule="evenodd"/>
                                                </svg>
                                                Admin
                                            </span>
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium
                                                {{ $c->isAdviserConfirmed() ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="{{ $c->isAdviserConfirmed() ? 'M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z' : 'M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z' }}" clip-rule="evenodd"/>
                                                </svg>
                                                Adviser
                                            </span>
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium
                                                {{ $c->isPriestConfirmed() ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="{{ $c->isPriestConfirmed() ? 'M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z' : 'M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z' }}" clip-rule="evenodd"/>
                                                </svg>
                                                Priest
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-right">
                                        <a href="{{ route('admin.cancellations.show', $c->cancellation_id) }}" 
                                           class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium transition-colors shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            Review
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mb-4">
                                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                                </svg>
                                            </div>
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">No cancellation requests</h3>
                                            <p class="text-gray-500 dark:text-gray-400">There are no {{ ($status ?? 'pending') === 'completed' ? 'completed' : 'pending' }} requests to display.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Mobile Card View -->
                <div class="lg:hidden">
                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($cancellations as $c)
                        <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <!-- Header -->
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                                        <span class="text-red-600 dark:text-red-400 font-bold text-sm">#{{ $c->cancellation_id }}</span>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-900 dark:text-white">Request {{ $c->cancellation_id }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $c->created_at->diffForHumans() }}</div>
                                    </div>
                                </div>
                                <span class="px-3 py-1 bg-{{ ($status ?? 'pending') === 'completed' ? 'green' : 'yellow' }}-100 text-{{ ($status ?? 'pending') === 'completed' ? 'green' : 'yellow' }}-800 dark:bg-{{ ($status ?? 'pending') === 'completed' ? 'green' : 'yellow' }}-900/30 dark:text-{{ ($status ?? 'pending') === 'completed' ? 'green' : 'yellow' }}-300 text-xs font-medium rounded-full">
                                    {{ ($status ?? 'pending') === 'completed' ? 'Completed' : 'Pending' }}
                                </span>
                            </div>
                            
                            <!-- Reservation Info -->
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 mb-3">
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Reservation Details</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400 font-medium">{{ $c->reservation->activity_name ?? 'Reservation #'.$c->reservation_id }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-500">{{ optional($c->reservation->schedule_date)->format('M d, Y h:i A') }}</p>
                            </div>
                            
                            <!-- Requestor -->
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                                    <span class="text-blue-600 dark:text-blue-400 font-bold text-xs">
                                        {{ strtoupper(substr($c->requestor->name, 0, 1)) }}
                                    </span>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $c->requestor->name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $c->requestor->email }}</div>
                                </div>
                            </div>
                            
                            <!-- Confirmations -->
                            <div class="mb-4">
                                <h5 class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Confirmations</h5>
                                <div class="grid grid-cols-2 gap-2">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium
                                        {{ $c->isStaffConfirmed() ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                                        <svg class="w-3 h-3 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="{{ $c->isStaffConfirmed() ? 'M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z' : 'M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z' }}" clip-rule="evenodd"/>
                                        </svg>
                                        Staff
                                    </span>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium
                                        {{ $c->isAdminConfirmed() ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                                        <svg class="w-3 h-3 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="{{ $c->isAdminConfirmed() ? 'M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z' : 'M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z' }}" clip-rule="evenodd"/>
                                        </svg>
                                        Admin
                                    </span>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium
                                        {{ $c->isAdviserConfirmed() ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                                        <svg class="w-3 h-3 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="{{ $c->isAdviserConfirmed() ? 'M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z' : 'M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z' }}" clip-rule="evenodd"/>
                                        </svg>
                                        Adviser
                                    </span>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium
                                        {{ $c->isPriestConfirmed() ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                                        <svg class="w-3 h-3 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="{{ $c->isPriestConfirmed() ? 'M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z' : 'M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z' }}" clip-rule="evenodd"/>
                                        </svg>
                                        Priest
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Action Button -->
                            <a href="{{ route('admin.cancellations.show', $c->cancellation_id) }}" 
                               class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium transition-colors shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Review Request
                            </a>
                        </div>
                        @empty
                        <div class="p-8 text-center">
                            <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">No cancellation requests</h3>
                            <p class="text-gray-500 dark:text-gray-400">There are no {{ ($status ?? 'pending') === 'completed' ? 'completed' : 'pending' }} requests to display.</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Pagination -->
                @if($cancellations->hasPages())
                <div class="px-4 sm:px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700">
                    {{ $cancellations->withQueryString()->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
