@extends('emails.layouts.default')

@section('title', 'Reservation Approved')
@section('heading', 'Reservation Approved')

@section('content')
    <div style="background-color: #dcfce7; color: #166534; padding: 15px; border-radius: 6px; text-align: center; border: 1px solid #86efac; margin-bottom: 25px;">
        <strong>Success!</strong> Your reservation has been fully approved.
    </div>

    <p style="margin-bottom: 20px;">Hello {{ $reservation->user->first_name }},</p>

    <p style="margin-bottom: 20px;">
        Great news! Your reservation has been approved by the CREaM Office and is now confirmed.
    </p>

    <div style="background-color: #f9fafb; border-radius: 8px; padding: 20px; margin-bottom: 25px; border-left: 4px solid #22c55e;">
        <h3 style="margin-top: 0; color: #374151; font-size: 16px;">Confirmed Details</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color: #6b7280; width: 120px;">Service:</td>
                <td style="padding: 8px 0; color: #111827; font-weight: 500;">{{ $reservation->service->service_name }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Date & Time:</td>
                <td style="padding: 8px 0; color: #111827; font-weight: 500;">{{ optional($reservation->schedule_date)->format('F d, Y - h:i A') }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Venue:</td>
                <td style="padding: 8px 0; color: #111827; font-weight: 500;">{{ $reservation->custom_venue_name ?? optional($reservation->venue)->name ?? 'N/A' }}</td>
            </tr>
            @if(!empty($priestNames))
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Priest(s):</td>
                <td style="padding: 8px 0; color: #111827; font-weight: 500;">{{ $priestNames }}</td>
            </tr>
            @endif
        </table>
    </div>

    <p style="margin-bottom: 20px;">
        Please ensure all preparations are in place for the event. If you need to make changes, please contact the CREaM office immediately.
    </p>
@endsection
