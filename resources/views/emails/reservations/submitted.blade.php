<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Submitted</title>
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
            background-color: #2563eb;
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
        .details {
            background-color: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            border-left: 4px solid #2563eb;
        }
        .details-row {
            margin: 10px 0;
        }
        .label {
            font-weight: bold;
            color: #555;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #666;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            background-color: #2563eb;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🙏 Reservation Request Submitted</h1>
    </div>
    <div class="content">
        <p>Dear {{ $requestor->first_name }},</p>

        <p>Your reservation request has been successfully submitted and is now pending review.</p>

        <div class="details">
            <h3>Reservation Details</h3>
            <div class="details-row">
                <span class="label">Service:</span> {{ $service->service_name }}
            </div>
            <div class="details-row">
                <span class="label">Date & Time:</span> {{ $reservation->schedule_date->format('F d, Y - h:i A') }}
            </div>
            <div class="details-row">
                <span class="label">Venue:</span>
                @if($reservation->custom_venue_name)
                    📍 {{ $reservation->custom_venue_name }} <em>(Custom Location)</em>
                @else
                    {{ $venue->name }}
                @endif
            </div>
            @if($organization)
            <div class="details-row">
                <span class="label">Organization:</span> {{ $organization->org_name }}
            </div>
            @endif
            <div class="details-row">
                <span class="label">Purpose:</span> {{ $reservation->purpose }}
            </div>
            @if($reservation->participants_count)
            <div class="details-row">
                <span class="label">Expected Participants:</span> {{ $reservation->participants_count }}
            </div>
            @endif
        </div>

        <p><strong>Next Steps:</strong></p>
        <ol>
            <li>Your organization adviser will review the request</li>
            <li>Upon adviser approval, CREaM administrator will assign a priest</li>
            <li>The assigned priest will confirm availability</li>
            <li>You will receive notifications at each stage</li>
        </ol>

        <p>You can track the status of your request through the eReligiousServices portal.</p>

        <div class="footer">
            <p>Center for Religious Education and Mission (CREaM)<br>
            Holy Name University<br>
            Tagbilaran City, Bohol</p>
        </div>
    </div>
</body>
</html>
