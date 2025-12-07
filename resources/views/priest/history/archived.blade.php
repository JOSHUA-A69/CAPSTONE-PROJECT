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
                            <div class="bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-4 shadow-sm" id="archived-history-{{ $h->history_id }}">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="px-2 py-1 bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 text-xs rounded-full font-medium">
                                                {{ $h->reservation->activity_name ?? 'N/A' }}
                                            </span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                Reservation ID: {{ $h->reservation_id }}
                                            </span>
                                        </div>
                                        
                                        <div class="flex">
                                            <div class="flex-shrink-0 w-2 bg-gray-400 rounded-full mr-4"></div>
                                            <div class="flex-1">
                                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                    {{ ucfirst(str_replace('_', ' ', $h->action)) }}
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                    by {{ $h->performedBy?->full_name ?? 'System' }}
                                                    • {{ $h->created_at->format('M d, Y h:i A') }}
                                                </p>
                                                @if($h->remarks)
                                                <p class="text-sm text-gray-700 dark:text-gray-300 mt-2 p-2 bg-white dark:bg-gray-600 rounded">
                                                    "{{ $h->remarks }}"
                                                </p>
                                                @endif
                                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-2 italic">
                                                    Archived {{ $h->archived_at->diffForHumans() }}
                                                    by {{ $h->archivedBy?->full_name ?? 'Unknown' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="ml-4 flex gap-2">
                                        <button onclick="restoreHistory({{ $h->history_id }})" 
                                                class="px-3 py-1.5 bg-green-50 hover:bg-green-100 text-green-600 rounded-md transition-colors text-xs font-medium"
                                                title="Restore this item">
                                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                            </svg>
                                            Restore
                                        </button>
                                        <a href="{{ route('priest.reservations.show', $h->reservation_id) }}" 
                                           class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 rounded-md transition-colors text-xs font-medium"
                                           title="View reservation">
                                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            View
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
