@php
/**
 * @var \Illuminate\Support\Collection<int, \App\Models\Notification> $notifications
 * @var string $role One of: admin, staff, adviser, priest, requestor
 */
@endphp

@if($notifications->isEmpty())
    <div class="px-6 py-12 text-center bg-white dark:bg-gray-800">
        <svg class="mx-auto h-14 w-14 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">No notifications yet</p>
    </div>
@else
    <ul role="list" class="divide-y divide-gray-200 dark:divide-gray-700">
        @foreach($notifications as $n)
            @php
                $isUnread = method_exists($n, 'isUnread') ? $n->isUnread() : is_null($n->read_at);
                $timeAgo = optional($n->sent_at)->diffForHumans() ?? optional($n->created_at)->diffForHumans() ?? '';
                $type = (string)($n->type ?? 'Notice');
                $label = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $type) ?: 'NT', 0, 2));
                $bgTint = $isUnread ? 'bg-blue-50 dark:bg-blue-900/20' : 'bg-white dark:bg-gray-800';
                $iconBg = match(true) {
                    str_contains(strtolower($type), 'urgent') => 'bg-red-100 text-red-600 dark:bg-red-900 dark:text-red-300',
                    str_contains(strtolower($type), 'approve') || str_contains(strtolower($type), 'confirm') => 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
                    str_contains(strtolower($type), 'cancel') || str_contains(strtolower($type), 'declin') => 'bg-orange-100 text-orange-700 dark:bg-orange-900 dark:text-orange-300',
                    default => 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300',
                };
                $safeMessage = strip_tags((string) $n->message, '<strong><b><em><i><br>');
                // Default link: role notifications index
                $indexRoute = match($role) {
                    'admin' => route('admin.notifications.index'),
                    'staff' => route('staff.notifications.index'),
                    'adviser' => route('adviser.notifications.index'),
                    'priest' => route('priest.notifications.index'),
                    default => route('requestor.notifications.index'),
                };
            @endphp
            <li class="group {{ $bgTint }}">
                <a href="{{ $indexRoute }}" class="flex items-start gap-3 px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors">
                    <div class="flex-shrink-0 mt-0.5">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center text-[11px] font-bold {{ $iconBg }}">
                            {{ $label }}
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-900 dark:text-gray-100 leading-snug line-clamp-2 break-words overflow-hidden">
                            {!! $safeMessage !!}
                        </p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 truncate">
                            {{ $timeAgo }}
                        </p>
                    </div>
                    @if($isUnread)
                        <span class="ml-2 mt-1 inline-flex h-2.5 w-2.5 rounded-full bg-blue-500 ring-2 ring-white dark:ring-gray-800"></span>
                    @endif
                </a>
            </li>
        @endforeach
    </ul>
@endif
