<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Priest Declined - Reassignment Required</title>
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
        .alert {
            background-color: #fee2e2;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #dc2626;
        }
        .reason-box {
            background-color: #fef2f2;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            border: 1px solid #fecaca;
        }
        .details {
            background-color: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .details-row {
            margin: 10px 0;
        }
        .label {
            font-weight: bold;
            color: #555;
        }
        .action-button {
            display: inline-block;
            background-color: #7c3aed;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>⚠️ Priest Declined Assignment</h1>
        <p style="margin: 5px 0;">Action Required: Reassignment Needed</p>
    </div>
    <div class="content">
        <p>Dear CREaM Administrator,</p>

        <div class="alert">
            <strong>🚨 A priest has declined their assignment for a reservation.</strong><br>
            You need to assign another presider for this service.
        </div>

        <div class="details">
            <h3>Reservation Details</h3>
            <div class="details-row">
                <span class="label">Reservation ID:</span> #{{ $reservation->reservation_id }}
            </div>
            <div class="details-row">
                <span class="label">Service Type:</span> {{ $service->service_name }}
            </div>
            <div class="details-row">
                <span class="label">Requestor:</span> {{ $reservation->user->first_name }} {{ $reservation->user->last_name }}
            </div>
            <div class="details-row">
                <span class="label">Date & Time:</span> {{ $reservation->schedule_date->format('l, F d, Y - h:i A') }}
            </div>
            <div class="details-row">
                <span class="label">Venue:</span>
                @if($reservation->custom_venue_name)
                    📍 {{ $reservation->custom_venue_name }} <em>(Custom Location)</em>
                @else
                    📍 {{ $venue->name ?? 'N/A' }}
                @endif
            </div>
        </div>

        @if($priest)
        <div class="details">
            <h3>Declined By</h3>
            <div class="details-row">
                <span class="label">Priest:</span> {{ $priest->first_name }} {{ $priest->last_name }}
            </div>
            @if($priest->email)
            <div class="details-row">
                <span class="label">Email:</span> {{ $priest->email }}
            </div>
            @endif
        </div>
        @endif

        <div class="reason-box">
            <h4 style="margin-top: 0;">Reason for Decline:</h4>
            <p style="margin: 5px 0;">{{ $reason }}</p>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ route('admin.reservations.show', $reservation->reservation_id) }}" class="action-button">
                View Reservation & Assign Priest
            </a>
        </div>

        <p><strong>Next Steps:</strong></p>
        <ol>
            <li>Review the reservation details</li>
            <li>Identify an available priest for this date and time</li>
            <li>Assign the new presider through the CREaM system</li>
            <li>The new priest will be notified automatically</li>
        </ol>

        <div class="footer">
            <p><strong>CREaM - eReligiousServices Management System</strong></p>
            <p>Holy Name University</p>
            <p style="color: #999; font-size: 12px;">
                This is an automated notification. Please do not reply to this email.
            </p>
        </div>
    </div>
</body>
</html>
