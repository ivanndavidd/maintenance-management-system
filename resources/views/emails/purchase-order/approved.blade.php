<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Purchase Order Approved</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #22C55E; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f8f9fa; padding: 20px; border: 1px solid #ddd; }
        .po-info { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .po-info table { width: 100%; border-collapse: collapse; }
        .po-info td { padding: 8px; border-bottom: 1px solid #eee; }
        .po-info td:first-child { font-weight: bold; width: 40%; color: #666; }
        .badge { display: inline-block; padding: 5px 10px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .badge-approved { background: #22C55E; color: white; }
        .btn { display: inline-block; padding: 12px 24px; background: #22C55E; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .btn:hover { background: #16A34A; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin: 0;">Purchase Order Approved</h1>
            <p style="margin: 10px 0 0 0;">Your purchase order has been approved</p>
        </div>

        <div class="content">
            <p>Dear <strong>{{ $purchaseOrder->orderedByUser->name ?? 'N/A' }}</strong>,</p>

            <p>Your purchase order has been <strong style="color: #22C55E;">APPROVED</strong> by <strong>{{ $purchaseOrder->approvedByUser->name ?? $purchaseOrder->approver->name ?? 'N/A' }}</strong>.</p>

            <div class="po-info">
                <h3 style="margin-top: 0;">Purchase Order Details</h3>
                <table>
                    <tr>
                        <td>PO Number</td>
                        <td><strong style="color: #22C55E;">{{ $purchaseOrder->po_number }}</strong></td>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <td><span class="badge badge-approved">APPROVED</span></td>
                    </tr>
                    <tr>
                        <td>Supplier</td>
                        <td>{{ $purchaseOrder->supplier }}</td>
                    </tr>
                    <tr>
                        <td>Total Items</td>
                        <td>{{ $purchaseOrder->total_items }} item(s)</td>
                    </tr>
                    <tr>
                        <td>Total Quantity</td>
                        <td>{{ number_format($purchaseOrder->total_quantity) }}</td>
                    </tr>
                    <tr>
                        <td>Total Price</td>
                        <td><strong>Rp {{ number_format($purchaseOrder->total_price, 0, ',', '.') }}</strong></td>
                    </tr>
                    <tr>
                        <td>Order Date</td>
                        <td>{{ \Carbon\Carbon::parse($purchaseOrder->order_date)->format('d M Y') }}</td>
                    </tr>
                    @if($purchaseOrder->expected_delivery_date)
                    <tr>
                        <td>Expected Delivery</td>
                        <td>{{ \Carbon\Carbon::parse($purchaseOrder->expected_delivery_date)->format('d M Y') }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td>Approved At</td>
                        <td>{{ $purchaseOrder->approved_at ? \Carbon\Carbon::parse($purchaseOrder->approved_at)->format('d M Y H:i') : now()->format('d M Y H:i') }}</td>
                    </tr>
                </table>
            </div>

            <p>The purchase order is now active and can proceed to the next steps (goods receiving, quality check, etc.).</p>

            <p style="text-align: center; margin: 25px 0;">
                <a href="{{ url('/supervisor/purchase-orders/' . $purchaseOrder->id) }}" class="btn">
                    View Purchase Order
                </a>
            </p>
        </div>

        <div class="footer">
            <p>This is an automated message from the Warehouse Maintenance System.<br>
            Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
