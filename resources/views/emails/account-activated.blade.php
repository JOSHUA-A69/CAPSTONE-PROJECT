@extends('emails.layouts.default')

@section('title', 'Account Activated')

@section('content')
    <h1 style="color: #333333; font-size: 24px; font-weight: bold; margin-top: 0; margin-bottom: 24px;">
        Account Activated
    </h1>

    <p style="font-size: 16px; line-height: 24px; margin-bottom: 24px;">
        Hello {{ $user->first_name }},
    </p>

    <p style="font-size: 16px; line-height: 24px; margin-bottom: 24px;">
        Your account at eReligiousServices has been activated and is now ready to use.
    </p>

    <div style="text-align: center; margin-bottom: 24px; padding: 20px 0;">
        <a href="{{ config('app.url') }}" style="background-color: #4f46e5; color: #ffffff; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: 600; display: inline-block;">
            Sign In Now
        </a>
    </div>

    <p style="font-size: 16px; line-height: 24px; margin-bottom: 24px;">
        Or visit this link: <a href="{{ config('app.url') }}" style="color: #4f46e5;">{{ config('app.url') }}</a>
    </p>
@endsection
