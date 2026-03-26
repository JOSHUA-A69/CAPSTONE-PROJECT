@extends('emails.layouts.default')

@section('title', 'REMINDER: Overdue Organization Booking Request')

@section('content')
    <h1 style="color: #333333; font-size: 24px; font-weight: bold; margin-top: 0; margin-bottom: 24px;">
        URGENT REMINDER
    </h1>

    <div style="background-color: #fef3c7; border: 2px solid #f59e0b; padding: 16px; margin-bottom: 24px; border-radius: 4px;">
        <p style="margin: 0; color: #92400e; font-weight: 500;">
            <strong>OVERDUE:</strong> Organization booking request requires immediate attention
        </p>
    </div>

    <p style="font-size: 16px; line-height: 24px; margin-bottom: 24px;">
        Dear CREaM Staff Member,
    </p>

    <p style="font-size: 16px; line-height: 24px; margin-bottom: 24px;">
        This is an automated reminder regarding an overdue organization booking request that has not received a response from the assigned adviser.
    </p>

    <div style="background-color: #fef2f2; border: 2px solid #ef4444; padding: 16px; margin-bottom: 24px; border-radius: 4px;">
        <h4 style="margin-top: 0; color: #991b1b; margin-bottom: 8px;">Overdue Information:</h4>
        <p style="margin-bottom: 4px; color: #991b1b;"><strong>Days Pending:</strong> {{ $daysPending }} day{{ $daysPending !== 1 ? 's' : '' }}</p>
        <p style="margin-bottom: 4px; color: #991b1b;"><strong>Originally Notified:</strong> {{ $request->adviser_notified_at->format('F j, Y \a\t g:i A') }}</p>
        <p style="margin: 0; color: #991b1b;"><strong>Status:</strong> Still pending adviser response</p>
    </div>

    <div style="background-color: #f3f4f6; border-radius: 8px; padding: 24px; margin-bottom: 24px;">
        <h2 style="color: #f59e0b; font-size: 18px; font-weight: bold; margin-top: 0; margin-bottom: 16px;">
            Request Details
        </h2>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding-bottom: 8px; color: #666666; width: 140px;">Activity Name:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">{{ $request->activity_name }}</td>
            </tr>
            <tr>
                <td style="padding-bottom: 8px; color: #666666;">Requestor:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">{{ $requestor->full_name ?? $requestor->name }} ({{ $requestor->email }})</td>
            </tr>
            <tr>
                <td style="padding-bottom: 8px; color: #666666;">Organization:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">{{ $organization->org_name }}</td>
            </tr>
            <tr>
                <td style="padding-bottom: 8px; color: #666666;">Assigned Adviser:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">{{ $adviser ? ($adviser->full_name ?? $adviser->name) . ' (' . $adviser->email . ')' : 'No adviser assigned' }}</td>
            </tr>
            <tr>
                <td style="padding-bottom: 8px; color: #666666;">Requested Date:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">{{ $request->formatted_requested_date }}</td>
            </tr>
            <tr>
                <td style="padding-bottom: 8px; color: #666666;">Original Submit:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">{{ $request->created_at->format('F j, Y \a\t g:i A') }}</td>
            </tr>
            <tr>
                <td style="padding-bottom: 8px; color: #666666; vertical-align: top;">Purpose:</td>
                <td style="padding-bottom: 8px; color: #333333; font-weight: 500;">{{ Str::limit($request->purpose, 200) }}</td>
            </tr>
        </table>
    </div>

    <div style="background-color: #fffbeb; border: 1px solid #f59e0b; padding: 16px; margin-bottom: 24px; border-radius: 4px;">
        <h4 style="margin-top: 0; color: #92400e; margin-bottom: 8px;">Staff Action Required:</h4>
        <ul style="margin: 0; padding-left: 20px; color: #92400e;">
            <li><strong>Contact the Adviser:</strong> Reach out to remind them about the pending request</li>
            <li><strong>Check Availability:</strong> Verify if the adviser is available or needs assistance</li>
            <li><strong>Escalate if Necessary:</strong> Consider reassigning if the adviser is unavailable</li>
            <li><strong>Update Requestor:</strong> Keep the requestor informed of any delays</li>
            <li><strong>Review Process:</strong> Ensure proper notification systems are working</li>
        </ul>
    </div>

    <div style="text-align: center; margin-bottom: 24px;">
        <a href="{{ route('staff.organization-bookings.show', $request) }}" style="display: inline-block; background-color: #ef4444; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;">
            View Request Details
        </a>
    </div>

    <p style="font-size: 16px; line-height: 24px; margin-bottom: 16px;">
        <strong>Important:</strong> Extended delays in responding to booking requests can negatively impact the requestor's planning and the organization's reputation. Please prioritize addressing this overdue request.
    </p>

    @if($adviser)
    <p style="font-size: 16px; line-height: 24px; margin-bottom: 24px;">
        <strong>Adviser Contact:</strong><br>
        {{ $adviser->full_name ?? $adviser->name }}<br>
        Email: <a href="mailto:{{ $adviser->email }}" style="color: #f59e0b;">{{ $adviser->email }}</a>
    </p>
    @else
    <div style="background-color: #fef2f2; padding: 16px; border-radius: 4px; margin-bottom: 24px;">
        <p style="margin: 0; color: #991b1b;">
            <strong>Warning:</strong> This organization has no assigned adviser. Please assign an adviser immediately to process this request.
        </p>
    </div>
    @endif
@endsection