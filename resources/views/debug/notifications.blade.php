<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Notification System Debug') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(Auth::user()?->role !== 'admin' && Auth::user()?->role !== 'staff')
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <p>This page is only accessible to staff and admin users.</p>
                </div>
            @else
                <!-- System Status -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">System Status</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-gray-600 dark:text-gray-400">Current DateTime:</p>
                                <p class="font-mono text-lg">{{ now() }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600 dark:text-gray-400">Current User:</p>
                                <p class="font-mono text-lg">{{ Auth::user()->full_name ?? Auth::user()->name }} ({{ Auth::user()->role }})</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Manual Trigger -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Manual Trigger</h3>
                        <form action="{{ route('dev.test-unnoticed') }}" method="GET">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                Run Unnoticed Reservation Check Now
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Unnoticed Reservations -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Unnoticed Reservations</h3>
                        @php
                            $unnoticed = \App\Models\Reservation::unnoticedByAdviser()->get();
                        @endphp
                        
                        @if($unnoticed->isEmpty())
                            <p class="text-gray-600 dark:text-gray-400">No unnoticed reservations found.</p>
                            <p class="text-sm text-gray-500 dark:text-gray-500 mt-2">
                                (Reservations must be pending, >24 hours old, and not yet responded to by adviser)
                            </p>
                        @else
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b">
                                        <th class="text-left py-2">ID</th>
                                        <th class="text-left py-2">Requestor</th>
                                        <th class="text-left py-2">Service</th>
                                        <th class="text-left py-2">Created</th>
                                        <th class="text-left py-2">Hours Old</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($unnoticed as $res)
                                        <tr class="border-b">
                                            <td class="py-2">#{{ $res->reservation_id }}</td>
                                            <td class="py-2">{{ $res->user->first_name }} {{ $res->user->last_name }}</td>
                                            <td class="py-2">{{ $res->service->service_name }}</td>
                                            <td class="py-2">{{ $res->created_at->format('Y-m-d H:i') }}</td>
                                            <td class="py-2">{{ $res->created_at->diffInHours(now()) }}h</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>

                <!-- Staff Notifications -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Your Notifications (Staff)</h3>
                        @php
                            $myNotifs = \App\Models\Notification::where('user_id', Auth::id())
                                ->orderByRaw('COALESCE(sent_at, created_at) DESC')
                                ->limit(10)
                                ->get();
                        @endphp
                        
                        @if($myNotifs->isEmpty())
                            <p class="text-gray-600 dark:text-gray-400">No notifications.</p>
                        @else
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b">
                                        <th class="text-left py-2">ID</th>
                                        <th class="text-left py-2">Message</th>
                                        <th class="text-left py-2">Type</th>
                                        <th class="text-left py-2">Sent At</th>
                                        <th class="text-left py-2">Status</th>
                                        <th class="text-left py-2">Data</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($myNotifs as $notif)
                                        <tr class="border-b">
                                            <td class="py-2">#{{ $notif->notification_id }}</td>
                                            <td class="py-2">{{ Str::limit(strip_tags($notif->message), 50) }}</td>
                                            <td class="py-2">{{ $notif->type }}</td>
                                            <td class="py-2 text-xs">{{ $notif->sent_at?->format('H:i:s') }}</td>
                                            <td class="py-2">
                                                <span class="px-2 py-1 rounded text-xs {{ $notif->isUnread() ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                                    {{ $notif->isUnread() ? 'Unread' : 'Read' }}
                                                </span>
                                            </td>
                                            <td class="py-2 text-xs font-mono">
                                                @if($notif->data)
                                                    <details>
                                                        <summary>Show</summary>
                                                        <pre class="text-xs mt-2 p-2 bg-gray-100 dark:bg-gray-700">{{ json_encode($notif->data, JSON_PRETTY_PRINT) }}</pre>
                                                    </details>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>

                <!-- All Unnoticed Notifications -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">All Unnoticed Notifications (Last 20)</h3>
                        @php
                            $allUnnoticed = \App\Models\Notification::where('type', 'Urgent')
                                ->orderByRaw('COALESCE(sent_at, created_at) DESC')
                                ->limit(20)
                                ->get();
                        @endphp
                        
                        @if($allUnnoticed->isEmpty())
                            <p class="text-gray-600 dark:text-gray-400">No urgent (unnoticed) notifications found in system.</p>
                        @else
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b">
                                        <th class="text-left py-2">ID</th>
                                        <th class="text-left py-2">User</th>
                                        <th class="text-left py-2">Message</th>
                                        <th class="text-left py-2">Reservation</th>
                                        <th class="text-left py-2">Sent At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($allUnnoticed as $notif)
                                        <tr class="border-b">
                                            <td class="py-2">#{{ $notif->notification_id }}</td>
                                            <td class="py-2">{{ $notif->user->first_name }} {{ $notif->user->last_name }} ({{ $notif->user->role }})</td>
                                            <td class="py-2">{{ Str::limit(strip_tags($notif->message), 40) }}</td>
                                            <td class="py-2">{{ $notif->reservation_id ? '#' . $notif->reservation_id : 'N/A' }}</td>
                                            <td class="py-2 text-xs">{{ $notif->sent_at?->format('M d, H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
