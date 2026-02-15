<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Archived History') }}
            </h2>
            <a href="{{ url()->previous() }}" class="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
                ← Back
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if($archivedHistory->isEmpty())
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No archived history</h3>
                            <p class="mt-1 text-sm text-gray-500">You haven't archived any activity history items yet.</p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($archivedHistory as $h)
                            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 sm:p-5 shadow-sm hover:shadow-md transition-shadow" id="archived-history-{{ $h->history_id }}">
                                <div class="space-y-3">
                                    <!-- Header -->
                                    <div class="flex flex-col space-y-2 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="px-3 py-1 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 text-xs rounded-full font-medium">
                                                {{ $h->reservation->activity_name ?? 'N/A' }}
                                            </span>
                                            <span class="text-xs text-gray-400 dark:text-gray-500">
                                                ID: #{{ $h->reservation_id }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 italic">
                                            Archived {{ $h->archived_at->diffForHumans() }}
                                        </p>
                                    </div>

                                    <!-- Content -->
                                    <div class="flex gap-4">
                                        <div class="flex-shrink-0 mt-1">
                                            <div class="w-2 h-2 rounded-full bg-gray-300 dark:bg-gray-600"></div>
                                        </div>
                                        <div class="flex-1 space-y-2">
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                    {{ ucfirst(str_replace('_', ' ', $h->action)) }}
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    by <span class="font-medium text-gray-700 dark:text-gray-300">{{ $h->performedBy?->full_name ?? 'System' }}</span>
                                                    • {{ $h->created_at->format('M d, Y h:i A') }}
                                                </p>
                                            </div>

                                            @if($h->remarks)
                                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 text-sm text-gray-600 dark:text-gray-300 italic border border-gray-100 dark:border-gray-700">
                                                "{{ $h->remarks }}"
                                            </div>
                                            @endif
                                            
                                            <p class="text-xs text-gray-400 dark:text-gray-500">
                                                Archived by {{ $h->archivedBy?->full_name ?? 'Unknown' }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex flex-col space-y-2 sm:flex-row sm:space-y-0 sm:space-x-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                                        <button onclick="restoreHistory({{ $h->history_id }})" 
                                                class="flex items-center justify-center gap-2 px-4 py-2 bg-green-50 hover:bg-green-100 text-green-700 dark:bg-green-900/20 dark:hover:bg-green-900/30 dark:text-green-300 rounded-lg transition-colors text-sm font-medium w-full sm:w-auto focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                                                title="Restore this item">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                            </svg>
                                            <span>Restore</span>
                                        </button>
                                        <a href="{{ route('priest.reservations.show', $h->reservation_id) }}" 
                                           class="flex items-center justify-center gap-2 px-4 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 dark:bg-indigo-900/20 dark:hover:bg-indigo-900/30 dark:text-indigo-300 rounded-lg transition-colors text-sm font-medium w-full sm:w-auto focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                           title="View reservation">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            <span>View Reservation</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            {{ $archivedHistory->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        function restoreHistory(historyId) {
            if (!confirm('Restore this history item?')) return;

            fetch(`/priest/history/${historyId}/restore`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const historyItem = document.getElementById(`archived-history-${historyId}`);
                    historyItem.style.transition = 'opacity 0.3s, transform 0.3s';
                    historyItem.style.opacity = '0';
                    historyItem.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        historyItem.remove();
                        // Check if no more items
                        const remainingItems = document.querySelectorAll('[id^="archived-history-"]');
                        if (remainingItems.length === 0) {
                            location.reload();
                        }
                    }, 300);
                } else {
                    alert('Failed to restore history item');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        }
    </script>
</x-app-layout>
