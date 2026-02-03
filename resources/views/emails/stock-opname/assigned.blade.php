<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Stock Opname Assigned</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #8B5CF6; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f8f9fa; padding: 20px; border: 1px solid #ddd; }
        .schedule-info { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .schedule-info table { width: 100%; border-collapse: collapse; }
        .schedule-info td { padding: 8px; border-bottom: 1px solid #eee; }
        .schedule-info td:first-child { font-weight: bold; width: 40%; color: #666; }
        .badge { display: inline-block; padding: 5px 10px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .badge-active { background: #8B5CF6; color: white; }
        .btn { display: inline-block; padding: 12px 24px; background: #8B5CF6; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .btn:hover { background: #7C3AED; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin: 0;">Stock Opname Assignment</h1>
            <p style="margin: 10px 0 0 0;">You have been assigned to a stock opname schedule</p>
        </div>

        <div class="content">
            <p>Dear <strong>{{ $user->name }}</strong>,</p>

            <p>You have been assigned to perform a stock opname. Please review the schedule details below and complete the stock counting on the scheduled date.</p>

            <div class="schedule-info">
                <h3 style="margin-top: 0;">Schedule Details</h3>
                <table>
                    <tr>
                        <td>Schedule Code</td>
                        <td><strong style="color: #8B5CF6;">{{ $schedule->schedule_code }}</strong></td>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <td><span class="badge badge-active">{{ strtoupper($schedule->status) }}</span></td>
                    </tr>
                    <tr>
                        <td>Execution Date</td>
                        <td><strong>{{ \Carbon\Carbon::parse($schedule->execution_date)->format('d M Y') }}</strong></td>
                    </tr>
                    <tr>
                        <td>Total Items</td>
                        <td>{{ number_format($schedule->total_items) }} item(s)</td>
                    </tr>
                    <tr>
                        <td>Items to Count</td>
                        <td>
                            @if($schedule->include_spareparts) <span class="badge" style="background: #3B82F6; color: white;">Spareparts</span> @endif
                            @if($schedule->include_tools) <span class="badge" style="background: #EAB308; color: white;">Tools</span> @endif
                            @if($schedule->include_assets) <span class="badge" style="background: #22C55E; color: white;">Assets</span> @endif
                        </td>
                    </tr>
                    @if($schedule->sparepart_locations)
                    <tr>
                        <td>Sparepart Locations</td>
                        <td>{{ $schedule->sparepart_locations }}</td>
                    </tr>
                    @endif
                    @if($schedule->asset_locations)
                    <tr>
                        <td>Asset Locations</td>
                        <td>{{ $schedule->asset_locations }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td>Created By</td>
                        <td>{{ $schedule->createdByUser->name ?? 'N/A' }}</td>
                    </tr>
                    @if($schedule->notes)
                    <tr>
                        <td>Notes</td>
                        <td>{{ $schedule->notes }}</td>
                    </tr>
                    @endif
                </table>
            </div>

            <p style="text-align: center; margin: 25px 0;">
                <a href="{{ route('supervisor.my-tasks.stock-opname.show', $schedule->id) }}" class="btn">
                    View Stock Opname Details
                </a>
            </p>

            <p><strong>Important Instructions:</strong></p>
            <ul>
                <li>Complete the stock counting on the scheduled execution date</li>
                <li>Count each item carefully and accurately</li>
                <li>Record the physical quantity for each item</li>
                <li>Add notes for any discrepancies or issues found</li>
                <li>Submit your counts once completed</li>
            </ul>

            <p>You can export the stock opname template from the detail page to perform offline counting if needed.</p>

            <p>If you have any questions or need assistance, please contact your supervisor.</p>
        </div>

        <div class="footer">
            <p>This is an automated message from the Warehouse Maintenance System.<br>
            Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
