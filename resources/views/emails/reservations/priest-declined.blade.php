@extends('emails.layouts.default')

@section('title', 'Priest Declined Assignment')

@section('content')
    <h1 style="color: #333333; font-size: 24px; font-weight: bold; margin-top: 0; margin-bottom: 24px;">
        Priest Declined Assignment
    </h1>

    <div style="background-color: #fee2e2; border-left: 4px solid #dc2626; padding: 16px; margin-bottom: 24px; border-radius: 4px;">
        <p style="margin: 0; color: #991b1b; font-weight: 500;">
            <strong>A priest has declined their assignment for a reservation.</strong><br>
            You need to assign another presider for this service.
        </p>
    </div>

    <p style="font-size: 16px; line-height: 24px; margin-bottom: 24px;">
        Dear CREaM Administrator,
    </p>

    <div style="background-color: #f3f4f6; border-radius: 8px; padding: 24px; margin-bottom: 24px;">
        <h2 style="color: #333333; font-size: 18px; font-weight: bold; margin-top: 0; margin-bottom: 16px;">
            Reservation Details
        </h2>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding-bottom: 8px; color: #666666; width: 140px;">Reservation ID:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">#{{ $reservation->reservation_id }}</td>
            </tr>
            <tr>
                <td style="padding-bottom: 8px; color: #666666;">Service Type:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">{{ $service->service_name }}</td>
            </tr>
            <tr>
                <td style="padding-bottom: 8px; color: #666666;">Requestor:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">{{ $reservation->user->first_name }} {{ $reservation->user->last_name }}</td>
            </tr>
            <tr>
                <td style="padding-bottom: 8px; color: #666666;">Date & Time:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">{{ $reservation->schedule_date->format('l, F d, Y - h:i A') }}</td>
            </tr>
            <tr>
                <td style="padding-bottom: 8px; color: #666666;">Venue:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">
                    @if($reservation->custom_venue_name)
                        {{ $reservation->custom_venue_name }} (Custom Location)
                    @else
                        {{ $venue->name ?? 'N/A' }}
                    @endif
                </td>
            </tr>
        </table>
    </div>

    @if($priest)
    <div style="background-color: #f3f4f6; border-radius: 8px; padding: 24px; margin-bottom: 24px;">
        <h2 style="color: #333333; font-size: 18px; font-weight: bold; margin-top: 0; margin-bottom: 16px;">
            Declined By
        </h2>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding-bottom: 8px; color: #666666; width: 140px;">Priest:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">{{ $priest->first_name }} {{ $priest->last_name }}</td>
            </tr>
            @if($priest->email)
            <tr>
                <td style="padding-bottom: 8px; color: #666666;">Email:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">{{ $priest->email }}</td>
            </tr>
            @endif
        </table>
    </div>
    @endif

    <div style="background-color: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 16px; margin-bottom: 24px;">
        <h4 style="margin-top: 0; margin-bottom: 8px; color: #991b1b;">Reason for Decline:</h4>
        <p style="margin: 0; color: #333333;">{{ $reason }}</p>
    </div>

    <div style="text-align: center; margin-bottom: 24px;">
        <a href="{{ route('admin.reservations.show', $reservation->reservation_id) }}" style="display: inline-block; background-color: #7c3aed; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;">
            View Reservation & Assign Priest
        </a>
    </div>

    <p style="font-size: 16px; line-height: 24px; margin-bottom: 16px;">
        <strong>Next Steps:</strong>
    </p>
    <ol style="font-size: 16px; line-height: 24px; margin-bottom: 24px; padding-left: 20px; color: #333333;">
        <li style="margin-bottom: 8px;">Review the reservation details</li>
        <li style="margin-bottom: 8px;">Identify an available priest for this date and time</li>
        <li style="margin-bottom: 8px;">Assign the new presider through the CREaM system</li>
        <li style="margin-bottom: 8px;">The new priest will be notified automatically</li>
    </ol>
@endsection
