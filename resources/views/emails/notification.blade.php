<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $notification->subject }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            padding: 20px;
            max-width: 600px;
            margin: 0 auto;
        }
        .header {
            border-bottom: 2px solid #0066cc;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #666;
        }
        img.pixel-tracker {
            width: 1px;
            height: 1px;
            position: absolute;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $notification->subject }}</h2>
    </div>
    
    <div class="content">
        {!! $content !!}
    </div>
    
    <div class="footer">
        <p>This is an automated notification from МУИС Thesis Management System.</p>
        <p>Please do not reply to this email.</p>
    </div>
    
    <!-- Tracking pixel to detect email opens -->
    <img class="pixel-tracker" src="{{ route('notification.track', ['recipient' => $recipientId]) }}" alt="">
</body>
</html>