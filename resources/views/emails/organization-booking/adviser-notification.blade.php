<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Organization Booking Request</title>
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
            background-color: #3b82f6;
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
        .info-badge {
            background-color: #dbeafe;
            color: #1d4ed8;
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
            border-left: 4px solid #3b82f6;
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
        .action-buttons {
            text-align: center;
            margin: 30px 0;
        }
        .btn {
            display: inline-block;
            padding: 12px 25px;
            margin: 5px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
            min-width: 120px;
        }
        .btn-approve {
            background-color: #16a34a;
            color: white;
        }
        .btn-reject {
            background-color: #dc2626;
            color: white;
        }
        .btn-view {
            background-color: #6b7280;
            color: white;
        }
        .urgency-notice {
            background-color: #fef3c7;
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
        <h1>New Organization Booking Request</h1>
        <p>Action Required: Please review this request</p>
    </div>

    <div class="content">
        <div class="info-badge">
            📋 A new booking request has been submitted for your organization
        </div>

        <p>Dear {{ $organization->adviser->full_name ?? $organization->adviser->name }},</p>

        <p>A new organization booking request has been submitted for <strong>{{ $organization->org_name }}</strong> and requires your review and approval.</p>

        <div class="details">
            <h3 style="margin-top: 0; color: #3b82f6;">Request Details</h3>
            
            <div class="details-row">
                <span class="label">Activity Name:</span>
                <span class="value">{{ $request->activity_name }}</span>
            </div>
            
            <div class="details-row">
                <span class="label">Requested by:</span>
                <span class="value">{{ $requestor->full_name ?? $requestor->name }} ({{ $requestor->email }})</span>
            </div>
            
            <div class="details-row">
                <span class="label">Organization:</span>
                <span class="value">{{ $organization->org_name }}</span>
            </div>
            
            <div class="details-row">
                <span class="label">Requested Date & Time:</span>
                <span class="value">{{ $request->formatted_requested_date }}</span>
            </div>
            
            @if($request->requested_venue)
            <div class="details-row">
                <span class="label">Preferred Venue:</span>
                <span class="value">{{ $request->requested_venue }}</span>
            </div>
            @endif
            
            @if($request->estimated_participants)
            <div class="details-row">
                <span class="label">Estimated Participants:</span>
                <span class="value">{{ $request->estimated_participants }} people</span>
            </div>
            @endif
            
            <div class="details-row">
                <span class="label">Purpose:</span>
                <div class="value" style="margin-top: 5px; padding: 10px; background-color: #f9fafb; border-radius: 3px;">
                    {{ $request->purpose }}
                </div>
            </div>
            
            @if($request->activity_details)
            <div class="details-row">
                <span class="label">Activity Details:</span>
                <div class="value" style="margin-top: 5px; padding: 10px; background-color: #f9fafb; border-radius: 3px;">
                    {{ $request->activity_details }}
                </div>
            </div>
            @endif
            
            @if($request->special_requirements)
            <div class="details-row">
                <span class="label">Special Requirements:</span>
                <div class="value" style="margin-top: 5px; padding: 10px; background-color: #f9fafb; border-radius: 3px;">
                    {{ $request->special_requirements }}
                </div>
            </div>
            @endif
            
            <div class="details-row">
                <span class="label">Submitted:</span>
                <span class="value">{{ $request->created_at->format('F j, Y \a\t g:i A') }}</span>
            </div>
        </div>

        <div class="urgency-notice">
            <strong>⏰ Response Requested:</strong> Please review this request within 24 hours. If no action is taken, CREaM staff will be notified to follow up.
        </div>

        <div class="action-buttons">
            <a href="{{ route('adviser.organization-bookings.show', $request) }}" class="btn btn-view">
                View Full Details
            </a>
        </div>

        <p>You can log in to your adviser dashboard to approve or reject this request:</p>
        <p><a href="{{ route('login') }}" style="color: #3b82f6;">{{ route('login') }}</a></p>

        <div class="footer">
            <p>This is an automated notification from the CREaM Religious Services Management System.</p>
            <p>If you have any questions, please contact the CREaM staff.</p>
        </div>
    </div>
</body>
</html>