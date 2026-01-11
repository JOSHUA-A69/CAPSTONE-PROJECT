@extends('emails.layouts.default')

@section('title', 'Reservation Confirmed')
@section('heading', 'Reservation Confirmed')

@section('content')
    <div style="background-color: #dcfce7; color: #166534; padding: 15px; border-radius: 6px; text-align: center; border: 1px solid #86efac; margin-bottom: 25px;">
        <strong>Good News!</strong> Your reservation is confirmed.
    </div>

    <p style="margin-bottom: 20px;">Dear {{ $reservation->user->first_name }},</p>

    <p style="margin-bottom: 20px;">
        <strong style="color: #0d9488;">{{ $priestName }}</strong> has confirmed their availability for your reservation. We look forward to serving you!
    </p>

    <div style="background-color: #f9fafb; border-radius: 8px; padding: 20px; margin-bottom: 25px; border-left: 4px solid #14b8a6;">
        <h3 style="margin-top: 0; color: #374151; font-size: 16px;">Reservation Details</h3>
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
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Officiant:</td>
                <td style="padding: 8px 0; color: #111827; font-weight: 500;">{{ $priestName }}</td>
            </tr>
        </table>
    </div>

    <p style="margin-bottom: 0;">
        You can view full details in your reservation history.
    </p>
@endsection
