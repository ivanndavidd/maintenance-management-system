<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Maintenance Request Received</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #17a2b8; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f8f9fa; padding: 20px; border: 1px solid #ddd; }
        .ticket-info { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .ticket-info table { width: 100%; border-collapse: collapse; }
        .ticket-info td { padding: 8px; border-bottom: 1px solid #eee; }
        .ticket-info td:first-child { font-weight: bold; width: 40%; color: #666; }
        .badge { display: inline-block; padding: 5px 10px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .badge-info { background: #17a2b8; color: white; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin: 0;">Ticket Received</h1>
            <p style="margin: 10px 0 0 0;">Your maintenance request has been received</p>
        </div>

        <div class="content">
            <p>Dear <strong>{{ $ticket->requestor_name }}</strong>,</p>

            <p>Thank you for submitting your maintenance request. Your ticket has been received and is now in our system.</p>

            <div class="ticket-info">
                <h3 style="margin-top: 0;">Ticket Details</h3>
                <table>
                    <tr>
                        <td>Ticket Number</td>
                        <td><strong style="color: #17a2b8;">{{ $ticket->ticket_number }}</strong></td>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <td><span class="badge badge-info">RECEIVED</span></td>
                    </tr>
                    <tr>
                        <td>Problem Category</td>
                        <td>{{ $ticket->getProblemCategoryLabel() }}</td>
                    </tr>
                    <tr>
                        <td>Problem Description</td>
                        <td>{{ $ticket->problem_description }}</td>
                    </tr>
                    <tr>
                        <td>Submitted At</td>
                        <td>{{ $ticket->created_at->format('d M Y H:i') }}</td>
                    </tr>
                </table>
            </div>

            <p>Our maintenance team will review your request and assign a technician shortly. You will receive another email notification when work begins on your ticket.</p>

            <p>If you have any questions, please reference your ticket number: <strong>{{ $ticket->ticket_number }}</strong></p>
        </div>

        <div class="footer">
            <p>This is an automated message from the Warehouse Maintenance System.<br>
            Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
