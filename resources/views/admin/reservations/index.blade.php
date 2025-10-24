<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Reservations</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex items-center justify-between flex-wrap gap-4">
                        <div>
                            <h3 class="text-2xl font-bold">All Reservations</h3>
                            @isset($pendingCount)
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Awaiting admin action: <span class="font-semibold">{{ $pendingCount }}</span></p>
                            @endisset
                        </div>
                        <form method="GET" action="{{ route('admin.reservations.index') }}" class="w-full md:w-auto">
                            <div class="flex items-center gap-2">
                                <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="Search purpose, requester..." class="w-full md:w-64 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" />
                                <select name="status" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                    <option value="">All Statuses</option>
                                    @foreach(($statuses ?? []) as $s)
                                        <option value="{{ $s }}" @selected(($status ?? '') === $s)>{{ ucwords(str_replace('_',' ', $s)) }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="inline-flex items-center rounded-md bg-[var(--er-green)] text-white px-4 py-2 font-semibold hover:opacity-95">Filter</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if(($reservations->count() ?? 0) === 0)
                        <div class="text-gray-600 dark:text-gray-400">No reservations found.</div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900/30">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ref</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requestor</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Organization</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Schedule</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-4 py-2"></th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($reservations as $r)
                                        <tr>
                                            <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-200">#{{ $r->reservation_id }}</td>
                                            <td class="px-4 py-2 text-sm">
                                                <div class="text-gray-900 dark:text-gray-100">{{ $r->user->full_name ?? ($r->user->name ?? $r->user->email ?? '—') }}</div>
                                                <div class="text-xs text-gray-500">{{ $r->purpose ? Str::limit($r->purpose, 48) : '—' }}</div>
                                            </td>
                                            <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-200">{{ $r->organization->org_name ?? '—' }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-200">{{ $r->service->service_name ?? $r->service->name ?? '—' }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-200">{{ optional($r->schedule_date)->format('M d, Y g:i A') }}</td>
                                            <td class="px-4 py-2 text-sm">
                                                @php
                                                    $st = $r->status ?? 'unknown';
                                                    $badge = match($st) {
                                                        'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300',
                                                        'adviser_approved' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
                                                        'admin_approved' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300',
                                                        'approved' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
                                                        'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
                                                        'cancelled' => 'bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
                                                        default => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200',
                                                    };
                                                @endphp
                                                <span class="px-2 py-1 rounded-full text-xs {{ $badge }}">{{ ucwords(str_replace('_',' ', $st)) }}</span>
                                            </td>
                                            <td class="px-4 py-2 text-sm text-right">
                                                <a href="{{ route('admin.reservations.show', $r->reservation_id) }}" class="inline-flex items-center rounded-md border border-gray-300 dark:border-gray-700 px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50">View</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">{{ $reservations->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
