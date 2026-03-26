@extends('emails.layouts.default')

@section('title', 'New Organization Booking Request')

@section('content')
    <h1 style="color: #333333; font-size: 24px; font-weight: bold; margin-top: 0; margin-bottom: 24px;">
        New Organization Booking Request
    </h1>

    <div style="background-color: #dbeafe; border-left: 4px solid #3b82f6; padding: 16px; margin-bottom: 24px; border-radius: 4px;">
        <p style="margin: 0; color: #1d4ed8; font-weight: 500;">
            A new booking request has been submitted for your organization
        </p>
    </div>

    <p style="font-size: 16px; line-height: 24px; margin-bottom: 24px;">
        Dear {{ $organization->adviser?->full_name ?? $organization->adviser?->name ?? 'Adviser' }},
    </p>

    <p style="font-size: 16px; line-height: 24px; margin-bottom: 24px;">
        A new organization booking request has been submitted for <strong>{{ $organization->org_name }}</strong> and requires your review and approval.
    </p>

    <div style="background-color: #f3f4f6; border-radius: 8px; padding: 24px; margin-bottom: 24px;">
        <h2 style="color: #3b82f6; font-size: 18px; font-weight: bold; margin-top: 0; margin-bottom: 16px;">
            Request Details
        </h2>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding-bottom: 8px; color: #666666; width: 140px;">Activity Name:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">{{ $request->activity_name }}</td>
            </tr>
            <tr>
                <td style="padding-bottom: 8px; color: #666666;">Requested by:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">{{ $requestor->full_name ?? $requestor->name }} ({{ $requestor->email }})</td>
            </tr>
            <tr>
                <td style="padding-bottom: 8px; color: #666666;">Organization:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">{{ $organization->org_name }}</td>
            </tr>
            <tr>
                <td style="padding-bottom: 8px; color: #666666;">Requested Date:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">{{ $request->formatted_requested_date }}</td>
            </tr>
            @if($request->requested_venue)
            <tr>
                <td style="padding-bottom: 8px; color: #666666;">Preferred Venue:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">{{ $request->requested_venue }}</td>
            </tr>
            @endif
            @if($request->estimated_participants)
            <tr>
                <td style="padding-bottom: 8px; color: #666666;">Est. Participants:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">{{ $request->estimated_participants }} people</td>
            </tr>
            @endif
            <tr>
                <td style="padding-bottom: 8px; color: #666666; vertical-align: top;">Purpose:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">{{ $request->purpose }}</td>
            </tr>
            @if($request->activity_details)
            <tr>
                <td style="padding-bottom: 8px; color: #666666; vertical-align: top;">Details:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">{{ $request->activity_details }}</td>
            </tr>
            @endif
            @if($request->special_requirements)
            <tr>
                <td style="padding-bottom: 8px; color: #666666; vertical-align: top;">Requirements:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">{{ $request->special_requirements }}</td>
            </tr>
            @endif
            <tr>
                <td style="padding-bottom: 8px; color: #666666;">Submitted:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">{{ $request->created_at->format('F j, Y \a\t g:i A') }}</td>
            </tr>
        </table>
    </div>

    <div style="background-color: #fef3c7; border: 1px solid #f59e0b; padding: 16px; margin-bottom: 24px; border-radius: 4px;">
        <p style="margin: 0; color: #92400e; font-weight: 500;">
            <strong>Response Requested:</strong> Please review this request within 24 hours. If no action is taken, CREaM staff will be notified to follow up.
        </p>
    </div>

    <div style="text-align: center; margin-bottom: 24px;">
        <a href="{{ route('adviser.organization-bookings.show', $request) }}" style="display: inline-block; background-color: #3b82f6; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;">
            View Full Details
        </a>
    </div>

    <p style="font-size: 16px; line-height: 24px; margin-bottom: 24px;">
        You can log in to your adviser dashboard to approve or reject this request: <a href="{{ route('login') }}" style="color: #3b82f6;">{{ route('login') }}</a>
    </p>
@endsection
</html>