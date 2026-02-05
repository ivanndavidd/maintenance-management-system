<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Stock Adjustment Created</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #F59E0B; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f8f9fa; padding: 20px; border: 1px solid #ddd; }
        .adjustment-info { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .adjustment-info table { width: 100%; border-collapse: collapse; }
        .adjustment-info td { padding: 8px; border-bottom: 1px solid #eee; }
        .adjustment-info td:first-child { font-weight: bold; width: 40%; color: #666; }
        .badge { display: inline-block; padding: 5px 10px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .badge-approved { background: #22C55E; color: white; }
        .badge-add { background: #3B82F6; color: white; }
        .badge-subtract { background: #EF4444; color: white; }
        .badge-correction { background: #F59E0B; color: white; }
        .btn { display: inline-block; padding: 12px 24px; background: #F59E0B; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .btn:hover { background: #D97706; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin: 0;">
                @if($adjustment->status === 'pending')
                    Stock Adjustment Approval Required
                @else
                    Stock Adjustment Created
                @endif
            </h1>
            <p style="margin: 10px 0 0 0;">
                @if($adjustment->status === 'pending')
                    A stock adjustment requires your approval
                @else
                    A stock adjustment has been created and applied
                @endif
            </p>
        </div>

        <div class="content">
            <p>Dear Admin,</p>

            @if($adjustment->status === 'pending')
                <p><strong style="color: #F59E0B;">Action Required:</strong> A stock adjustment has been created by {{ $adjustment->adjustedByUser->name ?? 'N/A' }} and requires your approval before it can be applied to the inventory.</p>
            @else
                <p>A stock adjustment has been created and automatically applied to the inventory. Please review the details below for your records.</p>
            @endif

            <div class="adjustment-info">
                <h3 style="margin-top: 0;">Adjustment Details</h3>
                <table>
                    <tr>
                        <td>Adjustment Code</td>
                        <td><strong style="color: #F59E0B;">{{ $adjustment->adjustment_code }}</strong></td>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <td>
                            @if($adjustment->status === 'pending')
                                <span class="badge" style="background: #EAB308; color: white;">PENDING APPROVAL</span>
                            @else
                                <span class="badge badge-approved">APPROVED</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Item Type</td>
                        <td>{{ ucfirst($adjustment->item_type) }}</td>
                    </tr>
                    <tr>
                        <td>Item Name</td>
                        <td><strong>{{ $itemName ?? 'N/A' }}</strong></td>
                    </tr>
                    <tr>
                        <td>Adjustment Type</td>
                        <td>
                            @if($adjustment->adjustment_type === 'add')
                                <span class="badge badge-add">ADD</span>
                            @elseif($adjustment->adjustment_type === 'subtract')
                                <span class="badge badge-subtract">SUBTRACT</span>
                            @else
                                <span class="badge badge-correction">CORRECTION</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Adjustment Quantity</td>
                        <td><strong style="font-size: 16px;">{{ $adjustment->adjustment_qty > 0 ? '+' : '' }}{{ number_format($adjustment->adjustment_qty) }}</strong></td>
                    </tr>
                    <tr>
                        <td>Quantity Before</td>
                        <td>{{ number_format($adjustment->quantity_before) }}</td>
                    </tr>
                    <tr>
                        <td>Quantity After</td>
                        <td><strong>{{ number_format($adjustment->quantity_after) }}</strong></td>
                    </tr>
                    <tr>
                        <td>Reason Category</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $adjustment->reason_category)) }}</td>
                    </tr>
                    <tr>
                        <td>Reason</td>
                        <td>{{ $adjustment->reason }}</td>
                    </tr>
                    <tr>
                        <td>Adjusted By</td>
                        <td>{{ $adjustment->adjustedByUser->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td>Created At</td>
                        <td>{{ $adjustment->created_at->format('d M Y H:i') }}</td>
                    </tr>
                </table>
            </div>

            <p style="text-align: center; margin: 25px 0;">
                @if($adjustment->status === 'pending')
                    <a href="{{ route($routePrefix.'.adjustments.show', $adjustment->id) }}" class="btn" style="background: #22C55E; margin-right: 10px;">
                        <strong>✓</strong> Approve Adjustment
                    </a>
                    <a href="{{ route($routePrefix.'.adjustments.show', $adjustment->id) }}" class="btn" style="background: #EF4444;">
                        <strong>✗</strong> Reject Adjustment
                    </a>
                @else
                    <a href="{{ route($routePrefix.'.adjustments.show', $adjustment->id) }}" class="btn">
                        View Adjustment Details
                    </a>
                @endif
            </p>

            @if($adjustment->status === 'pending')
                <p><strong>Note:</strong> This adjustment will NOT be applied to the inventory until you approve it. Please review the details carefully before making a decision.</p>

                <p>The current stock quantity will remain unchanged until approval.</p>
            @else
                <p><strong>Note:</strong> This is an informational notification. The adjustment has been automatically approved and applied to the inventory system.</p>

                <p>The stock quantity has been updated in the system. No further action is required unless you need to review the adjustment details.</p>
            @endif
        </div>

        <div class="footer">
            <p>This is an automated message from the Warehouse Maintenance System.<br>
            Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
