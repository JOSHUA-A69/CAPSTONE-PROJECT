@extends('emails.layouts.default')

@section('title', 'All Priests Confirmed')
@section('heading', 'Ready for Final Approval')

@section('content')
    <div style="background-color: #dcfce7; color: #166534; padding: 15px; border-radius: 6px; text-align: center; border: 1px solid #86efac; margin-bottom: 25px;">
        <strong>Action Required:</strong> Final Approval
    </div>

    <p style="margin-bottom: 20px;">Hello Admin/Staff,</p>

    <p style="margin-bottom: 20px;">
        All assigned priests have confirmed their availability. This reservation is now ready for your final approval.
    </p>

    <div style="background-color: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 25px;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color: #6b7280; width: 120px;">Service:</td>
                <td style="padding: 8px 0; color: #111827; font-weight: 500;">{{ $reservation->service->service_name }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Date & Time:</td>
                <td style="padding: 8px 0; color: #111827; font-weight: 500;">{{ $reservation->schedule_date->format('F d, Y - h:i A') }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Requestor:</td>
                <td style="padding: 8px 0; color: #111827; font-weight: 500;">{{ $requestorName }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Priests:</td>
                <td style="padding: 8px 0; color: #111827; font-weight: 500;">{{ $priestNames }}</td>
            </tr>
        </table>
    </div>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ config('app.url') }}" style="display: inline-block; background-color: #4f46e5; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;">
            Manage Reservation
        </a>
    </div>
@endsection
