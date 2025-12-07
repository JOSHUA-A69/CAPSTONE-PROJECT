<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organization Booking Request Approved</title>
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
            background-color: #16a34a;
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
        .success-badge {
            background-color: #dcfce7;
            color: #166534;
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
            border-left: 4px solid #16a34a;
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
        <h1>✅ Request Approved!</h1>
        <p>Your organization booking request has been approved</p>
    </div>

    <div class="content">
        <div class="success-badge">
            🎉 Great news! Your booking request has been approved by the organization adviser
        </div>

        <p>Hello {{ $request->requestor->full_name ?? $request->requestor->name }},</p>

        <p>We're pleased to inform you that your organization booking request for <strong>{{ $request->activity_name }}</strong> has been approved by the adviser for {{ $organization->org_name }}.</p>

        <div class="details">
            <h3 style="margin-top: 0; color: #16a34a;">Approved Request Details</h3>
            
            <div class="details-row">
                <span class="label">Activity Name:</span>
                <span class="value">{{ $request->activity_name }}</span>
            </div>
            
            <div class="details-row">
                <span class="label">Organization:</span>
                <span class="value">{{ $organization->org_name }}</span>
            </div>
            
            <div class="details-row">
                <span class="label">Approved Date & Time:</span>
                <span class="value">{{ $request->formatted_requested_date }}</span>
            </div>
            
            @if($request->requested_venue)
            <div class="details-row">
                <span class="label">Venue:</span>
                <span class="value">{{ $request->requested_venue }}</span>
            </div>
            @endif
            
            @if($request->estimated_participants)
            <div class="details-row">
                <span class="label">Expected Participants:</span>
                <span class="value">{{ $request->estimated_participants }} people</span>
            </div>
            @endif
            
            <div class="details-row">
                <span class="label">Approved by:</span>
                <span class="value">{{ $adviser->full_name ?? $adviser->name }} (Organization Adviser)</span>
            </div>
            
            <div class="details-row">
                <span class="label">Approval Date:</span>
                <span class="value">{{ $request->adviser_responded_at ? $request->adviser_responded_at->format('F j, Y \a\t g:i A') : 'Not available' }}</span>
            </div>
        </div>

        @if($comments)
        <div class="comments-section">
            <h4 style="margin-top: 0; color: #0284c7;">Adviser Comments:</h4>
            <p style="margin-bottom: 0;">{{ $comments }}</p>
        </div>
        @endif

        <div class="next-steps">
            <h4 style="margin-top: 0;">📋 Next Steps:</h4>
            <ul style="margin-bottom: 0;">
                <li>Your request is now approved by the organization adviser</li>
                <li>Please coordinate with the organization for any specific requirements</li>
                <li>Ensure all preparations are made for your scheduled activity</li>
                <li>Contact the organization adviser if you need any clarifications</li>
            </ul>
        </div>

        <p><strong>Contact Information:</strong></p>
        <p>Organization Adviser: {{ $adviser->full_name ?? $adviser->name }}<br>
        Email: <a href="mailto:{{ $adviser->email }}" style="color: #16a34a;">{{ $adviser->email }}</a></p>

        <p>You can also view your request details by logging into your account:</p>
        <p><a href="{{ route('login') }}" style="color: #16a34a;">Access Your Dashboard</a></p>

        <div class="footer">
            <p>Thank you for using the CREaM Religious Services Management System.</p>
            <p>If you have any questions, please contact the CREaM staff or your organization adviser.</p>
        </div>
    </div>
</body>
</html>