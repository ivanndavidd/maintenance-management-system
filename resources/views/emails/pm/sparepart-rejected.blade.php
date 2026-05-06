<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Penggunaan Sparepart Ditolak</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #EF4444; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f8f9fa; padding: 20px; border: 1px solid #ddd; }
        .info-box { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .info-box table { width: 100%; border-collapse: collapse; }
        .info-box td { padding: 8px; border-bottom: 1px solid #eee; }
        .info-box td:first-child { font-weight: bold; width: 40%; color: #666; }
        .reason-box { background: #FEF2F2; border-left: 4px solid #EF4444; padding: 15px; border-radius: 0 5px 5px 0; margin: 15px 0; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin:0;">Penggunaan Sparepart Ditolak</h1>
            <p style="margin:10px 0 0 0;">Report PM Anda memerlukan tindak lanjut</p>
        </div>

        <div class="content">
            <p>Kepada <strong>{{ $report->submitter->name ?? 'Yth.' }}</strong>,</p>

            <p>Penggunaan sparepart pada report PM Anda telah <strong style="color:#EF4444;">ditolak</strong> oleh supervisor/admin. Silakan submit ulang report tanpa sparepart atau dengan sparepart yang berbeda.</p>

            <div class="info-box">
                <h3 style="margin-top:0;">Detail Task</h3>
                <table>
                    <tr>
                        <td>Nama Task</td>
                        <td><strong>{{ $report->task->task_name }}</strong></td>
                    </tr>
                    <tr>
                        <td>Tanggal Task</td>
                        <td>{{ \Carbon\Carbon::parse($report->task->task_date)->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td>Shift</td>
                        <td>Shift {{ $report->task->assigned_shift_id ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Ditolak Oleh</td>
                        <td>{{ $report->sparepartApprover->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Waktu</td>
                        <td>{{ $report->sparepart_approved_at?->format('d M Y, H:i') }}</td>
                    </tr>
                </table>
            </div>

            <div class="reason-box">
                <strong>Alasan Penolakan:</strong><br>
                {{ $reason }}
            </div>

            <p>Silakan login ke sistem untuk melihat detail dan melakukan resubmit report.</p>
        </div>

        <div class="footer">
            <p>Pesan otomatis dari Warehouse Maintenance System.<br>Jangan balas email ini.</p>
        </div>
    </div>
</body>
</html>
