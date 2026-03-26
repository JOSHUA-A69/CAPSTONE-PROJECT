@extends('emails.layouts.default')

@section('title', 'Report Ready')
@section('heading', 'Report Ready')

@section('content')
    <div style="background-color: #dbeafe; color: #1e40af; padding: 15px; border-radius: 6px; text-align: center; margin-bottom: 25px;">
        <strong>Success!</strong> Your requested report has been generated.
    </div>

    <p style="margin-bottom: 20px;">Hello,</p>
    
    <p style="margin-bottom: 20px;">The report you requested is now available for download.</p>

    <div style="background-color: #f3f4f6; border-radius: 8px; padding: 20px; margin-bottom: 25px;">
        <h3 style="margin-top: 0; color: #374151; font-size: 16px;">Report Details</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color: #6b7280; width: 120px;">Total Rows:</td>
                <td style="padding: 8px 0; color: #111827; font-weight: 500;">{{ $rowsCount }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Generated At:</td>
                <td style="padding: 8px 0; color: #111827; font-weight: 500;">{{ now()->format('F j, Y h:i A') }}</td>
            </tr>
        </table>
    </div>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $url }}" style="display: inline-block; background-color: #3b82f6; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;">
            Download Report
        </a>
    </div>

    <p style="color: #6b7280; font-size: 14px; margin-top: 20px;">
        If the button above doesn't work, copy and paste this link into your browser:<br>
        <a href="{{ $url }}" style="color: #3b82f6; word-break: break-all;">{{ $url }}</a>
    </p>
@endsection
