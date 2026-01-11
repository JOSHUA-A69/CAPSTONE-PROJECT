@extends('emails.layouts.default')

@section('title', 'Action Required: Pending Reservation')
@section('heading', 'Pending Approval')

@section('content')
    <div style="background-color: #fff7ed; color: #9a3412; padding: 15px; border-radius: 6px; text-align: center; border: 1px solid #fed7aa; margin-bottom: 25px;">
        <strong>Reminder:</strong> A reservation is waiting for your approval.
    </div>

    <p style="margin-bottom: 20px;">Dear {{ $adviser->first_name }},</p>

    <p style="margin-bottom: 20px;">
        A reservation request from your organization has been waiting for your approval for over 24 hours. Please review it as soon as possible.
    </p>

    <div style="background-color: #f9fafb; border-radius: 8px; padding: 20px; margin-bottom: 25px; border-left: 4px solid #f97316;">
        <h3 style="margin-top: 0; color: #374151; font-size: 16px;">Request Details</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color: #6b7280; width: 120px;">Requestor:</td>
                <td style="padding: 8px 0; color: #111827; font-weight: 500;">
                    {{ $reservation->user->first_name }} {{ $reservation->user->last_name }}
                </td>
            </tr>
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
                <td style="padding: 8px 0; color: #6b7280;">Submitted:</td>
                <td style="padding: 8px 0; color: #111827; font-weight: 500;">{{ $reservation->created_at->format('F d, Y - h:i A') }}</td>
            </tr>
        </table>
    </div>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ route('adviser.reservations.show', $reservation->reservation_id) }}" style="display: inline-block; background-color: #f97316; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;">
            Review Request
        </a>
    </div>

    <p style="color: #6b7280; font-size: 14px; margin-top: 20px;">
        If you have questions, please contact the CREaM Office.
    </p>
@endsection
