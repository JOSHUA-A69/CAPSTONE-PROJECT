<div style="font-family: system-ui, -apple-system, Segoe UI, Roboto, 'Helvetica Neue', Arial; color: #111;">
    <h2>Hello {{ $user->first_name }},</h2>

    <p>Your account at eReligiousServices has been activated and is now ready to use.</p>

    <p>You can now sign in at <a href="{{ config('app.url') }}">{{ config('app.url') }}</a>.</p>

    <p>Thank you,<br />The CREaM Team</p>
</div>
