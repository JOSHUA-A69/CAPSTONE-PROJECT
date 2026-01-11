@extends('emails.layouts.default')

@section('title', 'Requestor Confirmed')
@section('heading', 'Requestor Confirmed')

@section('content')
    <div style="background-color: #dcfce7; color: #166534; padding: 15px; border-radius: 6px; text-align: center; border: 1px solid #86efac; margin-bottom: 25px;">
        <strong>Status Update:</strong> Confirmed
    </div>

    <p style="margin-bottom: 20px;">Hello Admin/Staff,</p>

    <p style="margin-bottom: 20px;">
        <strong>{{ $requestorName }}</strong> has confirmed their availability for the reservation.
    </p>

    <div style="background-color: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 25px;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color: #6b7280; width: 120px;">Reservation ID:</td>
                <td style="padding: 8px 0; color: #111827; font-weight: 500;">#{{ $reservation->reservation_id }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Service:</td>
                <td style="padding: 8px 0; color: #111827; font-weight: 500;">{{ $reservation->service->service_name }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Date & Time:</td>
                <td style="padding: 8px 0; color: #111827; font-weight: 500;">{{ $reservation->schedule_date->format('F d, Y - h:i A') }}</td>
            </tr>
        </table>
    </div>

    <p style="margin-bottom: 20px;">
        <strong>Next Step:</strong> Review and approve in Staff panel to notify the priest for confirmation.
    </p>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ config('app.url') }}" style="display: inline-block; background-color: #4f46e5; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;">
            Go to Staff Panel
        </a>
    </div>
@endsection
