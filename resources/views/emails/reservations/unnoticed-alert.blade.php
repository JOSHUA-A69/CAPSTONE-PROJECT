@extends('emails.layouts.default')

@section('title', 'Unnoticed Reservation Alert')
@section('heading', 'Unnoticed Reservation Alert')

@section('content')
    <div style="background-color: #fef2f2; color: #991b1b; padding: 15px; border-radius: 6px; text-align: center; border: 1px solid #fecaca; margin-bottom: 25px;">
        <strong>ALERT:</strong> Pending Over 24 Hours
    </div>

    <p style="margin-bottom: 20px;">Hello Admin/Staff,</p>

    <p style="margin-bottom: 20px;">
        A reservation request has been pending for over <strong>{{ $hoursPending }} hours</strong> without adviser action.
    </p>

    <div style="background-color: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 25px;">
        <h3 style="color: #111827; margin-top: 0; margin-bottom: 15px; border-bottom: 1px solid #e5e7eb; padding-bottom: 10px;">Reservation Details</h3>
        
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color: #6b7280; width: 140px;">Reservation ID:</td>
                <td style="padding: 8px 0; color: #111827; font-weight: 500;">#{{ $reservation->reservation_id }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Service:</td>
                <td style="padding: 8px 0; color: #111827; font-weight: 500;">{{ $reservation->service->service_name }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Schedule:</td>
                <td style="padding: 8px 0; color: #111827; font-weight: 500;">{{ $reservation->schedule_date->format('F d, Y - h:i A') }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Requestor:</td>
                <td style="padding: 8px 0; color: #111827; font-weight: 500;">{{ $requestorName }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Organization:</td>
                <td style="padding: 8px 0; color: #111827; font-weight: 500;">{{ $orgName }}</td>
            </tr>
        </table>
    </div>

    <div style="background-color: #f3f4f6; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
        <h3 style="color: #374151; margin-top: 0; margin-bottom: 10px;">Adviser Contact Info</h3>
        <p style="margin: 0 0 5px 0;"><strong>Name:</strong> {{ $adviserName }}</p>
        <p style="margin: 0 0 5px 0;"><strong>Email:</strong> <a href="mailto:{{ $adviserEmail }}" style="color: #4f46e5;">{{ $adviserEmail }}</a></p>
        <p style="margin: 0;"><strong>Phone:</strong> {{ $adviserPhone }}</p>
    </div>

    <p style="margin-bottom: 20px;">
        Please consider contacting the adviser or taking appropriate action.
    </p>
@endsection
