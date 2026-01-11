@extends('emails.layouts.default')

@section('title', 'Urgent Follow-up')
@section('heading', 'Urgent: Follow-up Required')

@section('content')
    <div style="background-color: #fef2f2; color: #991b1b; padding: 15px; border-radius: 6px; text-align: center; border: 1px solid #fecaca; margin-bottom: 25px;">
        <strong>ATTENTION:</strong> User Unresponsive to Cancellation
    </div>

    <p style="margin-bottom: 20px;">Hello Admin/Staff,</p>

    <p style="margin-bottom: 20px;">
        The <strong>{{ $role }}</strong> has not responded to a cancellation request within the required timeframe (1 minute). Immediate follow-up is required.
    </p>

    <div style="background-color: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 25px;">
        <h3 style="color: #111827; margin-top: 0; margin-bottom: 15px; border-bottom: 1px solid #e5e7eb; padding-bottom: 10px;">Unresponsive User Contact Info</h3>
        
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color: #6b7280; width: 120px;">Name:</td>
                <td style="padding: 8px 0; color: #111827; font-weight: 500;">{{ $contactInfo['name'] }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Email:</td>
                <td style="padding: 8px 0; color: #dc2626; font-weight: 500;">{{ $contactInfo['email'] }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Phone:</td>
                <td style="padding: 8px 0; color: #111827; font-weight: 500;">{{ $contactInfo['phone'] ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Role:</td>
                <td style="padding: 8px 0; color: #111827; font-weight: 500;">{{ $contactInfo['role'] }}</td>
            </tr>
        </table>
    </div>

    <div style="margin-bottom: 25px;">
        <h4 style="margin-bottom: 10px;">Reservation Context:</h4>
        <ul style="color: #4b5563; padding-left: 20px;">
            <li><strong>Service:</strong> {{ $reservation->service->service_name }}</li>
            <li><strong>Date:</strong> {{ $reservation->schedule_date->format('F d, Y \a\t h:i A') }}</li>
        </ul>
    </div>

    <p style="margin-bottom: 20px;">
        Please contact them immediately via phone or email to resolve the pending cancellation.
    </p>
@endsection
