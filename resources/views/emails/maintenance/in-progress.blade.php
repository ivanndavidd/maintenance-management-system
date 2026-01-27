<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Maintenance Request In Progress</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #007bff; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f8f9fa; padding: 20px; border: 1px solid #ddd; }
        .ticket-info { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .ticket-info table { width: 100%; border-collapse: collapse; }
        .ticket-info td { padding: 8px; border-bottom: 1px solid #eee; }
        .ticket-info td:first-child { font-weight: bold; width: 40%; color: #666; }
        .badge { display: inline-block; padding: 5px 10px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .badge-primary { background: #007bff; color: white; }
        .technician-box { background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #007bff; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin: 0;">Work In Progress</h1>
            <p style="margin: 10px 0 0 0;">A technician is now working on your request</p>
        </div>

        <div class="content">
            <p>Dear <strong>{{ $ticket->requestor_name }}</strong>,</p>

            <p>Good news! Your maintenance request is now being worked on by our maintenance team.</p>

            <div class="ticket-info">
                <h3 style="margin-top: 0;">Ticket Details</h3>
                <table>
                    <tr>
                        <td>Ticket Number</td>
                        <td><strong style="color: #007bff;">{{ $ticket->ticket_number }}</strong></td>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <td><span class="badge badge-primary">IN PROGRESS</span></td>
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
                        <td>Work Started At</td>
                        <td>{{ $ticket->started_at ? $ticket->started_at->format('d M Y H:i') : '-' }}</td>
                    </tr>
                </table>
            </div>

            @if($ticket->assignedUser)
            <div class="technician-box">
                <h4 style="margin-top: 0;"><i>Assigned Technician</i></h4>
                <p style="margin-bottom: 0;">
                    <strong>{{ $ticket->assignedUser->name }}</strong><br>
                    <small>{{ $ticket->assignedUser->email }}</small>
                </p>
            </div>
            @endif

            <p>You will receive another notification once the work is completed.</p>
        </div>

        <div class="footer">
            <p>This is an automated message from the Warehouse Maintenance System.<br>
            Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
