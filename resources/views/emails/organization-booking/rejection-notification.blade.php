<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organization Booking Request - Update Required</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #dc2626;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 5px 5px;
        }
        .reject-badge {
            background-color: #fef2f2;
            color: #991b1b;
            padding: 10px 20px;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
            margin: 20px 0;
        }
        .details {
            background-color: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            border-left: 4px solid #dc2626;
        }
        .details-row {
            margin: 10px 0;
        }
        .label {
            font-weight: bold;
            color: #555;
        }
        .value {
            color: #333;
            margin-left: 10px;
        }
        .reason-section {
            background-color: #fef2f2;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            border-left: 4px solid #dc2626;
        }
        .comments-section {
            background-color: #f0f9ff;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            border-left: 4px solid #0284c7;
        }
        .next-steps {
            background-color: #fffbeb;
            border: 1px solid #f59e0b;
            color: #92400e;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Request Update Required</h1>
        <p>Your organization booking request needs revision</p>
    </div>

    <div class="content">
        <div class="reject-badge">
            📝 Your booking request requires changes before approval
        </div>

        <p>Hello {{ $request->requestor->full_name ?? $request->requestor->name }},</p>

        <p>Thank you for your interest in {{ $organization->org_name }}. After reviewing your booking request for <strong>{{ $request->activity_name }}</strong>, the organization adviser has requested some changes before approval.</p>

        <div class="details">
            <h3 style="margin-top: 0; color: #dc2626;">Request Details</h3>
            
            <div class="details-row">
                <span class="label">Activity Name:</span>
                <span class="value">{{ $request->activity_name }}</span>
            </div>
            
            <div class="details-row">
                <span class="label">Organization:</span>
                <span class="value">{{ $organization->org_name }}</span>
            </div>
            
            <div class="details-row">
                <span class="label">Requested Date & Time:</span>
                <span class="value">{{ $request->formatted_requested_date }}</span>
            </div>
            
            <div class="details-row">
                <span class="label">Reviewed by:</span>
                <span class="value">{{ $adviser->full_name ?? $adviser->name }} (Organization Adviser)</span>
            </div>
            
            <div class="details-row">
                <span class="label">Review Date:</span>
                <span class="value">{{ $request->adviser_responded_at ? $request->adviser_responded_at->format('F j, Y \a\t g:i A') : 'Not available' }}</span>
            </div>
        </div>

        <div class="reason-section">
            <h4 style="margin-top: 0; color: #dc2626;">⚠️ Reason for Changes:</h4>
            <p style="margin-bottom: 0;">{{ $reason }}</p>
        </div>

        @if($comments)
        <div class="comments-section">
            <h4 style="margin-top: 0; color: #0284c7;">💬 Additional Comments from Adviser:</h4>
            <p style="margin-bottom: 0;">{{ $comments }}</p>
        </div>
        @endif

        <div class="next-steps">
            <h4 style="margin-top: 0;">🔄 What You Can Do:</h4>
            <ul style="margin-bottom: 0;">
                <li><strong>Revise and Resubmit:</strong> Modify your request based on the feedback and submit again</li>
                <li><strong>Contact the Adviser:</strong> Reach out directly for clarification or discussion</li>
                <li><strong>Submit a New Request:</strong> Create a completely new request if needed</li>
                <li><strong>Seek Alternative Solutions:</strong> Consider alternative dates, venues, or arrangements</li>
            </ul>
        </div>

        <p><strong>Contact Information:</strong></p>
        <p>Organization Adviser: {{ $adviser->full_name ?? $adviser->name }}<br>
        Email: <a href="mailto:{{ $adviser->email }}" style="color: #dc2626;">{{ $adviser->email }}</a></p>

        <p>You can view your request and submit a new one by logging into your account:</p>
        <p><a href="{{ route('login') }}" style="color: #dc2626;">Access Your Dashboard</a></p>

        <p>We encourage you to work with the organization adviser to find a solution that works for everyone. The CREaM team is here to support successful collaboration between requestors and organizations.</p>

        <div class="footer">
            <p>Thank you for your understanding and continued participation in CREaM activities.</p>
            <p>If you have any questions about this process, please contact the CREaM staff.</p>
        </div>
    </div>
</body>
</html>