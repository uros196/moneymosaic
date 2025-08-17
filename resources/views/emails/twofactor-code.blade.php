<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} – {{ __('Your authentication code') }}</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', sans-serif; background-color:#f6f6f6; padding:24px;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:600px; margin:0 auto; background:#ffffff; border-radius:8px; overflow:hidden;">
    <tr>
        <td style="padding:24px;">
            <h1 style="margin:0 0 12px; font-size:20px; color:#111827;">{{ __('Your authentication code') }}</h1>
            <p style="margin:0 0 16px; color:#374151;">
                {{ __('Use the following code to complete your sign in:') }}
            </p>
            <div style="display:inline-block; font-size:28px; letter-spacing:6px; padding:12px 16px; background:#F3F4F6; border-radius:6px; color:#111827;">
                {{ $code }}
            </div>
            <p style="margin:16px 0 0; color:#6B7280; font-size:14px;">
                {{ __('This code will expire in :minutes minutes.', ['minutes' => 10]) }}
            </p>
        </td>
    </tr>
</table>
</body>
</html>
