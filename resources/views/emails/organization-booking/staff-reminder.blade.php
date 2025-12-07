<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REMINDER: Overdue Organization Booking Request</title>
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
            background-color: #f59e0b;
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
        .urgent-badge {
            background-color: #fef3c7;
            color: #92400e;
            padding: 10px 20px;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
            margin: 20px 0;
            border: 2px solid #f59e0b;
        }
        .details {
            background-color: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            border-left: 4px solid #f59e0b;
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
        .overdue-info {
            background-color: #fef2f2;
            border: 2px solid #ef4444;
            color: #991b1b;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .action-required {
            background-color: #fffbeb;
            border: 1px solid #f59e0b;
            color: #92400e;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
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
        .btn-urgent {
            background-color: #ef4444;
            color: white;
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
        <h1>⚠️ URGENT REMINDER</h1>
        <p>Overdue Organization Booking Request - Staff Action Required</p>
    </div>

    <div class="content">
        <div class="urgent-badge">
            🚨 OVERDUE: Organization booking request requires immediate attention
        </div>

        <p>Dear CREaM Staff Member,</p>

        <p>This is an automated reminder regarding an overdue organization booking request that has not received a response from the assigned adviser.</p>

        <div class="overdue-info">
            <h4 style="margin-top: 0;">⏰ Overdue Information:</h4>
            <p><strong>Days Pending:</strong> {{ $daysPending }} day{{ $daysPending !== 1 ? 's' : '' }}</p>
            <p><strong>Originally Notified:</strong> {{ $request->adviser_notified_at->format('F j, Y \a\t g:i A') }}</p>
            <p style="margin-bottom: 0;"><strong>Status:</strong> Still pending adviser response</p>
        </div>

        <div class="details">
            <h3 style="margin-top: 0; color: #f59e0b;">Request Details</h3>
            
            <div class="details-row">
                <span class="label">Activity Name:</span>
                <span class="value">{{ $request->activity_name }}</span>
            </div>
            
            <div class="details-row">
                <span class="label">Requestor:</span>
                <span class="value">{{ $requestor->full_name ?? $requestor->name }} ({{ $requestor->email }})</span>
            </div>
            
            <div class="details-row">
                <span class="label">Organization:</span>
                <span class="value">{{ $organization->org_name }}</span>
            </div>
            
            <div class="details-row">
                <span class="label">Assigned Adviser:</span>
                <span class="value">{{ $adviser ? ($adviser->full_name ?? $adviser->name) . ' (' . $adviser->email . ')' : 'No adviser assigned' }}</span>
            </div>
            
            <div class="details-row">
                <span class="label">Requested Date:</span>
                <span class="value">{{ $request->formatted_requested_date }}</span>
            </div>
            
            <div class="details-row">
                <span class="label">Originally Submitted:</span>
                <span class="value">{{ $request->created_at->format('F j, Y \a\t g:i A') }}</span>
            </div>
            
            <div class="details-row">
                <span class="label">Purpose:</span>
                <div class="value" style="margin-top: 5px; padding: 10px; background-color: #f9fafb; border-radius: 3px;">
                    {{ Str::limit($request->purpose, 200) }}
                </div>
            </div>
        </div>

        <div class="action-required">
            <h4 style="margin-top: 0;">📋 Staff Action Required:</h4>
            <ul style="margin-bottom: 10px;">
                <li><strong>Contact the Adviser:</strong> Reach out to remind them about the pending request</li>
                <li><strong>Check Availability:</strong> Verify if the adviser is available or needs assistance</li>
                <li><strong>Escalate if Necessary:</strong> Consider reassigning if the adviser is unavailable</li>
                <li><strong>Update Requestor:</strong> Keep the requestor informed of any delays</li>
                <li><strong>Review Process:</strong> Ensure proper notification systems are working</li>
            </ul>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ route('staff.organization-bookings.show', $request) }}" class="btn btn-urgent">
                View Request Details
            </a>
        </div>

        <p><strong>Important:</strong> Extended delays in responding to booking requests can negatively impact the requestor's planning and the organization's reputation. Please prioritize addressing this overdue request.</p>

        @if($adviser)
        <p><strong>Adviser Contact:</strong><br>
        {{ $adviser->full_name ?? $adviser->name }}<br>
        Email: <a href="mailto:{{ $adviser->email }}" style="color: #f59e0b;">{{ $adviser->email }}</a></p>
        @else
        <div style="background-color: #fef2f2; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <strong>⚠️ Warning:</strong> This organization has no assigned adviser. Please assign an adviser immediately to process this request.
        </div>
        @endif

        <div class="footer">
            <p>This is an automated reminder from the CREaM Religious Services Management System.</p>
            <p>Reminder sent at: {{ now()->format('F j, Y \a\t g:i A') }}</p>
        </div>
    </div>
</body>
</html>