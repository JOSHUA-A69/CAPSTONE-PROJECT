@extends('emails.layouts.default')

@section('title', 'Priest Restored Assignment')
@section('heading', 'Assignment Restored')

@section('content')
    <div style="background-color: #dcfce7; color: #166534; padding: 15px; border-radius: 6px; text-align: center; border: 1px solid #86efac; margin-bottom: 25px;">
        <strong>Update:</strong> Priest Available
    </div>

    <p style="margin-bottom: 20px;">Hello Admin/Staff,</p>

    <p style="margin-bottom: 20px;">
        <strong>{{ $priestName }}</strong> has restored their assignment for the following reservation:
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
        </table>
    </div>

    <p style="margin-bottom: 20px;">
        The priest previously declined this assignment but has now undone their decline. They will need to confirm their availability.
    </p>
@endsection
