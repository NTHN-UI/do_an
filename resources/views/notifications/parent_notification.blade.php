<!DOCTYPE html>
<html>
<head>
    <title>{{ $notification->subject }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #f8f9fa; padding: 10px; text-align: center; }
        .content { padding: 20px; }
        .footer { margin-top: 20px; padding: 10px; text-align: center; font-size: 12px; color: #6c757d; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h2>{{ config('app.name') }}</h2>
    </div>

    <div class="content">
        <h3>{{ $notification->subject }}</h3>
        <p>{!! nl2br(e($content)) !!}</p>

    </div>

    <div class="footer">
        © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
    </div>
</div>
</body>
</html>
