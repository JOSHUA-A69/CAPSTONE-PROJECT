@extends('emails.layouts.default')

@section('title', 'Reservation Rejected')
@section('heading', 'Reservation Rejected')

@section('content')
    <div style="background-color: #fef2f2; color: #991b1b; padding: 15px; border-radius: 6px; text-align: center; border: 1px solid #fecaca; margin-bottom: 25px;">
        <strong>Validation Status:</strong> Not Approved
    </div>

    <p style="margin-bottom: 20px;">Hello {{ $requestor->first_name }},</p>

    <p style="margin-bottom: 20px;">
        We regret to inform you that your reservation request for <strong>{{ $reservation->service->service_name }}</strong> scheduled on <strong>{{ $reservation->schedule_date->format('F d, Y \a\t h:i A') }}</strong> has been rejected by {{ $adminName }}.
    </p>

    <div style="background-color: #f3f4f6; padding: 20px; border-radius: 8px; border-left: 4px solid #ef4444; margin: 25px 0;">
        <h3 style="margin-top: 0; color: #374151; font-size: 16px; margin-bottom: 10px;">Reason for Rejection:</h3>
        <p style="margin: 0; color: #4b5563; font-style: italic;">"{{ $reason }}"</p>
    </div>

    <p style="margin-bottom: 20px;">
        Since your request was not approved, you may submit a new reservation request with a different date, time, or venue.
    </p>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ config('app.url') }}" style="display: inline-block; background-color: #4f46e5; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;">
            Submit New Request
        </a>
    </div>
@endsection
