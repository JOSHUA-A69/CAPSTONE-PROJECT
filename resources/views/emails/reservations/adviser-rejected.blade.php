@extends('emails.layouts.default')

@section('title', 'Reservation Not Approved')

@section('content')
    <h1 style="color: #333333; font-size: 24px; font-weight: bold; margin-top: 0; margin-bottom: 24px;">
        Reservation Not Approved
    </h1>

    <p style="font-size: 16px; line-height: 24px; margin-bottom: 24px;">
        Dear {{ $requestor->first_name }},
    </p>

    <div style="background-color: #fee2e2; border-left: 4px solid #dc2626; padding: 16px; margin-bottom: 24px; border-radius: 4px;">
        <p style="margin: 0; color: #991b1b; font-weight: 500;">
            Unfortunately, your reservation request was not approved by your organization adviser.
        </p>
    </div>

    <div style="background-color: #f3f4f6; border-radius: 8px; padding: 24px; margin-bottom: 24px;">
        <h2 style="color: #333333; font-size: 18px; font-weight: bold; margin-top: 0; margin-bottom: 16px;">
            Reservation Details
        </h2>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding-bottom: 8px; color: #666666; width: 140px;">Service:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">{{ $reservation->service->service_name }}</td>
            </tr>
            <tr>
                <td style="padding-bottom: 8px; color: #666666;">Requested Date & Time:</td>
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
                <td style="padding-bottom: 8px; color: #666666;">Reviewed By:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">{{ $adviser->full_name }}</td>
            </tr>
            <tr>
                <td style="padding-bottom: 8px; color: #666666; vertical-align: top;">Reason:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500; font-style: italic;">{{ $reason }}</td>
            </tr>
        </table>
    </div>

    <p style="font-size: 16px; line-height: 24px; margin-bottom: 24px;">
        If you have any questions or would like to discuss this decision, please contact your organization adviser or the CREaM office.
    </p>

    <p style="font-size: 16px; line-height: 24px; margin-bottom: 24px;">
        You may submit a new reservation request with different details if needed.
    </p>
@endsection
