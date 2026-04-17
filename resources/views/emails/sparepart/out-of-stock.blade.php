<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sparepart Out of Stock</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #EF4444; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f8f9fa; padding: 20px; border: 1px solid #ddd; }
        .info-box { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 8px; border-bottom: 1px solid #eee; }
        td:first-child { font-weight: bold; width: 40%; color: #666; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin:0;">⚠ Sparepart Out of Stock</h1>
            <p style="margin:8px 0 0;">Immediate attention required</p>
        </div>
        <div class="content">
            <p>Dear Supervisor/Admin,</p>
            <p><strong>{{ $reportedBy->name }}</strong> has reported that the following sparepart is <strong style="color:#EF4444;">out of stock</strong>. Please initiate a Purchase Order or update the inventory as soon as possible.</p>

            <div class="info-box">
                <h3 style="margin-top:0;">Sparepart Details</h3>
                <table>
                    <tr><td>Name</td><td><strong>{{ $sparepart->sparepart_name }}</strong></td></tr>
                    <tr><td>Material Code</td><td>{{ $sparepart->material_code ?? '-' }}</td></tr>
                    <tr><td>Equipment Type</td><td>{{ $sparepart->equipment_type ?? '-' }}</td></tr>
                    <tr><td>Current Stock</td><td><strong style="color:#EF4444;">0 {{ $sparepart->unit }}</strong></td></tr>
                    <tr><td>Minimum Stock</td><td>{{ $sparepart->minimum_stock }} {{ $sparepart->unit }}</td></tr>
                    <tr><td>Reported By</td><td>{{ $reportedBy->name }}</td></tr>
                    <tr><td>Reported At</td><td>{{ now()->format('d M Y H:i') }}</td></tr>
                </table>
            </div>

            <p>Please take action to restock this item to avoid disruption to maintenance operations.</p>
        </div>
        <div class="footer">
            <p>This is an automated message from the Warehouse Maintenance System.<br>Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
