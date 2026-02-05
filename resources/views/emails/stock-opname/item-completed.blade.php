<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Stock Opname Review Required</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #EF4444; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f8f9fa; padding: 20px; border: 1px solid #ddd; }
        .schedule-info { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .schedule-info table { width: 100%; border-collapse: collapse; }
        .schedule-info td { padding: 8px; border-bottom: 1px solid #eee; }
        .schedule-info td:first-child { font-weight: bold; width: 40%; color: #666; }
        .badge { display: inline-block; padding: 5px 10px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .badge-review { background: #EF4444; color: white; }
        .btn { display: inline-block; padding: 12px 24px; background: #EF4444; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .btn:hover { background: #DC2626; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        .discrepancy-list { background: #FEF2F2; border-left: 4px solid #EF4444; padding: 15px; margin: 15px 0; }
        .discrepancy-item { padding: 8px 0; border-bottom: 1px solid #FCA5A5; }
        .discrepancy-item:last-child { border-bottom: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin: 0;">Stock Opname Review Required</h1>
            <p style="margin: 10px 0 0 0;">Discrepancies detected in stock counting</p>
        </div>

        <div class="content">
            <p>Dear Supervisor/Admin,</p>

            <p>Stock opname items with discrepancies have been submitted and require your review and approval.</p>

            <div class="schedule-info">
                <h3 style="margin-top: 0;">Schedule Details</h3>
                <table>
                    <tr>
                        <td>Schedule Code</td>
                        <td><strong style="color: #EF4444;">{{ $schedule->schedule_code }}</strong></td>
                    </tr>
                    <tr>
                        <td>Execution Date</td>
                        <td>{{ \Carbon\Carbon::parse($schedule->execution_date)->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td>Items Needing Review</td>
                        <td><span class="badge badge-review">{{ count($itemsWithDiscrepancy) }} ITEMS</span></td>
                    </tr>
                    <tr>
                        <td>Progress</td>
                        <td>{{ $schedule->completed_items }} / {{ $schedule->total_items }} items completed</td>
                    </tr>
                </table>
            </div>

            <div class="discrepancy-list">
                <h4 style="margin-top: 0; color: #EF4444;">Items with Discrepancies (First 10)</h4>
                @foreach(array_slice($itemsWithDiscrepancy, 0, 10) as $item)
                <div class="discrepancy-item">
                    <strong>{{ $item->getItemName() }}</strong><br>
                    <small style="color: #666;">
                        Expected: {{ number_format($item->expected_quantity) }} |
                        Actual: {{ number_format($item->physical_quantity) }} |
                        Discrepancy: <span style="color: #EF4444; font-weight: bold;">{{ $item->discrepancy > 0 ? '+' : '' }}{{ number_format($item->discrepancy) }}</span>
                    </small>
                    @if($item->notes)
                    <br><small style="color: #666; font-style: italic;">Note: {{ $item->notes }}</small>
                    @endif
                </div>
                @endforeach
                @if(count($itemsWithDiscrepancy) > 10)
                <div style="margin-top: 10px; text-align: center; color: #666;">
                    <em>... and {{ count($itemsWithDiscrepancy) - 10 }} more items</em>
                </div>
                @endif
            </div>

            <p style="text-align: center; margin: 25px 0;">
                <a href="{{ route($routePrefix.'.opname.schedules.show', $schedule->id) }}" class="btn">
                    Review & Approve Items
                </a>
            </p>

            <p><strong>Action Required:</strong></p>
            <ul>
                <li>Review each item with discrepancies</li>
                <li>Verify the physical quantity counts</li>
                <li>Approve or reject the discrepancies</li>
                <li>Add comments if needed for rejected items</li>
            </ul>

            <p>Please review these items promptly to ensure accurate inventory records.</p>
        </div>

        <div class="footer">
            <p>This is an automated message from the Warehouse Maintenance System.<br>
            Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
