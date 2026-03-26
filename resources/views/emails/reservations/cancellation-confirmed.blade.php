@extends('emails.layouts.default')

@section('title', 'Cancellation Confirmed')
@section('heading', 'Cancellation Confirmed')

@section('content')
    <div style="background-color: #f3f4f6; color: #374151; padding: 15px; border-radius: 6px; text-align: center; border: 1px solid #d1d5db; margin-bottom: 25px;">
        <strong>Status Update:</strong> Cancelled
    </div>

    <p style="margin-bottom: 20px;">Dear {{ $requestor->first_name }},</p>

    <p style="margin-bottom: 20px;">
        Your cancellation request has been confirmed by all parties.
    </p>

    <div style="background-color: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 25px;">
        <h3 style="color: #111827; margin-top: 0; margin-bottom: 15px; border-bottom: 1px solid #e5e7eb; padding-bottom: 10px;">Reservation Details</h3>
        
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color: #6b7280; width: 120px;">Service:</td>
                <td style="padding: 8px 0; color: #111827; font-weight: 500;">{{ $reservation->service->service_name }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Original Date:</td>
                <td style="padding: 8px 0; color: #111827; font-weight: 500;">{{ $reservation->schedule_date->format('F d, Y \a\t h:i A') }}</td>
            </tr>
        </table>
    </div>

    <p style="margin-bottom: 20px;">
        The reservation has been cancelled. No further action is required.
    </p>
@endsection
