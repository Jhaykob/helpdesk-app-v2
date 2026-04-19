<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f9f9f9; margin: 0; padding: 20px; }
        .container { max-w-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; padding: 30px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05); }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 20px; }
        .title { color: #d32f2f; margin: 0; font-size: 24px; }
        .content { margin-bottom: 30px; }
        .button-group { text-align: center; margin: 30px 0; }
        .btn { display: inline-block; padding: 12px 24px; text-decoration: none; border-radius: 4px; font-weight: bold; margin: 0 10px; text-align: center; }
        .btn-success { background-color: #22c55e; color: #ffffff; border: 1px solid #16a34a; }
        .btn-danger { background-color: #ef4444; color: #ffffff; border: 1px solid #dc2626; }
        .footer { text-align: center; color: #888; font-size: 12px; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="title">Ticket #{{ $ticket->id }} Resolved</h1>
        </div>

        <div class="content">
            <p>{{ $bodyMessage }}</p>
            <p>Did this completely fix your issue?</p>
        </div>

        <div class="button-group">
            <a href="{{ route('tickets.confirmResolution', $ticket) }}#csat-box" class="btn btn-success">Yes, Close Ticket</a>
            <a href="{{ route('tickets.rejectResolution', $ticket) }}" class="btn btn-danger">No, I Still Need Help</a>
        </div>

        <div class="footer">
            <p>If you have any questions, simply reply to this email.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
