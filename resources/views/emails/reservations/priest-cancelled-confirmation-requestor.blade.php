@extends('emails.layouts.default')

@section('title', 'Priest Cancelled Confirmation')
@section('heading', 'Priest Assignment Update')

@section('content')
    <div style="background-color: #fff7ed; color: #9a3412; padding: 15px; border-radius: 6px; text-align: center; border: 1px solid #fed7aa; margin-bottom: 25px;">
        <strong>Action:</strong> Reassignment In Progress
    </div>

    <p style="margin-bottom: 20px;">Dear {{ $requestor->first_name }},</p>

    <p style="margin-bottom: 20px;">
        We regret to inform you that <strong>{{ $priestName }}</strong> has cancelled their confirmation for your reservation.
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
                <td style="padding: 8px 0; color: #6b7280;">Venue:</td>
                <td style="padding: 8px 0; color: #111827; font-weight: 500;">{{ $venueName }}</td>
            </tr>
        </table>
    </div>

    <div style="background-color: #f3f4f6; padding: 20px; border-radius: 8px; border-left: 4px solid #f97316; margin-bottom: 25px;">
        <h3 style="margin-top: 0; color: #374151; font-size: 16px; margin-bottom: 10px;">Reason:</h3>
        <p style="margin: 0; color: #4b5563; font-style: italic;">"{{ $reason }}"</p>
    </div>

    <p style="margin-bottom: 20px;">
        Our administrators have been notified and will work to assign another priest for your reservation. You will receive a notification once a new priest is assigned.
    </p>

    <p style="margin-bottom: 20px;">
        We apologize for any inconvenience this may cause.
    </p>
@endsection
