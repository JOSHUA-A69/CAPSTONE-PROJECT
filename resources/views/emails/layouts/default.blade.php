<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'eReligiousServices Notification')</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7fa;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 600px; margin: 40px auto;">
        <tr>
            <td style="background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); padding: 40px;">
                <!-- Header -->
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                        <td align="center" style="padding-bottom: 30px;">
                            <img src="{{ asset('images/ers-logo.png') }}" alt="eReligiousServices" width="80" height="80" style="display: block;">
                            <h1 style="margin: 20px 0 0 0; color: #1f2937; font-size: 24px; font-weight: 600;">@yield('heading')</h1>
                        </td>
                    </tr>
                </table>

                <!-- Content -->
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                        <td style="color: #4b5563; font-size: 16px; line-height: 1.6;">
                            @yield('content')
                        </td>
                    </tr>
                </table>

                <!-- Footer -->
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                        <td style="border-top: 1px solid #e5e7eb; padding-top: 25px; margin-top: 30px;">
                            <p style="margin: 0; color: #9ca3af; font-size: 13px; text-align: center;">
                                Thank you,<br>
                                <strong style="color: #6b7280;">The CREaM Team</strong>
                            </p>
                            <p style="margin: 15px 0 0 0; color: #9ca3af; font-size: 12px; text-align: center;">
                                &copy; {{ date('Y') }} eReligiousServices. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
