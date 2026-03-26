@extends('emails.layouts.default')

@section('title', 'Final Approval Notification')
@section('heading', 'Reservation Approved')

@section('content')
    <div style="background-color: #dcfce7; color: #166534; padding: 15px; border-radius: 6px; text-align: center; border: 1px solid #86efac; margin-bottom: 25px;">
        <strong>Status:</strong> Approved & Confirmed
    </div>

    <p style="margin-bottom: 20px;">Dear Adviser,</p>

    <p style="margin-bottom: 20px;">
        The following reservation has received final approval and is now confirmed.
    </p>

    <div style="background-color: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 25px;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color: #6b7280; width: 120px;">Requestor:</td>
                <td style="padding: 8px 0; color: #111827; font-weight: 500;">{{ $requestorName }}</td>
            </tr>
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
                <td style="padding: 8px 0; color: #111827; font-weight: 500;">{{ $venueName }}</td>
            </tr>
        </table>
    </div>

    <p style="margin-bottom: 20px;">
        Please ensure all necessary preparations are in order for this event.
    </p>
@endsection
