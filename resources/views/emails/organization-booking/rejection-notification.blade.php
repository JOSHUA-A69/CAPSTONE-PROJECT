@extends('emails.layouts.default')

@section('title', 'Organization Booking Request - Update Required')

@section('content')
    <h1 style="color: #333333; font-size: 24px; font-weight: bold; margin-top: 0; margin-bottom: 24px;">
        Request Update Required
    </h1>

    <div style="background-color: #fef2f2; border-left: 4px solid #dc2626; padding: 16px; margin-bottom: 24px; border-radius: 4px;">
        <p style="margin: 0; color: #991b1b; font-weight: 500;">
            Your booking request requires changes before approval
        </p>
    </div>

    <p style="font-size: 16px; line-height: 24px; margin-bottom: 24px;">
        Hello {{ $request->requestor->full_name ?? $request->requestor->name }},
    </p>

    <p style="font-size: 16px; line-height: 24px; margin-bottom: 24px;">
        Thank you for your interest in {{ $organization->org_name }}. After reviewing your booking request for <strong>{{ $request->activity_name }}</strong>, the organization adviser has requested some changes before approval.
    </p>

    <div style="background-color: #f3f4f6; border-radius: 8px; padding: 24px; margin-bottom: 24px;">
        <h2 style="color: #dc2626; font-size: 18px; font-weight: bold; margin-top: 0; margin-bottom: 16px;">
            Request Details
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
                <td style="padding-bottom: 8px; color: #666666;">Requested Date:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">{{ $request->formatted_requested_date }}</td>
            </tr>
            <tr>
                <td style="padding-bottom: 8px; color: #666666;">Reviewed by:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">{{ $adviser->full_name ?? $adviser->name }} (Organization Adviser)</td>
            </tr>
            <tr>
                <td style="padding-bottom: 8px; color: #666666;">Review Date:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">{{ $request->adviser_responded_at ? $request->adviser_responded_at->format('F j, Y \a\t g:i A') : 'Not available' }}</td>
            </tr>
        </table>
    </div>

    <div style="background-color: #fef2f2; border-left: 4px solid #dc2626; padding: 16px; margin-bottom: 24px; border-radius: 4px;">
        <h4 style="margin-top: 0; color: #dc2626; margin-bottom: 8px;">Reason for Changes:</h4>
        <p style="margin: 0;">{{ $reason }}</p>
    </div>

    @if($comments)
    <div style="background-color: #f0f9ff; border-left: 4px solid #0284c7; padding: 16px; margin-bottom: 24px; border-radius: 4px;">
        <h4 style="margin-top: 0; color: #0284c7; margin-bottom: 8px;">Additional Comments from Adviser:</h4>
        <p style="margin: 0;">{{ $comments }}</p>
    </div>
    @endif

    <div style="background-color: #fffbeb; border: 1px solid #f59e0b; padding: 16px; margin-bottom: 24px; border-radius: 4px;">
        <h4 style="margin-top: 0; color: #92400e; margin-bottom: 8px;">What You Can Do:</h4>
        <ul style="margin: 0; padding-left: 20px; color: #92400e;">
            <li><strong>Revise and Resubmit:</strong> Modify your request based on the feedback and submit again</li>
            <li><strong>Contact the Adviser:</strong> Reach out directly for clarification or discussion</li>
            <li><strong>Submit a New Request:</strong> Create a completely new request if needed</li>
            <li><strong>Seek Alternative Solutions:</strong> Consider alternative dates, venues, or arrangements</li>
        </ul>
    </div>

    <p style="font-size: 16px; line-height: 24px; margin-bottom: 16px;">
        <strong>Contact Information:</strong><br>
        Organization Adviser: {{ $adviser->full_name ?? $adviser->name }}<br>
        Email: <a href="mailto:{{ $adviser->email }}" style="color: #dc2626;">{{ $adviser->email }}</a>
    </p>

    <p style="font-size: 16px; line-height: 24px; margin-bottom: 24px;">
        You can view your request and submit a new one by logging into your account: <a href="{{ route('login') }}" style="color: #dc2626;">Access Your Dashboard</a>
    </p>

    <p style="font-size: 16px; line-height: 24px; margin-bottom: 24px;">
        We encourage you to work with the organization adviser to find a solution that works for everyone. The CREaM team is here to support successful collaboration between requestors and organizations.
    </p>
@endsection