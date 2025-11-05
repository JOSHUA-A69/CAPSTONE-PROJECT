@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-12">
    <div class="mb-6 flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-heading">Confirm Reservation</h1>
            <p class="text-sm text-muted mt-1">Please review and confirm your availability</p>
        </div>
        <a href="{{ route('requestor.reservations.show', $reservation->reservation_id) }}" class="btn-ghost">Back to Details</a>
    </div>

    <div class="card mb-6">
        <div class="card-header">
            <h3 class="text-lg font-semibold">Reservation Summary</h3>
        </div>
        <div class="card-body">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <dt class="form-label">Service</dt>
                    <dd class="mt-1 text-body">{{ $reservation->service->service_name }}</dd>
                </div>
                <div>
                    <dt class="form-label">Schedule</dt>
                    <dd class="mt-1 text-body">{{ optional($reservation->schedule_date)->format('F d, Y g:i A') }}</dd>
                </div>
                <div>
                    <dt class="form-label">Venue</dt>
                    <dd class="mt-1 text-body">
                        @if($reservation->custom_venue_name)
                            {{ $reservation->custom_venue_name }}
                        @elseif($reservation->venue)
                            {{ $reservation->venue->name }}
                        @else
                            —
                        @endif
                    </dd>
                </div>
                @if($reservation->organization)
                <div>
                    <dt class="form-label">Organization</dt>
                    <dd class="mt-1 text-body">{{ $reservation->organization->org_name }}</dd>
                </div>
                @endif
                @if($reservation->purpose)
                <div class="sm:col-span-2">
                    <dt class="form-label">Purpose</dt>
                    <dd class="mt-1 text-body">{{ $reservation->purpose }}</dd>
                </div>
                @endif
            </dl>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="text-lg font-semibold">Confirm Your Reservation</h3>
        </div>
        <div class="card-body">
            <p class="text-sm text-muted mb-4">
                By confirming, you verify the details are correct and you’re available. CREaM staff will proceed to assign an officiant and finalize arrangements.
            </p>

            <div class="flex flex-col sm:flex-row gap-3">
                <form method="POST" action="{{ route('requestor.reservations.confirm-reservation', ['reservation_id' => $reservation->reservation_id, 'token' => $token]) }}" class="flex-1">
                    @csrf
                    <button type="submit" class="btn-primary w-full" onclick="return confirm('Confirm this reservation?');">
                        Confirm Reservation
                    </button>
                </form>
                <form method="POST" action="{{ route('requestor.reservations.decline-reservation', ['reservation_id' => $reservation->reservation_id, 'token' => $token]) }}" class="flex-1">
                    @csrf
                    <button type="submit" class="btn-danger w-full" onclick="return confirm('Decline and cancel this reservation?');">
                        Decline Reservation
                    </button>
                </form>
            </div>

            <p class="text-xs text-muted mt-4 text-center">If you have questions, contact the CREaM Office.</p>
        </div>
    </div>
</div>
@endsection
