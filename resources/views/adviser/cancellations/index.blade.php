<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Cancellation Requests') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Flash Messages -->
            @if(session('success'))
            <div class="mb-6 bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 p-4 rounded-r-lg">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <p class="text-sm font-medium text-green-800 dark:text-green-300">{{ session('success') }}</p>
                </div>
            </div>
            @endif

            <p class="mb-6 text-gray-600 dark:text-gray-400">
                Review and manage cancellation requests from users.
            </p>

            @if($cancellations->isEmpty())
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">No requests found</h3>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">There are no pending cancellation requests at this time.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($cancellations as $cancellation)
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 flex flex-col h-full hover:shadow-md transition-shadow duration-200">
                            <!-- Card Header: User Info -->
                            <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <div class="h-10 w-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-500 dark:text-gray-400 font-bold text-lg">
                                        {{ substr($cancellation->requestor->name, 0, 1) }}
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        {{ $cancellation->requestor->name }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                        {{ $cancellation->requestor->email }}
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Card Body: Details -->
                            <div class="p-5 flex-grow space-y-4">
                                <!-- Grid for Org/Service -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider font-semibold">Organization</p>
                                        <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white truncate" title="{{ $cancellation->reservation->organization->name }}">
                                            {{ $cancellation->reservation->organization->name }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider font-semibold">Service</p>
                                        <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white truncate">
                                            {{ $cancellation->reservation->service->service_name ?? 'N/A' }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Schedule Date -->
                                <div class="pt-2">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider font-semibold">Scheduled Date</p>
                                    <div class="mt-1 flex items-center text-sm font-medium text-gray-900 dark:text-white">
                                        <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        {{ \Carbon\Carbon::parse($cancellation->reservation->schedule_date)->format('M d, Y') }}
                                        @if($cancellation->reservation->schedule_time) 
                                            <span class="ml-2 text-gray-400">|</span> 
                                            <span class="ml-2">{{ \Carbon\Carbon::parse($cancellation->reservation->schedule_time)->format('h:i A') }}</span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Reason -->
                                <div class="pt-2">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider font-semibold">Reason</p>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300 line-clamp-2" title="{{ $cancellation->reason }}">
                                        {{ $cancellation->reason }}
                                    </p>
                                </div>
                            </div>

                            <!-- Card Footer: Status & Action -->
                            <div class="p-5 bg-gray-50 dark:bg-gray-750 border-t border-gray-100 dark:border-gray-700 rounded-b-lg flex items-center justify-between">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $cancellation->status === 'approved' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 
                                    ($cancellation->status === 'rejected' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' : 
                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300') }}">
                                    {{ ucwords(str_replace('_', ' ', $cancellation->status)) }}
                                </span>
                                
                                <a href="{{ route('adviser.cancellations.show', $cancellation->cancellation_id) }}" 
                                   class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                    Review
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="mt-6">
                    {{ $cancellations->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
