<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Approved by Adviser</title>
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
        <h1>✅ Reservation Approved by Adviser</h1>
    </div>
    <div class="content">
        <p>Dear {{ $requestor->first_name }},</p>

        <div class="success-badge">
            Your organization adviser has approved your reservation request!
        </div>

        <p>Your reservation is now awaiting final approval from the CREaM administrator, who will assign a priest to officiate your service.</p>

        <div class="details">
            <h3>Reservation Details</h3>
            <div class="details-row">
                <span class="label">Service:</span> {{ $reservation->service->service_name }}
            </div>
            <div class="details-row">
                <span class="label">Date & Time:</span> {{ $reservation->schedule_date->format('F d, Y - h:i A') }}
            </div>
            <div class="details-row">
                <span class="label">Venue:</span>
                @if($reservation->custom_venue_name)
                    📍 {{ $reservation->custom_venue_name }} <em>(Custom Location)</em>
                @else
                    {{ $reservation->venue->name }}
                @endif
            </div>
            <div class="details-row">
                <span class="label">Approved By:</span> {{ $adviser->full_name }}
            </div>
            @if($remarks)
            <div class="details-row">
                <span class="label">Remarks:</span> {{ $remarks }}
            </div>
            @endif
        </div>

        <p><strong>Next Steps:</strong></p>
        <ul>
            <li>CREaM administrator will review and assign a priest</li>
            <li>The assigned priest will confirm availability</li>
            <li>You will receive a final confirmation email</li>
        </ul>

        <div class="footer">
            <p>Center for Religious Education and Mission (CREaM)<br>
            Holy Name University<br>
            Tagbilaran City, Bohol</p>
        </div>
    </div>
</body>
</html>
