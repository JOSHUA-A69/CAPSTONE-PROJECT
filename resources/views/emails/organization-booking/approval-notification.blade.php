@extends('emails.layouts.default')

@section('title', 'Organization Booking Request Approved')

@section('content')
    <h1 style="color: #333333; font-size: 24px; font-weight: bold; margin-top: 0; margin-bottom: 24px;">
        Request Approved!
    </h1>

    <div style="background-color: #dcfce7; border-left: 4px solid #16a34a; padding: 16px; margin-bottom: 24px; border-radius: 4px;">
        <p style="margin: 0; color: #166534; font-weight: 500;">
            Great news! Your booking request has been approved by the organization adviser
        </p>
    </div>

    <p style="font-size: 16px; line-height: 24px; margin-bottom: 24px;">
        Hello {{ $request->requestor->full_name ?? $request->requestor->name }},
    </p>

    <p style="font-size: 16px; line-height: 24px; margin-bottom: 24px;">
        We're pleased to inform you that your organization booking request for <strong>{{ $request->activity_name }}</strong> has been approved by the adviser for {{ $organization->org_name }}.
    </p>

    <div style="background-color: #f3f4f6; border-radius: 8px; padding: 24px; margin-bottom: 24px;">
        <h2 style="color: #16a34a; font-size: 18px; font-weight: bold; margin-top: 0; margin-bottom: 16px;">
            Approved Request Details
        </h2>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding-bottom: 8px; color: #666666; width: 140px;">Activity Name:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">{{ $request->activity_name }}</td>
            </tr>
            <tr>
                <td style="padding-bottom: 8px; color: #666666;">Organization:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">{{ $organization->org_name }}</td>
            </tr>
            <tr>
                <td style="padding-bottom: 8px; color: #666666;">Approved Date:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">{{ $request->formatted_requested_date }}</td>
            </tr>
            @if($request->requested_venue)
            <tr>
                <td style="padding-bottom: 8px; color: #666666;">Venue:</td>
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
                <td style="padding-bottom: 8px; color: #666666;">Approved by:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">{{ $adviser->full_name ?? $adviser->name }} (Organization Adviser)</td>
            </tr>
            <tr>
                <td style="padding-bottom: 8px; color: #666666;">Approval Date:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">{{ $request->adviser_responded_at ? $request->adviser_responded_at->format('F j, Y \a\t g:i A') : 'Not available' }}</td>
            </tr>
        </table>
    </div>

    @if($comments)
    <div style="background-color: #f0f9ff; border-left: 4px solid #0284c7; padding: 16px; margin-bottom: 24px; border-radius: 4px;">
        <h4 style="margin-top: 0; color: #0284c7; margin-bottom: 8px;">Adviser Comments:</h4>
        <p style="margin: 0;">{{ $comments }}</p>
    </div>
    @endif

    <div style="background-color: #fffbeb; border: 1px solid #f59e0b; padding: 16px; margin-bottom: 24px; border-radius: 4px;">
        <h4 style="margin-top: 0; color: #92400e; margin-bottom: 8px;">Next Steps:</h4>
        <ul style="margin: 0; padding-left: 20px; color: #92400e;">
            <li>Your request is now approved by the organization adviser</li>
            <li>Please coordinate with the organization for any specific requirements</li>
            <li>Ensure all preparations are made for your scheduled activity</li>
            <li>Contact the organization adviser if you need any clarifications</li>
        </ul>
    </div>

    <p style="font-size: 16px; line-height: 24px; margin-bottom: 16px;">
        <strong>Contact Information:</strong><br>
        Organization Adviser: {{ $adviser->full_name ?? $adviser->name }}<br>
        Email: <a href="mailto:{{ $adviser->email }}" style="color: #16a34a;">{{ $adviser->email }}</a>
    </p>

    <p style="font-size: 16px; line-height: 24px; margin-bottom: 24px;">
        You can also view your request details by logging into your account: <a href="{{ route('login') }}" style="color: #16a34a;">Access Your Dashboard</a>
    </p>
@endsection