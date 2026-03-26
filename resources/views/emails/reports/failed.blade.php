@extends('emails.layouts.default')

@section('title', 'Report Failed')
@section('heading', 'Generation Failed')

@section('content')
    <div style="background-color: #fef2f2; color: #b91c1c; padding: 15px; border-radius: 6px; text-align: center; border: 1px solid #fca5a5; margin-bottom: 25px;">
        <strong>Error:</strong> Something went wrong.
    </div>

    <p style="margin-bottom: 20px;">Hello,</p>

    <p style="margin-bottom: 20px;">We encountered an unexpected error while trying to generate your requested report.</p>

    <div style="background-color: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 20px; margin-bottom: 25px;">
        <h3 style="margin-top: 0; color: #991b1b; font-size: 16px;">Error Details</h3>
        <p style="font-family: monospace; background-color: white; padding: 10px; border-radius: 4px; border: 1px solid #e5e7eb; color: #ef4444; margin-bottom: 0;">
            {{ $errorMessage }}
        </p>
    </div>

    <p style="margin-bottom: 0;">
        Please try generating the report again. If the problem persists, please contact the system administrator.
    </p>
@endsection
