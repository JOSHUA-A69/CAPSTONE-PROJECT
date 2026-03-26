@extends('emails.layouts.default')

@section('title', 'Verify Email')
@section('heading', 'Verify Email Address')

@section('content')
    <p style="margin-bottom: 24px;">
        Hello {{ $user->first_name }},
    </p>

    <p style="margin-bottom: 24px;">
        Please click the button below to verify your email address.
    </p>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $url }}" style="display: inline-block; background-color: #4f46e5; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;">
            Verify Email Address
        </a>
    </div>

    <p style="margin-bottom: 24px;">
        If you did not create an account, no further action is required.
    </p>
    
    <p style="color: #6b7280; font-size: 14px; margin-top: 20px;">
        If you're having trouble clicking the "Verify Email Address" button, copy and paste the URL below into your web browser:<br>
        <a href="{{ $url }}" style="color: #4f46e5; word-break: break-all;">{{ $url }}</a>
    </p>
@endsection
