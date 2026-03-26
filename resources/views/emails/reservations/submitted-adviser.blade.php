@extends('emails.layouts.default')

@section('title', 'Action Required: New Reservation Request')

@section('content')
    <div style="text-align: center; margin-bottom: 24px;">
        <h1 style="color: #333333; font-size: 24px; font-weight: bold; margin-top: 0; margin-bottom: 10px;">
            New Reservation Request
        </h1>
        <div style="background-color: #fce7f3; color: #be185d; padding: 5px 10px; border-radius: 4px; display: inline-block; font-weight: 500; font-size: 14px;">
            Action Required: Review
        </div>
    </div>

    <p style="font-size: 16px; line-height: 24px; margin-bottom: 24px;">
        Dear {{ $adviser->first_name }},
    </p>

    <p style="font-size: 16px; line-height: 24px; margin-bottom: 24px;">
        <strong>{{ $requestor?->first_name ?? 'Unknown' }} {{ $requestor?->last_name ?? 'User' }}</strong> has submitted a new reservation request for your organization that requires your review and approval.
    </p>

    <div style="background-color: #f3f4f6; border-radius: 8px; padding: 24px; margin-bottom: 24px;">
        <h2 style="color: #333333; font-size: 18px; font-weight: bold; margin-top: 0; margin-bottom: 16px;">
            Request Details
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

    <p style="font-size: 16px; line-height: 24px; margin-bottom: 24px;">
        As the organization adviser, your approval is required before this request can proceed to priest assignment.
    </p>

    <div style="text-align: center; margin-bottom: 24px;">
        <a href="{{ route('adviser.reservations.show', $reservation->reservation_id) }}" 
           style="background-color: #be185d; color: #ffffff; display: inline-block; padding: 12px 24px; border-radius: 4px; text-decoration: none; font-weight: bold; font-size: 16px;">
            Review Request
        </a>
    </div>

    <p style="font-size: 14px; color: #6b7280; text-align: center;">
        If the button above doesn't work, verify via the eReligiousServices dashboard.
    </p>
@endsection
