@extends('emails.layouts.default')

@section('title', 'Reservation Request Submitted')

@section('content')
    <h1 style="color: #333333; font-size: 24px; font-weight: bold; margin-top: 0; margin-bottom: 24px;">
        Reservation Request Submitted
    </h1>

    <p style="font-size: 16px; line-height: 24px; margin-bottom: 24px;">
        Dear {{ $requestor->first_name }},
    </p>

    <p style="font-size: 16px; line-height: 24px; margin-bottom: 24px;">
        Your reservation request has been successfully submitted and is now pending review.
    </p>

    <div style="background-color: #f3f4f6; border-radius: 8px; padding: 24px; margin-bottom: 24px;">
        <h2 style="color: #333333; font-size: 18px; font-weight: bold; margin-top: 0; margin-bottom: 16px;">
            Reservation Details
        </h2>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding-bottom: 8px; color: #666666; width: 140px;">Service:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">{{ $service->service_name }}</td>
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
                        {{ $venue->name }}
                    @endif
                </td>
            </tr>
            @if(isset($organization) && $organization)
            <tr>
                <td style="padding-bottom: 8px; color: #666666;">Organization:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">{{ $organization->org_name }}</td>
            </tr>
            @endif
            <tr>
                <td style="padding-bottom: 8px; color: #666666;">Purpose:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">{{ $reservation->purpose }}</td>
            </tr>
            @if($reservation->participants_count)
            <tr>
                <td style="padding-bottom: 8px; color: #666666;">Expected Participants:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">{{ $reservation->participants_count }}</td>
            </tr>
            @endif
        </table>
    </div>

    <p style="font-size: 16px; line-height: 24px; margin-bottom: 16px;">
        <strong>Next Steps:</strong>
    </p>
    <ul style="font-size: 16px; line-height: 24px; margin-bottom: 24px; padding-left: 20px; color: #333333;">
        <li style="margin-bottom: 8px;">Your organization adviser will review the request</li>
        <li style="margin-bottom: 8px;">Upon approval, the CREaM administrator will assign a priest</li>
        <li style="margin-bottom: 8px;">The assigned priest will confirm availability</li>
    </ul>

    <p style="font-size: 16px; line-height: 24px; margin-bottom: 24px;">
        You can track the status of your request through the <a href="{{ config('app.url') }}" style="color: #2563eb; text-decoration: none;">eReligiousServices portal</a>.
    </p>
@endsection
