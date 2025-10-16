@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold">Reservation #{{ $reservation->reservation_id }}</h1>
        <a href="{{ route('staff.reservations.index') }}" class="text-blue-600 hover:underline">← Back to List</a>
    </div>

    @if(session('status'))
        <div class="alert alert-success mb-4">{{ session('status') }}</div>
    @endif

    <!-- Reservation Details -->
    <div class="bg-white shadow rounded p-6 mb-6">
        <h2 class="text-lg font-semibold mb-3">Details</h2>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <span class="text-sm text-gray-600">Requestor:</span>
                <p class="font-medium">{{ $reservation->user->full_name ?? $reservation->user->email }}</p>
            </div>
            <div>
                <span class="text-sm text-gray-600">Organization:</span>
                <p class="font-medium">{{ $reservation->organization->org_name ?? '—' }}</p>
            </div>
            <div>
                <span class="text-sm text-gray-600">Service:</span>
                <p class="font-medium">{{ $reservation->service->service_name ?? '—' }}</p>
            </div>
            <div>
                <span class="text-sm text-gray-600">Venue:</span>
                <p class="font-medium">{{ $reservation->venue->venue_name ?? '—' }}</p>
            </div>
            <div>
                <span class="text-sm text-gray-600">Schedule:</span>
                <p class="font-medium">{{ optional($reservation->schedule_date)->format('Y-m-d H:i') }}</p>
            </div>
            <div>
                <span class="text-sm text-gray-600">Participants:</span>
                <p class="font-medium">{{ $reservation->participants_count ?? '—' }}</p>
            </div>
            <div class="col-span-2">
                <span class="text-sm text-gray-600">Purpose:</span>
                <p class="font-medium">{{ $reservation->purpose ?? '—' }}</p>
            </div>
            <div class="col-span-2">
                <span class="text-sm text-gray-600">Details:</span>
                <p class="font-medium">{{ $reservation->details ?? '—' }}</p>
            </div>
            <div>
                <span class="text-sm text-gray-600">Status:</span>
                <p>
                    <span class="px-2 py-1 text-xs rounded
                        {{ $reservation->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                        {{ $reservation->status === 'adviser_approved' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $reservation->status === 'admin_approved' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $reservation->status === 'approved' ? 'bg-green-200 text-green-900' : '' }}
                        {{ $reservation->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}
                        {{ $reservation->status === 'cancelled' ? 'bg-gray-100 text-gray-800' : '' }}
                    ">
                        {{ ucfirst(str_replace('_', ' ', $reservation->status)) }}
                    </span>
                </p>
            </div>
        </div>
    </div>

    <!-- History Timeline -->
    <div class="bg-white shadow rounded p-6 mb-6">
        <h2 class="text-lg font-semibold mb-3">History</h2>
        @if($reservation->history->isEmpty())
            <p class="text-gray-600 text-sm">No history recorded.</p>
        @else
            <div class="space-y-3">
                @foreach($reservation->history->sortByDesc('created_at') as $h)
                <div class="border-l-2 border-gray-300 pl-4">
                    <p class="text-sm font-medium">{{ ucfirst(str_replace('_', ' ', $h->action)) }}</p>
                    <p class="text-xs text-gray-600">by {{ $h->user->full_name ?? $h->user->email }} on {{ $h->created_at->format('Y-m-d H:i') }}</p>
                    @if($h->remarks)
                    <p class="text-sm text-gray-700 mt-1">{{ $h->remarks }}</p>
                    @endif
                </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Actions -->
    @if(in_array($reservation->status, ['adviser_approved', 'admin_approved']))
    <div class="bg-white shadow rounded p-6">
        <h2 class="text-lg font-semibold mb-3">Actions</h2>
        <div class="flex gap-2">
            @if($reservation->status === 'adviser_approved')
            <form method="POST" action="{{ route('staff.reservations.approve', $reservation->reservation_id) }}" class="inline">
                @csrf
                <button class="btn btn-primary">Approve for Final Processing</button>
            </form>
            @endif

            <button onclick="showCancelModal()" class="btn btn-danger">Cancel Reservation</button>
        </div>
    </div>
    @endif
</div>

<!-- Cancel Modal -->
<div id="cancelModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center h-full">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold mb-4">Cancel Reservation</h3>
            <form method="POST" action="{{ route('staff.reservations.cancel', $reservation->reservation_id) }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Reason for cancellation</label>
                    <textarea name="reason" class="form-textarea w-full" rows="3" required></textarea>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary">Submit</button>
                    <button type="button" onclick="hideCancelModal()" class="btn">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showCancelModal() {
    document.getElementById('cancelModal').classList.remove('hidden');
}
function hideCancelModal() {
    document.getElementById('cancelModal').classList.add('hidden');
}
</script>
@endsection
