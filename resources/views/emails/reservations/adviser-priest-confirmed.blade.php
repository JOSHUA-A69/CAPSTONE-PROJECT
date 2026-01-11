@extends('emails.layouts.default')

@section('title', 'Priest Confirmed')
@section('heading', 'Priest Confirmed')

@section('content')
    <div style="background-color: #dcfce7; color: #166534; padding: 15px; border-radius: 6px; text-align: center; border: 1px solid #86efac; margin-bottom: 25px;">
        <strong>Reservation Confirmed</strong> by Priest
    </div>

    <p style="margin-bottom: 20px;">Hello {{ $adviser->first_name }},</p>

    <p style="margin-bottom: 20px;">
        <strong style="color: #0d9488;">{{ $priestName }}</strong> has confirmed availability for a reservation from your organization.
    </p>

    <div style="background-color: #f9fafb; border-radius: 8px; padding: 20px; margin-bottom: 25px; border-left: 4px solid #14b8a6;">
        <h3 style="margin-top: 0; color: #374151; font-size: 16px;">Reservation Details</h3>
        <table style="width: 100%; border-collapse: collapse;">
             <tr>
                <td style="padding: 8px 0; color: #6b7280; width: 120px;">Organization:</td>
                <td style="padding: 8px 0; color: #111827; font-weight: 500;">
                    {{ $reservation->organization->org_name ?? 'N/A' }}
                </td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Service:</td>
                <td style="padding: 8px 0; color: #111827; font-weight: 500;">{{ $reservation->service->service_name }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Date & Time:</td>
                <td style="padding: 8px 0; color: #111827; font-weight: 500;">{{ optional($reservation->schedule_date)->format('F d, Y - h:i A') }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Requestor:</td>
                <td style="padding: 8px 0; color: #111827; font-weight: 500;">{{ $reservation->user->first_name }} {{ $reservation->user->last_name }}</td>
            </tr>
        </table>
    </div>

    <p style="margin-bottom: 0;">
        The reservation is now confirmed. No further action is required from you.
    </p>
@endsection
