@extends('emails.layouts.default')

@section('title', 'Confirm Your Reservation')
@section('heading', 'Action Required')

@section('content')
    <div style="background-color: #eff6ff; color: #1e40af; padding: 15px; border-radius: 6px; text-align: center; border: 1px solid #bfdbfe; margin-bottom: 25px;">
        <strong>Confirm Availability</strong>
    </div>

    <p style="margin-bottom: 20px;">Hello {{ $reservation->user->first_name }},</p>

    <p style="margin-bottom: 20px;">
        Staff has reviewed your request. Please confirm your availability for the following reservation to verify that you are still interested to proceed.
    </p>

    <div style="background-color: #f9fafb; border-radius: 8px; padding: 20px; margin-bottom: 25px;">
        <h3 style="margin-top: 0; color: #374151; font-size: 16px;">Reservation Details</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color: #6b7280; width: 120px;">Service:</td>
                <td style="padding: 8px 0; color: #111827; font-weight: 500;">{{ $reservation->service->service_name }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Date & Time:</td>
                <td style="padding: 8px 0; color: #111827; font-weight: 500;">{{ optional($reservation->schedule_date)->format('F d, Y - h:i A') }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Venue:</td>
                <td style="padding: 8px 0; color: #111827; font-weight: 500;">{{ $reservation->custom_venue_name ?? optional($reservation->venue)->name ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $confirmationUrl }}" style="display: inline-block; background-color: #2563eb; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;">
            Confirm or Decline
        </a>
    </div>

    <p style="color: #6b7280; font-size: 14px; margin-top: 20px;">
        If you do not confirm within 24 hours, your reservation slot may be released to others.
    </p>

    <p style="color: #6b7280; font-size: 12px; margin-top: 20px;">
        Button not working? Copy this link:<br>
        <a href="{{ $confirmationUrl }}" style="color: #2563eb; word-break: break-all;">{{ $confirmationUrl }}</a>
    </p>
@endsection
