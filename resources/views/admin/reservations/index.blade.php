@extends('layouts.app')

@section('title', 'Reservations')

@section('content')
<div class="container mx-auto p-4">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">Reservations</h1>
        @isset($pendingCount)
            <span class="text-sm text-gray-600">Pending admin review: {{ $pendingCount }}</span>
        @endisset
    </div>

    <form method="GET" class="mb-4 flex flex-wrap gap-2">
        <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="Search purpose, details, name..." class="border rounded px-3 py-2 w-64" />
        <select name="status" class="border rounded px-3 py-2">
            <option value="">All statuses</option>
            @foreach(($statuses ?? []) as $s)
                <option value="{{ $s }}" @selected(($status ?? '') === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
            @endforeach
        </select>
        <button class="bg-blue-600 text-white rounded px-4 py-2" type="submit">Filter</button>
    </form>

    @if(($reservations ?? null) && $reservations->count())
        <div class="overflow-x-auto bg-white shadow rounded">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">ID</th>
                        <th class="px-4 py-2 text-left">Purpose</th>
                        <th class="px-4 py-2 text-left">Requestor</th>
                        <th class="px-4 py-2 text-left">Status</th>
                        <th class="px-4 py-2 text-left">Scheduled</th>
                        <th class="px-4 py-2 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reservations as $r)
                        <tr class="border-t">
                            <td class="px-4 py-2">{{ $r->reservation_id }}</td>
                            <td class="px-4 py-2">{{ Str::limit($r->purpose, 60) }}</td>
                            <td class="px-4 py-2">{{ optional($r->user)->full_name ?? '—' }}</td>
                            <td class="px-4 py-2">
                                <span class="inline-block px-2 py-1 rounded bg-gray-100">{{ ucfirst(str_replace('_', ' ', $r->status)) }}</span>
                            </td>
                            <td class="px-4 py-2">{{ optional($r->schedule_date)->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-2">
                                <a href="{{ route('admin.reservations.show', $r->reservation_id) }}" class="text-blue-600 hover:underline">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $reservations->links() }}
        </div>
    @else
        <div class="p-6 text-gray-600 bg-white rounded shadow">No reservations found.</div>
    @endif
</div>
@endsection
