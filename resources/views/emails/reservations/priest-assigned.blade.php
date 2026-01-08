@extends('emails.layouts.default')

@section('title', 'Assignment to Officiate Service')

@section('content')
    <h1 style="color: #333333; font-size: 24px; font-weight: bold; margin-top: 0; margin-bottom: 24px;">
        Assignment to Officiate Service
    </h1>

    <p style="font-size: 16px; line-height: 24px; margin-bottom: 24px;">
        Dear {{ $priest->first_name }},
    </p>

    <div style="background-color: #ede9fe; border-left: 4px solid #7c3aed; padding: 16px; margin-bottom: 24px; border-radius: 4px;">
        <p style="margin: 0; color: #5b21b6; font-weight: 500;">
            <strong>You have been assigned to officiate a religious service.</strong><br>
            Please confirm your availability as soon as possible.
        </p>
    </div>

    <div style="background-color: #f3f4f6; border-radius: 8px; padding: 24px; margin-bottom: 24px;">
        <h2 style="color: #333333; font-size: 18px; font-weight: bold; margin-top: 0; margin-bottom: 16px;">
            Service Details
        </h2>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding-bottom: 8px; color: #666666; width: 140px;">Service Type:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">{{ $service->service_name }}</td>
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
                        {{ $venue->name }}
                    @endif
                </td>
            </tr>
            <tr>
                <td style="padding-bottom: 8px; color: #666666;">Organization:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">{{ $reservation->organization->org_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="padding-bottom: 8px; color: #666666;">Requestor:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">{{ $reservation->user->full_name }}</td>
            </tr>
            <tr>
                <td style="padding-bottom: 8px; color: #666666; vertical-align: top;">Purpose:</td>
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
        <strong>Action Required:</strong>
    </p>

    <p style="font-size: 16px; line-height: 24px; margin-bottom: 24px;">
        Please log in to the <a href="{{ config('app.url') }}" style="color: #2563eb; text-decoration: none;">eReligiousServices portal</a> to confirm or decline this assignment.
    </p>

    <p style="font-size: 16px; line-height: 24px; margin-bottom: 24px;">
        If you have any conflicts or questions, please contact the CREaM office immediately.
    </p>
@endsection
