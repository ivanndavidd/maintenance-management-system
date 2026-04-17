<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sparepart Used in CM Ticket</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #3B82F6; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f8f9fa; padding: 20px; border: 1px solid #ddd; }
        .info-box { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f1f5f9; padding: 8px; text-align: left; border-bottom: 2px solid #e2e8f0; font-size: 13px; color: #555; }
        td { padding: 8px; border-bottom: 1px solid #eee; font-size: 14px; }
        td:first-child { font-weight: bold; width: 40%; color: #666; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin:0;">Sparepart Usage Notification</h1>
            <p style="margin:8px 0 0;">Sparepart has been used in a CM ticket</p>
        </div>
        <div class="content">
            <p>Dear Supervisor/Admin,</p>
            <p>The following sparepart(s) have been recorded as used during the resolution of corrective maintenance ticket <strong>{{ $ticketNumber }}</strong>.</p>

            <div class="info-box">
                <h3 style="margin-top:0;">Ticket Information</h3>
                <table>
                    <tr><td>Ticket Number</td><td><strong style="color:#3B82F6;">{{ $ticketNumber }}</strong></td></tr>
                    <tr><td>Asset</td><td>{{ $report->asset->asset_name ?? '-' }}</td></tr>
                    <tr><td>Submitted By</td><td>{{ $submitterName }}</td></tr>
                    <tr><td>Date</td><td>{{ now()->format('d M Y H:i') }}</td></tr>
                </table>
            </div>

            <div class="info-box">
                <h3 style="margin-top:0;">Spareparts Used</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Sparepart</th>
                            <th>Material Code</th>
                            <th>Qty Used</th>
                            <th>Remaining Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($usages as $usage)
                        <tr>
                            <td style="font-weight:normal;">{{ $usage['name'] }}</td>
                            <td style="font-weight:normal;">{{ $usage['material_code'] ?? '-' }}</td>
                            <td style="font-weight:normal;">{{ $usage['qty'] }} {{ $usage['unit'] }}</td>
                            <td style="font-weight:normal; {{ $usage['remaining'] <= $usage['minimum_stock'] ? 'color:#EF4444;font-weight:bold;' : '' }}">
                                {{ $usage['remaining'] }} {{ $usage['unit'] }}
                                @if($usage['remaining'] <= $usage['minimum_stock'])
                                    ⚠ Low Stock
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p>Please review the inventory status and initiate a Purchase Order if restocking is required.</p>
        </div>
        <div class="footer">
            <p>This is an automated message from the Warehouse Maintenance System.<br>Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
