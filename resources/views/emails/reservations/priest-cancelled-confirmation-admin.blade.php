@extends('emails.layouts.default')

@section('title', 'Priest Cancelled Confirmation')
@section('heading', 'URGENT: Priest Cancellation')

@section('content')
    <div style="background-color: #fef2f2; color: #991b1b; padding: 15px; border-radius: 6px; text-align: center; border: 1px solid #fecaca; margin-bottom: 25px;">
        <strong>URGENT:</strong> Reassignment Needed
    </div>

    <p style="margin-bottom: 20px;">Hello Admin/Staff,</p>

    <p style="margin-bottom: 20px;">
        <strong>{{ $priestName }}</strong> has CANCELLED their previously confirmed reservation.
    </p>

    <div style="background-color: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 25px;">
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
                <td style="padding: 8px 0; color: #6b7280;">Requestor:</td>
                <td style="padding: 8px 0; color: #111827; font-weight: 500;">{{ $requestorName }}</td>
            </tr>
        </table>
    </div>

    <div style="background-color: #f3f4f6; padding: 20px; border-radius: 8px; border-left: 4px solid #dc2626; margin-bottom: 25px;">
        <h3 style="margin-top: 0; color: #374151; font-size: 16px; margin-bottom: 10px;">Reason:</h3>
        <p style="margin: 0; color: #4b5563; font-style: italic;">"{{ $reason }}"</p>
    </div>

    <p style="margin-bottom: 20px;">
        <strong>Action Required:</strong> Please reassign another priest immediately.
    </p>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ config('app.url') }}" style="display: inline-block; background-color: #dc2626; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;">
            Manage Reservation
        </a>
    </div>
@endsection
