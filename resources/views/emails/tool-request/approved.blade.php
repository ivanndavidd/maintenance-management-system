<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tool Usage Request Approved</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #22C55E; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f8f9fa; padding: 20px; border: 1px solid #ddd; }
        .info { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .info table { width: 100%; border-collapse: collapse; }
        .info td { padding: 8px; border-bottom: 1px solid #eee; }
        .info td:first-child { font-weight: bold; width: 40%; color: #666; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .badge-approved { background: #22C55E; color: white; }
        .badge-consumable { background: #06B6D4; color: white; }
        .badge-borrow { background: #6366F1; color: white; }
        .btn { display: inline-block; padding: 12px 24px; background: #22C55E; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        .alert { padding: 12px 15px; border-radius: 5px; margin: 10px 0; font-size: 13px; }
        .alert-success { background: #F0FDF4; border-left: 4px solid #22C55E; }
        .alert-info { background: #EFF6FF; border-left: 4px solid #3B82F6; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin:0; font-size:22px;">Request Approved ✓</h1>
            <p style="margin:8px 0 0 0; opacity:0.9;">Your tool usage request has been approved</p>
        </div>

        <div class="content">
            <p>Dear <strong>{{ $toolRequest->requester->name ?? 'N/A' }}</strong>,</p>
            <p>Your tool usage request has been <strong style="color:#22C55E;">APPROVED</strong> by <strong>{{ $toolRequest->reviewer->name ?? 'N/A' }}</strong>.</p>

            <div class="info">
                <h3 style="margin-top:0;">Request Details</h3>
                <table>
                    <tr><td>Request No</td><td><strong style="color:#22C55E;">{{ $toolRequest->request_number }}</strong></td></tr>
                    <tr><td>Tool</td><td><strong>{{ $toolRequest->tool->sparepart_name ?? '-' }}</strong></td></tr>
                    <tr><td>Type</td><td>
                        @if($toolRequest->isConsumable())
                            <span class="badge badge-consumable">Consumable</span>
                        @else
                            <span class="badge badge-borrow">Borrowing</span>
                        @endif
                    </td></tr>
                    <tr><td>Quantity</td><td>{{ $toolRequest->quantity_requested }} {{ $toolRequest->tool->unit ?? '' }}</td></tr>
                    <tr><td>Usage Date</td><td>{{ $toolRequest->usage_date->format('d M Y') }}</td></tr>
                    @if(!$toolRequest->isConsumable() && $toolRequest->return_date)
                    <tr><td>Return Date</td><td>{{ $toolRequest->return_date->format('d M Y') }}</td></tr>
                    @endif
                    <tr><td>Status</td><td><span class="badge badge-approved">APPROVED</span></td></tr>
                    <tr><td>Approved By</td><td>{{ $toolRequest->reviewer->name ?? 'N/A' }}</td></tr>
                    <tr><td>Approved At</td><td>{{ $toolRequest->reviewed_at->format('d M Y H:i') }}</td></tr>
                    @if($toolRequest->review_notes)
                    <tr><td>Notes</td><td>{{ $toolRequest->review_notes }}</td></tr>
                    @endif
                </table>
            </div>

            @if($toolRequest->isConsumable())
            <div class="alert alert-success">
                <strong>Stock has been deducted.</strong> You may now proceed to take the item from the warehouse.
            </div>
            @else
            <div class="alert alert-info">
                <strong>Next step:</strong> Pick up the tool from the warehouse and mark it as <strong>In Use</strong> in the system when you start using it.
                @if($toolRequest->return_date)
                    Please return by <strong>{{ $toolRequest->return_date->format('d M Y') }}</strong>.
                @endif
            </div>
            @endif

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
