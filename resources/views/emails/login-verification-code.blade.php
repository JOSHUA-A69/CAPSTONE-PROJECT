<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Verification Code</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7fa;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 600px; margin: 40px auto;">
        <tr>
            <td style="background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); padding: 40px;">
                <!-- Header -->
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                        <td align="center" style="padding-bottom: 30px;">
                            <img src="{{ config('app.url') }}/images/ers-logo.png" alt="eReligiousServices" width="80" height="80" style="display: block;">
                            <h1 style="margin: 20px 0 0 0; color: #1f2937; font-size: 24px; font-weight: 600;">Login Verification</h1>
                        </td>
                    </tr>
                </table>

                <!-- Content -->
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                        <td style="color: #4b5563; font-size: 16px; line-height: 1.6;">
                            <p style="margin: 0 0 20px 0;">Hello <strong>{{ $user->first_name }}</strong>,</p>
                            <p style="margin: 0 0 20px 0;">You are attempting to sign in to your eReligiousServices account. Please use the verification code below to complete your login:</p>
                        </td>
                    </tr>
                </table>

                <!-- Code Box -->
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                        <td align="center" style="padding: 30px 0;">
                            <div style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); border-radius: 12px; padding: 25px 50px; display: inline-block;">
                                <span style="font-size: 36px; font-weight: 700; letter-spacing: 12px; color: #ffffff; font-family: 'Courier New', monospace;">{{ $code }}</span>
                            </div>
                        </td>
                    </tr>
                </table>

                <!-- Info -->
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                        <td style="color: #6b7280; font-size: 14px; line-height: 1.6; padding-top: 20px;">
                            <p style="margin: 0 0 15px 0;">
                                <strong style="color: #4b5563;">⏱ This code will expire in 10 minutes.</strong>
                            </p>
                            <p style="margin: 0 0 10px 0;">If you didn't request this code, please ignore this email. Someone may have entered your email address by mistake.</p>
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
                                © {{ date('Y') }} eReligiousServices. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
