<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Notifications') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Action Buttons -->
            <div class="mb-4 flex justify-end gap-3">
                <a href="{{ route('adviser.notifications.archived') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                    </svg>
                    View Archived
                </a>
                <button onclick="clearAllNotifications()" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Clear All
                </button>
                <button onclick="markAllAsRead()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                    Mark All as Read
                </button>
            </div>

            <!-- Notifications List -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-3">
                    @forelse($notifications as $notification)
                        <div class="notification-item rounded-lg p-4 shadow-md transition-all {{ $notification->isUnread() ? 'bg-blue-100 dark:bg-blue-900/40 border-2 border-blue-200 dark:border-blue-700' : 'bg-gray-50 dark:bg-gray-700/30 border border-gray-200 dark:border-gray-600' }}" id="notification-{{ $notification->notification_id }}">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center mb-2">
                                        @if($notification->type === 'Cancellation Request')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                                </svg>
                                                {{ $notification->type }}
                                            </span>
                                        @elseif($notification->type === 'Update')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                </svg>
                                                {{ $notification->type }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-200 text-gray-800 dark:bg-gray-600 dark:text-gray-300">
                                                {{ $notification->type }}
                                            </span>
                                        @endif

                                        @if($notification->isUnread())
                                            <span class="ml-2 w-2 h-2 bg-blue-600 dark:bg-blue-400 rounded-full animate-pulse"></span>
                                        @endif
                                    </div>

                                    <p class="text-sm text-gray-900 dark:text-gray-100 {{ $notification->isUnread() ? 'font-semibold' : 'font-normal' }} mb-1">
                                        {!! $notification->message !!}
                                    </p>

                                    @if($notification->reservation)
                                        <p class="text-xs text-gray-600 dark:text-gray-400 {{ $notification->isUnread() ? 'font-medium' : '' }}">
                                            Reservation #{{ $notification->reservation_id }} -
                                            {{ $notification->reservation->schedule_date->format('M d, Y h:i A') }}
                                        </p>
                                    @endif

                                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                        {{ optional($notification->sent_at)->diffForHumans() ?? optional($notification->created_at)->diffForHumans() ?? '' }}
                                    </p>
                                </div>

                                <div class="ml-4 flex-shrink-0 flex flex-col items-end gap-2">
                                    <button onclick="archiveNotification({{ $notification->notification_id }})" class="flex-shrink-0 text-gray-400 hover:text-red-500 transition-colors p-2" title="Archive this notification">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                    
                                    @if($notification->reservation_id)
                                        <a href="{{ route('adviser.reservations.show', $notification->reservation_id) }}" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none transition">
                                            View
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            <p class="mt-2">No notifications yet</p>
                        </div>
                    @endforelse
                </div>

                @if($notifications->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        {{ $notifications->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('DOMContentLoaded', function() {
            if (window.dispatchEvent) window.dispatchEvent(new Event('notification-update'));
        });

        function markAllAsRead() {
            fetch('{{ route('adviser.notifications.mark-all-read') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (window.dispatchEvent) window.dispatchEvent(new Event('notification-update'));
                    location.reload();
                }
            });
        }

        function clearAllNotifications() {
            if (!confirm('Are you sure you want to archive all notifications?')) return;
            fetch('{{ route('adviser.notifications.clear-all') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (window.dispatchEvent) window.dispatchEvent(new Event('notification-update'));
                    document.querySelectorAll('.notification-item').forEach(item => {
                        item.style.transition = 'opacity 0.3s, transform 0.3s';
                        item.style.opacity = '0';
                        item.style.transform = 'scale(0.95)';
                    });
                    setTimeout(() => location.reload(), 300);
                } else {
                    alert('Failed to clear notifications.');
                }
            }).catch(error => { console.error('Error:', error); alert('An error occurred.'); });
        }

        function archiveNotification(notificationId) {
            const modalHtml = `
                <div id="archiveModal-${notificationId}" style="position: fixed; inset: 0; z-index: 99999; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); animation: fadeIn 0.2s ease;" onclick="if(event.target === this) closeArchiveModal(${notificationId})">
                    <div style="background: white; border-radius: 16px; box-shadow: 0 25px 50px rgba(0,0,0,0.15); max-width: 380px; width: 90%; overflow: hidden; animation: slideUp 0.3s ease;">
                        <div style="background: linear-gradient(135deg, #f3e8ff 0%, #e0e7ff 100%); padding: 24px; text-align: center; border-bottom: 1px solid #e9d5ff;">
                            <div style="width: 56px; height: 56px; background: #fef3c7; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;">
                                <svg width="28" height="28" fill="#d97706" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <h3 style="color: #1f2937; font-size: 18px; font-weight: 700; margin: 0;">Archive Notification?</h3>
                            <p style="color: #6b7280; font-size: 13px; margin: 8px 0 0 0;">This notification will be moved to archived</p>
                        </div>
                        <div style="padding: 20px 24px;">
                            <p style="color: #6b7280; font-size: 14px; margin: 0; line-height: 1.6;">You can restore this notification later from your archived notifications. This action is reversible.</p>
                        </div>
                        <div style="padding: 16px 24px 24px; display: flex; gap: 12px; justify-content: flex-end; border-top: 1px solid #e5e7eb;">
                            <button onclick="closeArchiveModal(${notificationId})" style="background: #f3f4f6; color: #374151; border: none; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer;">Cancel</button>
                            <button onclick="confirmArchive(${notificationId})" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; border: none; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer;">Archive</button>
                        </div>
                    </div>
                </div>
                <style>@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } } @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }</style>
            `;
            document.body.insertAdjacentHTML('beforeend', modalHtml);
        }

        function closeArchiveModal(notificationId) {
            const modal = document.getElementById(`archiveModal-${notificationId}`);
            if (modal) { modal.style.opacity = '0'; setTimeout(() => modal.remove(), 200); }
        }

        function confirmArchive(notificationId) {
            const modal = document.getElementById(`archiveModal-${notificationId}`);
            if (modal) modal.remove();
            fetch(`/adviser/notifications/${notificationId}/archive`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const item = document.getElementById(`notification-${notificationId}`);
                    item.style.transition = 'opacity 0.3s, transform 0.3s';
                    item.style.opacity = '0';
                    item.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        item.remove();
                        if (window.dispatchEvent) window.dispatchEvent(new Event('notification-update'));
                        if (document.querySelectorAll('.notification-item').length === 0) location.reload();
                    }, 300);
                } else { alert('Failed to archive notification'); }
            }).catch(error => { console.error('Error:', error); alert('An error occurred'); });
        }
    </script>
</x-app-layout>
