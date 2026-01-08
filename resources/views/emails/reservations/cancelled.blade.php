@extends('emails.layouts.default')

@section('title', 'Reservation Cancelled')

@section('content')
    <h1 style="color: #333333; font-size: 24px; font-weight: bold; margin-top: 0; margin-bottom: 24px;">
        Reservation Cancelled
    </h1>

    <div style="background-color: #fed7aa; border-left: 4px solid #ea580c; padding: 16px; margin-bottom: 24px; border-radius: 4px;">
        <p style="margin: 0; color: #9a3412; font-weight: 500;">
            This reservation has been cancelled.
        </p>
    </div>

    <div style="background-color: #f3f4f6; border-radius: 8px; padding: 24px; margin-bottom: 24px;">
        <h2 style="color: #333333; font-size: 18px; font-weight: bold; margin-top: 0; margin-bottom: 16px;">
            Cancelled Reservation
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
                <td style="padding-bottom: 8px; color: #666666;">Cancelled By:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">{{ $cancelledBy }}</td>
            </tr>
            <tr>
                <td style="padding-bottom: 8px; color: #666666; vertical-align: top;">Reason:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500; font-style: italic;">{{ $reason }}</td>
            </tr>
        </table>
    </div>

    <p style="font-size: 16px; line-height: 24px; margin-bottom: 24px;">
        All parties involved have been notified of this cancellation.
    </p>

    <p style="font-size: 16px; line-height: 24px; margin-bottom: 24px;">
        If you have any questions or need to make alternative arrangements, please contact the CREaM office.
    </p>
@endsection
