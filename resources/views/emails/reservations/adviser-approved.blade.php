@extends('emails.layouts.default')

@section('title', 'Reservation Approved by Adviser')

@section('content')
    <h1 style="color: #333333; font-size: 24px; font-weight: bold; margin-top: 0; margin-bottom: 24px;">
        Reservation Approved by Adviser
    </h1>

    <p style="font-size: 16px; line-height: 24px; margin-bottom: 24px;">
        Dear {{ $requestor->first_name }},
    </p>

    <div style="background-color: #dcfce7; border-left: 4px solid #16a34a; padding: 16px; margin-bottom: 24px; border-radius: 4px;">
        <p style="margin: 0; color: #166534; font-weight: 500;">
            Your organization adviser has approved your reservation request!
        </p>
    </div>

    <p style="font-size: 16px; line-height: 24px; margin-bottom: 24px;">
        Your reservation is now awaiting final approval from the CREaM administrator, who will assign a priest to officiate your service.
    </p>

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
                <td style="padding-bottom: 8px; color: #666666;">Approved By:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">{{ $adviser->full_name }}</td>
            </tr>
            @if($remarks)
            <tr>
                <td style="padding-bottom: 8px; color: #666666;">Remarks:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">{{ $remarks }}</td>
            </tr>
            @endif
        </table>
    </div>

    <p style="font-size: 16px; line-height: 24px; margin-bottom: 16px;">
        <strong>Next Steps:</strong>
    </p>
    <ul style="font-size: 16px; line-height: 24px; margin-bottom: 24px; padding-left: 20px; color: #333333;">
        <li style="margin-bottom: 8px;">CREaM administrator will review and assign a priest</li>
        <li style="margin-bottom: 8px;">The assigned priest will confirm availability</li>
        <li style="margin-bottom: 8px;">You will receive a final confirmation email</li>
    </ul>

    <p style="font-size: 16px; line-height: 24px; margin-bottom: 24px;">
        You can track the status of your request through the <a href="{{ config('app.url') }}" style="color: #2563eb; text-decoration: none;">eReligiousServices portal</a>.
    </p>
@endsection
