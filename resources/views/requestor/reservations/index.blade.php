@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold">My Reservations</h1>
        <a href="{{ route('requestor.reservations.create') }}" class="btn btn-primary">New Reservation</a>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="bg-white shadow rounded">
        <div class="overflow-x-auto">
            @if($reservations->isEmpty())
            <div class="p-6 text-center text-gray-600">
                No reservations yet.
            </div>
            @else
            <table class="min-w-full divide-y">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">Service</th>
                        <th class="px-4 py-2 text-left">Venue</th>
                        <th class="px-4 py-2 text-left">Schedule</th>
                        <th class="px-4 py-2 text-left">Status</th>
                        <th class="px-4 py-2 text-left">Purpose</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y">
                    @foreach($reservations as $r)
                    <tr>
                        <td class="px-4 py-2">{{ $r->service->service_name ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $r->venue->name ?? '—' }}</td>
                        <td class="px-4 py-2">{{ optional($r->schedule_date)->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-2">{{ ucfirst($r->status) }}</td>
                        <td class="px-4 py-2">{{ \Illuminate\Support\Str::limit($r->purpose ?? '—', 80) }}</td>
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
