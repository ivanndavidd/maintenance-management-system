<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Maintenance Request {{ $ticket->status === 'completed' ? 'Completed' : 'Failed' }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: {{ $ticket->status === 'completed' ? '#28a745' : '#dc3545' }}; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f8f9fa; padding: 20px; border: 1px solid #ddd; }
        .ticket-info { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .ticket-info table { width: 100%; border-collapse: collapse; }
        .ticket-info td { padding: 8px; border-bottom: 1px solid #eee; }
        .ticket-info td:first-child { font-weight: bold; width: 40%; color: #666; }
        .badge { display: inline-block; padding: 5px 10px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .badge-success { background: #28a745; color: white; }
        .badge-danger { background: #dc3545; color: white; }
        .resolution-box { background: {{ $ticket->status === 'completed' ? '#d4edda' : '#f8d7da' }}; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid {{ $ticket->status === 'completed' ? '#28a745' : '#dc3545' }}; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        .timeline { margin: 15px 0; }
        .timeline-item { padding: 10px 0; border-left: 2px solid #ddd; padding-left: 15px; margin-left: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin: 0;">{{ $ticket->status === 'completed' ? 'Work Completed' : 'Work Failed' }}</h1>
            <p style="margin: 10px 0 0 0;">Your maintenance request has been {{ $ticket->status === 'completed' ? 'resolved' : 'closed' }}</p>
        </div>

        <div class="content">
            <p>Dear <strong>{{ $ticket->requestor_name }}</strong>,</p>

            @if($ticket->status === 'completed')
            <p>We are pleased to inform you that your maintenance request has been successfully completed.</p>
            @else
            <p>We regret to inform you that your maintenance request could not be completed successfully.</p>
            @endif

            <div class="ticket-info">
                <h3 style="margin-top: 0;">Ticket Details</h3>
                <table>
                    <tr>
                        <td>Ticket Number</td>
                        <td><strong>{{ $ticket->ticket_number }}</strong></td>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <td>
                            @if($ticket->status === 'completed')
                                <span class="badge badge-success">COMPLETED</span>
                            @else
                                <span class="badge badge-danger">FAILED</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Problem Category</td>
                        <td>{{ $ticket->getProblemCategoryLabel() }}</td>
                    </tr>
                    <tr>
                        <td>Problem Description</td>
                        <td>{{ $ticket->problem_description }}</td>
                    </tr>
                </table>
            </div>

            <div class="resolution-box">
                <h4 style="margin-top: 0;">{{ $ticket->status === 'completed' ? 'Resolution' : 'Reason' }}</h4>
                <p style="margin-bottom: 0;">{{ $ticket->resolution ?? 'No details provided.' }}</p>
            </div>

            <div class="ticket-info">
                <h4 style="margin-top: 0;">Timeline</h4>
                <table>
                    <tr>
                        <td>Submitted</td>
                        <td>{{ $ticket->created_at->format('d M Y H:i') }}</td>
                    </tr>
                    @if($ticket->started_at)
                    <tr>
                        <td>Work Started</td>
                        <td>{{ $ticket->started_at->format('d M Y H:i') }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td>{{ $ticket->status === 'completed' ? 'Completed' : 'Closed' }}</td>
                        <td>{{ $ticket->completed_at ? $ticket->completed_at->format('d M Y H:i') : now()->format('d M Y H:i') }}</td>
                    </tr>
                    @if($ticket->assignedUser)
                    <tr>
                        <td>Technician</td>
                        <td>{{ $ticket->assignedUser->name }}</td>
                    </tr>
                    @endif
                </table>
            </div>

            @if($ticket->status === 'completed')
            <p>If you experience any further issues with this equipment, please submit a new maintenance request.</p>
            @else
            <p>If you have any questions or would like to discuss alternative solutions, please contact our maintenance team.</p>
            @endif

            <p>Thank you for your patience.</p>
        </div>

        <div class="footer">
            <p>This is an automated message from the Warehouse Maintenance System.<br>
            Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
