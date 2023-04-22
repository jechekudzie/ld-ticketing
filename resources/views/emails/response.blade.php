<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Response</title>
</head>
<body>
<p>Dear Customer,</p>

<p>
    {{ $comment->message }}
</p>

<p>
    This email is in response to your ticket number: {{ $comment->ticket->ticket_number }}.
</p>


<p>Thank you for contacting us.</p>
<p>Best regards,</p>
<p>Your Support Team</p>
</body>
</html>
