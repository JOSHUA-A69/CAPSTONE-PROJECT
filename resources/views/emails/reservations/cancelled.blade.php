<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Cancelled</title>
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
            background-color: #ea580c;
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
        .warning {
            background-color: #fed7aa;
            color: #9a3412;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #ea580c;
            margin: 20px 0;
            text-align: center;
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
        <h1>🚫 Reservation Cancelled</h1>
    </div>
    <div class="content">
        <div class="warning">
            This reservation has been cancelled.
        </div>

        <div class="details">
            <h3>Cancelled Reservation</h3>
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
                <span class="label">Cancelled By:</span> {{ $cancelledBy }}
            </div>
            <div class="details-row">
                <span class="label">Reason:</span><br>
                <em>{{ $reason }}</em>
            </div>
        </div>

        <p>All parties involved have been notified of this cancellation.</p>

        <p>If you have any questions or need to make alternative arrangements, please contact the CREaM office.</p>

        <div class="footer">
            <p>Center for Religious Education and Mission (CREaM)<br>
            Holy Name University<br>
            Tagbilaran City, Bohol</p>
        </div>
    </div>
</body>
</html>
