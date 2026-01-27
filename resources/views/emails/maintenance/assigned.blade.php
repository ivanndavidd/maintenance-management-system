<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New Maintenance Assignment</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #ffc107; color: #333; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f8f9fa; padding: 20px; border: 1px solid #ddd; }
        .ticket-info { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .ticket-info table { width: 100%; border-collapse: collapse; }
        .ticket-info td { padding: 8px; border-bottom: 1px solid #eee; }
        .ticket-info td:first-child { font-weight: bold; width: 40%; color: #666; }
        .badge { display: inline-block; padding: 5px 10px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .badge-warning { background: #ffc107; color: #333; }
        .badge-danger { background: #dc3545; color: white; }
        .badge-high { background: #fd7e14; color: white; }
        .priority-box { padding: 15px; border-radius: 5px; margin: 15px 0; }
        .priority-critical { background: #f8d7da; border-left: 4px solid #dc3545; }
        .priority-high { background: #fff3cd; border-left: 4px solid #ffc107; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin: 0;">New Assignment</h1>
            <p style="margin: 10px 0 0 0;">You have been assigned a new maintenance request</p>
        </div>

        <div class="content">
            <p>Dear <strong>{{ $technician->name ?? 'Technician' }}</strong>,</p>

            <p>You have been assigned to handle a new corrective maintenance request based on your current shift assignment{{ $shiftInfo ? ' ('.$shiftInfo.')' : '' }}. Please review the details below and begin work as soon as possible.</p>

            <div class="ticket-info">
                <h3 style="margin-top: 0;">Ticket Details</h3>
                <table>
                    <tr>
                        <td>Ticket Number</td>
                        <td><strong>{{ $ticket->ticket_number }}</strong></td>
                    </tr>
                    <tr>
                        <td>Problem Category</td>
                        <td><strong>{{ $ticket->getProblemCategoryLabel() }}</strong></td>
                    </tr>
                    <tr>
                        <td>Problem Description</td>
                        <td>{{ $ticket->problem_description }}</td>
                    </tr>
                </table>
            </div>

            <div class="ticket-info">
                <h3 style="margin-top: 0;">Requestor Information</h3>
                <table>
                    <tr>
                        <td>Name</td>
                        <td>{{ $ticket->requestor_name }}</td>
                    </tr>
                    <tr>
                        <td>Email</td>
                        <td>{{ $ticket->requestor_email }}</td>
                    </tr>
                </table>
            </div>

            <p>Please update the ticket status in the system once you begin working on it and when you complete the task.</p>
        </div>

        <div class="footer">
            <p>This is an automated message from the Warehouse Maintenance System.<br>
            Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
