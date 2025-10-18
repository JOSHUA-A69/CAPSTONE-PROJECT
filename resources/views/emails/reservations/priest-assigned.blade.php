<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Priest Assignment</title>
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
            background-color: #7c3aed;
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
        .highlight {
            background-color: #ede9fe;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
            border-left: 4px solid #7c3aed;
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
        <h1>⛪ Assignment to Officiate Service</h1>
    </div>
    <div class="content">
        <p>Dear {{ $priest->first_name }},</p>

        <div class="highlight">
            <strong>You have been assigned to officiate a religious service.</strong><br>
            Please confirm your availability as soon as possible.
        </div>

        <div class="details">
            <h3>Service Details</h3>
            <div class="details-row">
                <span class="label">Service Type:</span> {{ $service->service_name }}
            </div>
            <div class="details-row">
                <span class="label">Date & Time:</span> {{ $reservation->schedule_date->format('l, F d, Y - h:i A') }}
            </div>
            <div class="details-row">
                <span class="label">Venue:</span>
                @if($reservation->custom_venue_name)
                    📍 {{ $reservation->custom_venue_name }} <em>(Custom Location)</em>
                @else
                    {{ $venue->name }}
                @endif
            </div>
            <div class="details-row">
                <span class="label">Organization:</span> {{ $reservation->organization->org_name ?? 'N/A' }}
            </div>
            <div class="details-row">
                <span class="label">Requestor:</span> {{ $reservation->user->full_name }}
            </div>
            <div class="details-row">
                <span class="label">Purpose:</span><br>
                {{ $reservation->purpose }}
            </div>
            @if($reservation->participants_count)
            <div class="details-row">
                <span class="label">Expected Participants:</span> {{ $reservation->participants_count }}
            </div>
            @endif
        </div>

        <p><strong>Action Required:</strong></p>
        <p>Please log in to the eReligiousServices portal to confirm or decline this assignment.</p>

        <p>If you have any conflicts or questions, please contact the CREaM office immediately.</p>

        <div class="footer">
            <p>Center for Religious Education and Mission (CREaM)<br>
            Holy Name University<br>
            Tagbilaran City, Bohol</p>
        </div>
    </div>
</body>
</html>
