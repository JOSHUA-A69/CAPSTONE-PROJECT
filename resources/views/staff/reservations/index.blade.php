@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    <h1 class="text-2xl font-semibold mb-4">Manage Reservations</h1>

    @if(session('status'))
        <div class="alert alert-success mb-4">{{ session('status') }}</div>
    @endif

    <!-- Search / Filter -->
    <form method="GET" class="bg-white p-4 shadow rounded mb-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Search</label>
                <input type="text" name="q" value="{{ request('q') }}" class="form-input w-full" placeholder="Requestor, organization...">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Status</label>
                <select name="status" class="form-select w-full">
                    <option value="">All</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="adviser_approved" {{ request('status') === 'adviser_approved' ? 'selected' : '' }}>Adviser Approved</option>
                    <option value="admin_approved" {{ request('status') === 'admin_approved' ? 'selected' : '' }}>Admin Approved</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="btn btn-primary mr-2">Filter</button>
                <a href="{{ route('staff.reservations.index') }}" class="btn">Clear</a>
            </div>
        </div>
    </form>

    <div class="bg-white shadow rounded">
        <div class="overflow-x-auto">
            @if($reservations->isEmpty())
            <div class="p-6 text-center text-gray-600">
                No reservations found.
            </div>
            @else
            <table class="min-w-full divide-y">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">ID</th>
                        <th class="px-4 py-2 text-left">Requestor</th>
                        <th class="px-4 py-2 text-left">Organization</th>
                        <th class="px-4 py-2 text-left">Service</th>
                        <th class="px-4 py-2 text-left">Schedule</th>
                        <th class="px-4 py-2 text-left">Status</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y">
                    @foreach($reservations as $r)
                    <tr>
                        <td class="px-4 py-2">{{ $r->reservation_id }}</td>
                        <td class="px-4 py-2">{{ $r->user->full_name ?? $r->user->email }}</td>
                        <td class="px-4 py-2">{{ $r->organization->org_name ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $r->service->service_name ?? '—' }}</td>
                        <td class="px-4 py-2">{{ optional($r->schedule_date)->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-2">
                            <span class="px-2 py-1 text-xs rounded
                                {{ $r->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $r->status === 'adviser_approved' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $r->status === 'admin_approved' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $r->status === 'approved' ? 'bg-green-200 text-green-900' : '' }}
                                {{ $r->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}
                                {{ $r->status === 'cancelled' ? 'bg-gray-100 text-gray-800' : '' }}
                            ">
                                {{ ucfirst(str_replace('_', ' ', $r->status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-right">
                            <a href="{{ route('staff.reservations.show', $r->reservation_id) }}" class="text-blue-600 hover:underline">View</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>

    <div class="mt-4">
        {{ $reservations->links() }}
    </div>
</div>
@endsection
