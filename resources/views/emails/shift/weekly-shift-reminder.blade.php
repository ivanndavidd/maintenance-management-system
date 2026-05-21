<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Shift Schedule Reminder</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: {{ $isReminder ? '#EF4444' : '#3B82F6' }}; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f8f9fa; padding: 20px; border: 1px solid #ddd; }
        .alert-box { background: {{ $isReminder ? '#FEF2F2' : '#EFF6FF' }}; border: 1px solid {{ $isReminder ? '#EF4444' : '#3B82F6' }}; border-radius: 5px; padding: 12px; margin: 15px 0; }
        .info-box { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid {{ $isReminder ? '#EF4444' : '#3B82F6' }}; }
        .btn { display: inline-block; padding: 12px 24px; background: {{ $isReminder ? '#EF4444' : '#3B82F6' }}; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        .stat { display: inline-block; background: white; padding: 10px 20px; border-radius: 5px; margin: 5px; text-align: center; border: 1px solid #ddd; }
        .stat-number { font-size: 20px; font-weight: bold; color: {{ $isReminder ? '#EF4444' : '#3B82F6' }}; }
        .stat-label { font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin: 0;">
                {{ $isReminder ? '⚠ Reminder: ' : '' }}Shift Schedule Minggu Depan
            </h1>
            <p style="margin: 10px 0 0 0;">{{ $siteName }}</p>
        </div>

        <div class="content">
            <div class="alert-box">
                @if($isReminder)
                    <strong>Reminder:</strong> Shift schedule untuk minggu depan <strong>belum dibuat</strong>. Segera buat shift schedule agar tugas PM dapat berjalan dengan lancar.
                @else
                    <strong>Perhatian:</strong> Shift schedule untuk minggu depan belum ditemukan di sistem. Harap segera buat shift schedule sebelum minggu baru dimulai.
                @endif
            </div>

            <div style="text-align: center; margin: 20px 0;">
                <div class="stat">
                    <div class="stat-number">{{ $nextWeekStart->format('d M') }}</div>
                    <div class="stat-label">Mulai</div>
                </div>
                <div class="stat">
                    <div class="stat-number">{{ $nextWeekEnd->format('d M') }}</div>
                    <div class="stat-label">Selesai</div>
                </div>
                <div class="stat">
                    <div class="stat-number">{{ $nextWeekStart->format('Y') }}</div>
                    <div class="stat-label">Tahun</div>
                </div>
            </div>

            <div class="info-box">
                <h3 style="margin-top: 0;">Periode Minggu Depan</h3>
                <p style="margin: 0;">
                    <strong>{{ $nextWeekStart->translatedFormat('l, d F Y') }}</strong>
                    &mdash;
                    <strong>{{ $nextWeekEnd->translatedFormat('l, d F Y') }}</strong>
                </p>
            </div>

            <p style="text-align: center; margin: 25px 0;">
                <a href="{{ url('/admin/shift-schedules/create') }}" class="btn">
                    Buat Shift Schedule
                </a>
            </p>

            <p><strong>Yang perlu dilakukan:</strong></p>
            <ol>
                <li>Login ke sistem Warehouse Maintenance</li>
                <li>Buka menu <strong>Shift Management</strong></li>
                <li>Buat shift schedule baru untuk periode <strong>{{ $nextWeekStart->format('d M') }} - {{ $nextWeekEnd->format('d M Y') }}</strong></li>
                <li>Assign anggota ke setiap shift</li>
                <li>Aktifkan shift schedule</li>
            </ol>
        </div>

        <div class="footer">
            <p>Email ini dikirim otomatis oleh sistem Warehouse Maintenance.<br>
            Jangan membalas email ini.</p>
        </div>
    </div>
</body>
</html>
