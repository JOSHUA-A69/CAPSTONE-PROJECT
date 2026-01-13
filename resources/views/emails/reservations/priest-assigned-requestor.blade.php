@extends('emails.layouts.default')

@section('title', 'Priest Assigned to Reservation')

@section('content')
    <h1 style="color: #333333; font-size: 24px; font-weight: bold; margin-top: 0; margin-bottom: 24px;">
        Priest Assigned to Reservation
    </h1>

    <p style="font-size: 16px; line-height: 24px; margin-bottom: 24px;">
        Dear {{ $requestor->first_name }},
    </p>

    <div style="background-color: #dbeafe; border-left: 4px solid #3b82f6; padding: 16px; margin-bottom: 24px; border-radius: 4px;">
        <p style="margin: 0; color: #1e40af; font-weight: 500;">
            A priest has been assigned to officiate your reservation.
        </p>
    </div>

    <p style="font-size: 16px; line-height: 24px; margin-bottom: 24px;">
        The priest will now review the details and confirm their availability. You will get another notification once they confirm.
    </p>

    <div style="background-color: #f3f4f6; border-radius: 8px; padding: 24px; margin-bottom: 24px;">
        <h2 style="color: #333333; font-size: 18px; font-weight: bold; margin-top: 0; margin-bottom: 16px;">
            Assignment Details
        </h2>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding-bottom: 8px; color: #666666; width: 140px;">Service:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">{{ $reservation->service->service_name }}</td>
            </tr>
            <tr>
                <td style="padding-bottom: 8px; color: #666666;">Date & Time:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">{{ $reservation->schedule_date->format('F d, Y - h:i A') }}</td>
            </tr>
            <tr>
                <td style="padding-bottom: 8px; color: #666666;">Venue:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">
                    @if($reservation->custom_venue_name)
                        {{ $reservation->custom_venue_name }} (Custom Location)
                    @else
                        {{ $reservation->venue->name }}
                    @endif
                </td>
            </tr>
            <tr>
                <td style="padding-bottom: 8px; color: #666666;">Assigned Priest:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">{{ $priest->full_name }}</td>
            </tr>
        </table>
    </div>

    <div style="text-align: center; margin-bottom: 24px;">
        <a href="{{ url('/') }}" 
           style="background-color: #2563eb; color: #ffffff; display: inline-block; padding: 12px 24px; border-radius: 4px; text-decoration: none; font-weight: bold; font-size: 16px;">
            View Reservation
        </a>
    </div>
@endsection
