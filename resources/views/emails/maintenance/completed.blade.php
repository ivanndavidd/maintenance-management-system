@php
    $isDone = in_array($ticket->status, ['completed', 'done']);
    $isFurtherRepair = $ticket->status === 'further_repair';
    $isFailed = $ticket->status === 'failed';

    if ($isDone) {
        $headerBg = '#28a745';
        $headerTitle = 'Work Completed';
        $headerSub = 'Your maintenance request has been resolved';
        $badgeClass = 'badge-success';
        $badgeText = 'DONE';
        $resolutionBg = '#d4edda';
        $resolutionBorder = '#28a745';
        $resolutionTitle = 'Resolution';
    } elseif ($isFurtherRepair) {
        $headerBg = '#ffc107';
        $headerTitle = 'Further Repair Needed';
        $headerSub = 'Your maintenance request requires additional work';
        $badgeClass = 'badge-warning';
        $badgeText = 'FURTHER REPAIR';
        $resolutionBg = '#fff3cd';
        $resolutionBorder = '#ffc107';
        $resolutionTitle = 'Work Done So Far';
    } else {
        $headerBg = '#dc3545';
        $headerTitle = 'Work Failed';
        $headerSub = 'Your maintenance request could not be completed';
        $badgeClass = 'badge-danger';
        $badgeText = 'FAILED';
        $resolutionBg = '#f8d7da';
        $resolutionBorder = '#dc3545';
        $resolutionTitle = 'Reason';
    }
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Maintenance Request - {{ $headerTitle }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: {{ $headerBg }}; color: {{ $isFurtherRepair ? '#333' : 'white' }}; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f8f9fa; padding: 20px; border: 1px solid #ddd; }
        .ticket-info { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .ticket-info table { width: 100%; border-collapse: collapse; }
        .ticket-info td { padding: 8px; border-bottom: 1px solid #eee; }
        .ticket-info td:first-child { font-weight: bold; width: 40%; color: #666; }
        .badge { display: inline-block; padding: 5px 10px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .badge-success { background: #28a745; color: white; }
        .badge-warning { background: #ffc107; color: #333; }
        .badge-danger { background: #dc3545; color: white; }
        .resolution-box { background: {{ $resolutionBg }}; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid {{ $resolutionBorder }}; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin: 0;">{{ $headerTitle }}</h1>
            <p style="margin: 10px 0 0 0;">{{ $headerSub }}</p>
        </div>

        <div class="content">
            <p>Dear <strong>{{ $ticket->requestor_name }}</strong>,</p>

            @if($isDone)
            <p>We are pleased to inform you that your maintenance request has been successfully completed.</p>
            @elseif($isFurtherRepair)
            <p>We would like to inform you that your maintenance request has been attended to, but further repair is needed. Our team will follow up with additional work.</p>
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
                            <span class="badge {{ $badgeClass }}">{{ $badgeText }}</span>
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
                <h4 style="margin-top: 0;">{{ $resolutionTitle }}</h4>
                <p style="margin-bottom: 0;">{{ $ticket->resolution ?? 'No details provided.' }}</p>
            </div>

            <div class="ticket-info">
                <h4 style="margin-top: 0;">Timeline</h4>
                <table>
                    <tr>
                        <td>Submitted</td>
                        <td>{{ $ticket->created_at->format('d M Y H:i') }}</td>
                    </tr>
                    @if($ticket->in_progress_at)
                    <tr>
                        <td>Work Started</td>
                        <td>{{ $ticket->in_progress_at->format('d M Y H:i') }}</td>
                    </tr>
                    @elseif($ticket->started_at)
                    <tr>
                        <td>Work Started</td>
                        <td>{{ $ticket->started_at->format('d M Y H:i') }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td>{{ $isDone ? 'Completed' : ($isFurtherRepair ? 'Report Submitted' : 'Closed') }}</td>
                        <td>{{ $ticket->report_submitted_at ? $ticket->report_submitted_at->format('d M Y H:i') : ($ticket->completed_at ? $ticket->completed_at->format('d M Y H:i') : now()->format('d M Y H:i')) }}</td>
                    </tr>
                    @if($ticket->work_duration)
                    <tr>
                        <td>Work Duration</td>
                        <td>{{ $ticket->work_duration }}</td>
                    </tr>
                    @endif
                    @if($ticket->technicians->count() > 0)
                    <tr>
                        <td>Technician</td>
                        <td>{{ $ticket->technician_names }}</td>
                    </tr>
                    @elseif($ticket->assignedUser)
                    <tr>
                        <td>Technician</td>
                        <td>{{ $ticket->assignedUser->name }}</td>
                    </tr>
                    @endif
                </table>
            </div>

            @if($isDone)
            <p>If you experience any further issues with this equipment, please submit a new maintenance request.</p>
            @elseif($isFurtherRepair)
            <p>Our team will schedule a follow-up repair. You will be notified when additional work is completed.</p>
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
