@extends('emails.layouts.default')

@section('title', 'Priest Assignment Required')

@section('content')
    <h1 style="color: #333333; font-size: 24px; font-weight: bold; margin-top: 0; margin-bottom: 24px;">
        Priest Assignment Required
    </h1>

    <p style="font-size: 16px; line-height: 24px; margin-bottom: 24px;">
        Hello Admin,
    </p>

    <div style="background-color: #fef3c7; border-left: 4px solid #d97706; padding: 16px; margin-bottom: 24px; border-radius: 4px;">
        <p style="margin: 0; color: #92400e; font-weight: 500;">
            A requestor submitted a reservation and selected Any Available Priest (Admin will assign).
        </p>
    </div>

    <p style="font-size: 16px; line-height: 24px; margin-bottom: 24px;">
        Please assign a priest for this reservation as soon as possible.
    </p>

    <div style="background-color: #f3f4f6; border-radius: 8px; padding: 24px; margin-bottom: 24px;">
        <h2 style="color: #333333; font-size: 18px; font-weight: bold; margin-top: 0; margin-bottom: 16px;">
            Reservation Details
        </h2>

        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding-bottom: 8px; color: #666666; width: 160px;">Requestor:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">{{ $requestor?->full_name ?? 'Unknown User' }}</td>
            </tr>
            <tr>
                <td style="padding-bottom: 8px; color: #666666;">Service:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">{{ $reservation->service?->service_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="padding-bottom: 8px; color: #666666;">Date and Time:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">{{ optional($reservation->schedule_date)->format('F d, Y - h:i A') }}</td>
            </tr>
            <tr>
                <td style="padding-bottom: 8px; color: #666666;">Venue:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">
                    @if($reservation->custom_venue_name)
                        {{ $reservation->custom_venue_name }} (Custom Location)
                    @else
                        {{ $reservation->venue->name ?? 'N/A' }}
                    @endif
                </td>
            </tr>
            <tr>
                <td style="padding-bottom: 0; color: #666666;">Participants:</td>
                <td style="padding-bottom: 0; color: #333333; font-weight: 500;">{{ $reservation->participants_count ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <div style="text-align: center; margin-bottom: 24px;">
        <a href="{{ route('admin.reservations.show', $reservation->reservation_id) }}"
           style="background-color: #2563eb; color: #ffffff; display: inline-block; padding: 12px 24px; border-radius: 4px; text-decoration: none; font-weight: bold; font-size: 16px;">
            Assign Priest Now
        </a>
    </div>
@endsection
