@extends('emails.layouts.default')

@section('title', 'Login Verification Code')

@section('content')
    <h1 style="color: #333333; font-size: 24px; font-weight: bold; margin-top: 0; margin-bottom: 24px;">
        Login Verification
    </h1>

    <p style="font-size: 16px; line-height: 24px; margin-bottom: 24px;">
        Hello {{ $user->first_name }},
    </p>

    <p style="font-size: 16px; line-height: 24px; margin-bottom: 24px;">
        You are attempting to sign in to your eReligiousServices account. Please use the verification code below to complete your login:
    </p>

    <div style="text-align: center; margin-bottom: 24px; padding: 30px 0;">
        <div style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); border-radius: 12px; padding: 25px 50px; display: inline-block;">
            <span style="font-size: 36px; font-weight: 700; letter-spacing: 12px; color: #ffffff; font-family: 'Courier New', monospace;">{{ $code }}</span>
        </div>
    </div>

    <div style="background-color: #f3f4f6; border-radius: 8px; padding: 16px; margin-bottom: 24px;">
        <p style="margin: 0 0 8px 0; color: #4b5563;">
            <strong style="color: #4b5563;">⏱ This code will expire in 10 minutes.</strong>
        </p>
        <p style="margin: 0; color: #6b7280; font-size: 14px;">
            If you didn't request this code, please ignore this email. Someone may have entered your email address by mistake.
        </p>
    </div>
@endsection

