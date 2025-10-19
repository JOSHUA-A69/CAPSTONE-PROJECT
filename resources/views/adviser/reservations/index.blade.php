@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-6">
    <h1 class="text-2xl font-semibold mb-4">Organization Reservations</h1>

    @if(session('status'))
        <div class="alert alert-success mb-4">{{ session('status') }}</div>
    @endif

    <div class="bg-white shadow rounded">
        <div class="overflow-x-auto">
            @if($reservations->isEmpty())
            <div class="p-6 text-center text-gray-600">
                No pending reservations for your organizations.
            </div>
            @else
            <table class="min-w-full divide-y">
                <thead class="bg-gray-50">
                    <tr>
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
                        <td class="px-4 py-2">{{ $r->user->full_name ?? $r->user->email }}</td>
                        <td class="px-4 py-2">{{ $r->organization->org_name ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $r->service->service_name ?? '—' }}</td>
                        <td class="px-4 py-2">{{ optional($r->schedule_date)->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-2">
                            <span class="px-2 py-1 text-xs rounded {{ $r->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : ($r->status === 'adviser_approved' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800') }}">
                                {{ ucfirst(str_replace('_', ' ', $r->status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-right space-x-3">
                            @if($r->status === 'pending')
                                <form method="POST" action="{{ route('adviser.reservations.approve', $r->reservation_id) }}" class="inline">
                                    @csrf
                                    <button class="text-green-600 hover:underline">Approve</button>
                                </form>
                                <a href="{{ route('adviser.reservations.show', $r->reservation_id) }}#reject" class="text-red-600 hover:underline">Reject</a>
                            @endif
                            <a href="{{ route('adviser.reservations.show', $r->reservation_id) }}" class="text-blue-600 hover:underline">
                                View Details
                            </a>
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
