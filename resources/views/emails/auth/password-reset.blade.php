@extends('emails.layouts.default')

@section('title', 'Reset Password')
@section('heading', 'Reset Password')

@section('content')
    <p style="margin-bottom: 24px;">
        Hello {{ $user->first_name }},
    </p>

    <p style="margin-bottom: 24px;">
        You are receiving this email because we received a password reset request for your account.
    </p>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $url }}" style="display: inline-block; background-color: #dc2626; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;">
            Reset Password
        </a>
    </div>

    <p style="margin-bottom: 24px;">
        This password reset link will expire in {{ config('auth.passwords.'.config('auth.defaults.passwords').'.expire') }} minutes.
    </p>

    <p style="margin-bottom: 24px;">
        If you did not request a password reset, no further action is required.
    </p>
    
    <p style="color: #6b7280; font-size: 14px; margin-top: 20px;">
        If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:<br>
        <a href="{{ $url }}" style="color: #4f46e5; word-break: break-all;">{{ $url }}</a>
    </p>
@endsection
