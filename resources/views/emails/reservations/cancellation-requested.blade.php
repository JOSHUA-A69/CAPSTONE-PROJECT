@extends('emails.layouts.default')

@section('title', 'Cancellation Request')
@section('heading', 'Cancellation Request')

@section('content')
    <div style="background-color: #fff7ed; color: #9a3412; padding: 15px; border-radius: 6px; text-align: center; border: 1px solid #fed7aa; margin-bottom: 25px;">
        <strong>Action Required:</strong> Cancellation Request Received
    </div>

    @if($recipientName)
        <p style="margin-bottom: 20px;">Dear {{ $recipientName }},</p>
    @else
        <p style="margin-bottom: 20px;">Hello,</p>
    @endif

    <p style="margin-bottom: 20px;">
        <strong>{{ $requestorName }}</strong> has requested to cancel their reservation.
    </p>

    <div style="background-color: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 25px;">
        <h3 style="color: #111827; margin-top: 0; margin-bottom: 15px; border-bottom: 1px solid #e5e7eb; padding-bottom: 10px;">Reservation Details</h3>
        
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color: #6b7280; width: 120px;">Service:</td>
                <td style="padding: 8px 0; color: #111827; font-weight: 500;">{{ $reservation->service->service_name }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Date & Time:</td>
                <td style="padding: 8px 0; color: #111827; font-weight: 500;">{{ $reservation->schedule_date->format('F d, Y \a\t h:i A') }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Venue:</td>
                <td style="padding: 8px 0; color: #111827; font-weight: 500;">{{ $reservation->custom_venue_name ?? $reservation->venue->name ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <div style="background-color: #f3f4f6; padding: 20px; border-radius: 8px; border-left: 4px solid #f97316; margin-bottom: 25px;">
        <h3 style="margin-top: 0; color: #374151; font-size: 16px; margin-bottom: 10px;">Result/Reason:</h3>
        <p style="margin: 0; color: #4b5563; font-style: italic;">"{{ $reason }}"</p>
    </div>

    <p style="margin-bottom: 20px;">
        Please log in to the system to acknowledge or confirm this cancellation request.
    </p>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ config('app.url') }}" style="display: inline-block; background-color: #f97316; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;">
            View Request
        </a>
    </div>
@endsection
