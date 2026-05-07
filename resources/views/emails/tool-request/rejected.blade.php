<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tool Usage Request Rejected</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #EF4444; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f8f9fa; padding: 20px; border: 1px solid #ddd; }
        .info { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .info table { width: 100%; border-collapse: collapse; }
        .info td { padding: 8px; border-bottom: 1px solid #eee; }
        .info td:first-child { font-weight: bold; width: 40%; color: #666; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .badge-rejected { background: #EF4444; color: white; }
        .btn { display: inline-block; padding: 12px 24px; background: #6366F1; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        .alert { padding: 12px 15px; border-radius: 5px; margin: 10px 0; font-size: 13px; }
        .alert-danger { background: #FEF2F2; border-left: 4px solid #EF4444; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin:0; font-size:22px;">Request Rejected</h1>
            <p style="margin:8px 0 0 0; opacity:0.9;">Your tool usage request has been rejected</p>
        </div>

        <div class="content">
            <p>Dear <strong>{{ $toolRequest->requester->name ?? 'N/A' }}</strong>,</p>
            <p>Unfortunately, your tool usage request has been <strong style="color:#EF4444;">REJECTED</strong> by <strong>{{ $toolRequest->reviewer->name ?? 'N/A' }}</strong>.</p>

            <div class="info">
                <h3 style="margin-top:0;">Request Details</h3>
                <table>
                    <tr><td>Request No</td><td><strong style="color:#EF4444;">{{ $toolRequest->request_number }}</strong></td></tr>
                    <tr><td>Tool</td><td><strong>{{ $toolRequest->tool->sparepart_name ?? '-' }}</strong></td></tr>
                    <tr><td>Quantity</td><td>{{ $toolRequest->quantity_requested }} {{ $toolRequest->tool->unit ?? '' }}</td></tr>
                    <tr><td>Usage Date</td><td>{{ $toolRequest->usage_date->format('d M Y') }}</td></tr>
                    <tr><td>Purpose</td><td>{{ $toolRequest->purpose }}</td></tr>
                    <tr><td>Status</td><td><span class="badge badge-rejected">REJECTED</span></td></tr>
                    <tr><td>Reviewed By</td><td>{{ $toolRequest->reviewer->name ?? 'N/A' }}</td></tr>
                    <tr><td>Reviewed At</td><td>{{ $toolRequest->reviewed_at->format('d M Y H:i') }}</td></tr>
                </table>
            </div>

            @if($toolRequest->review_notes)
            <div class="alert alert-danger">
                <strong>Reason:</strong> {{ $toolRequest->review_notes }}
            </div>
            @endif

            <p>If you have questions, please contact your supervisor. You may submit a new request if needed.</p>

            <p style="text-align:center; margin:25px 0;">
                <a href="{{ url('/user/tool-requests/' . $toolRequest->id) }}" class="btn">View Request</a>
            </p>
        </div>

        <div class="footer">
            <p>This is an automated message from the Warehouse Maintenance System.<br>Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
