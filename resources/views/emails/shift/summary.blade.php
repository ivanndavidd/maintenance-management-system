<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Shift Summary</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
        .container { max-width: 680px; margin: 0 auto; padding: 20px; }
        .header { background: #1e40af; color: white; padding: 24px 20px; border-radius: 8px 8px 0 0; }
        .header h1 { margin: 0 0 4px 0; font-size: 22px; }
        .header p { margin: 0; opacity: 0.85; font-size: 14px; }
        .content { background: #f8fafc; padding: 20px; border: 1px solid #e2e8f0; border-top: none; border-radius: 0 0 8px 8px; }

        .section { background: white; border: 1px solid #e2e8f0; border-radius: 6px; margin-bottom: 16px; overflow: hidden; }
        .section-header { padding: 12px 16px; font-weight: bold; font-size: 14px; display: flex; align-items: center; gap: 8px; }
        .section-header.blue  { background: #eff6ff; color: #1d4ed8; border-bottom: 1px solid #bfdbfe; }
        .section-header.green { background: #f0fdf4; color: #15803d; border-bottom: 1px solid #bbf7d0; }
        .section-header.red   { background: #fef2f2; color: #b91c1c; border-bottom: 1px solid #fecaca; }
        .section-header.gray  { background: #f8fafc; color: #475569; border-bottom: 1px solid #e2e8f0; }

        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th { background: #f1f5f9; padding: 8px 12px; text-align: left; color: #475569; font-weight: 600; border-bottom: 1px solid #e2e8f0; }
        td { padding: 8px 12px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
        tr:last-child td { border-bottom: none; }

        .badge { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; }
        .badge-pending    { background: #fef9c3; color: #854d0e; }
        .badge-progress   { background: #dbeafe; color: #1e40af; }
        .badge-completed  { background: #dcfce7; color: #166534; }
        .badge-late       { background: #fee2e2; color: #991b1b; }
        .badge-active     { background: #e0f2fe; color: #0369a1; }
        .badge-shift      { background: #1e40af; color: white; }

        .user-chip { display: inline-block; background: #ede9fe; color: #5b21b6; border-radius: 20px; padding: 3px 10px; font-size: 12px; margin: 2px; }
        .empty-msg { padding: 14px 16px; color: #94a3b8; font-size: 13px; font-style: italic; }
        .footer { text-align: center; padding: 16px; color: #94a3b8; font-size: 12px; }
        .progress-bar-wrap { background: #e2e8f0; border-radius: 4px; height: 6px; width: 100px; display: inline-block; vertical-align: middle; }
        .progress-bar-fill { background: #22c55e; border-radius: 4px; height: 6px; }
        .overdue-label { color: #b91c1c; font-size: 11px; font-weight: 600; }
    </style>
</head>
<body>
<div class="container">

    {{-- Header --}}
    <div class="header">
        <h1>📋 Shift Summary — {{ $payload['shiftLabel'] }}</h1>
        <p>
            {{ $payload['taskDate']->format('l, d F Y') }} &nbsp;|&nbsp;
            {{ $payload['shiftTime'] }} &nbsp;|&nbsp;
            {{ $payload['siteName'] }}
        </p>
    </div>

    <div class="content">
        <p style="margin-top:0;">Dear <strong>{{ $recipient->name }}</strong>,</p>
        <p style="margin-top:0; color:#475569; font-size:13px;">
            Berikut adalah ringkasan tugas dan informasi untuk <strong>{{ $payload['shiftLabel'] }} ({{ $payload['shiftTime'] }})</strong>
            tanggal <strong>{{ $payload['taskDate']->format('d M Y') }}</strong>.
        </p>

        {{-- Duty Users --}}
        <div class="section">
            <div class="section-header green">
                👷 Personil Duty — {{ $payload['shiftLabel'] }}
            </div>
            @if($payload['dutyUsers']->isNotEmpty())
                <div style="padding: 12px 16px;">
                    @foreach($payload['dutyUsers'] as $user)
                        <span class="user-chip">{{ $user->name }}</span>
                    @endforeach
                </div>
            @else
                <div class="empty-msg">Tidak ada personil terjadwal untuk shift ini.</div>
            @endif
        </div>

        {{-- PM Tasks --}}
        <div class="section">
            <div class="section-header blue">
                🔧 PM Tasks — {{ $payload['shiftLabel'] }} ({{ $payload['pmTasks']->count() }} task)
            </div>
            @if($payload['pmTasks']->isNotEmpty())
                <table>
                    <thead>
                        <tr>
                            <th>Task Name</th>
                            <th>Equipment</th>
                            <th>Status Task</th>
                            <th>Status Report</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payload['pmTasks'] as $task)
                        @php
                            $statusMap = [
                                'pending'     => ['label' => 'Pending',      'class' => 'badge-pending'],
                                'in_progress' => ['label' => 'In Progress',  'class' => 'badge-progress'],
                                'completed'   => ['label' => 'Completed',    'class' => 'badge-completed'],
                            ];
                            $s = $statusMap[$task->status] ?? ['label' => ucfirst($task->status), 'class' => 'badge-pending'];
                            $report = $task->latestReport;
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $task->task_name }}</strong>
                                @if($task->task_description)
                                    <br><span style="color:#94a3b8; font-size:11px;">{{ Str::limit($task->task_description, 60) }}</span>
                                @endif
                            </td>
                            <td>{{ $task->equipment_type ?? '-' }}</td>
                            <td><span class="badge {{ $s['class'] }}">{{ $s['label'] }}</span></td>
                            <td>
                                @if($report)
                                    <span class="badge badge-active">{{ $report->getStatusLabel() }}</span>
                                @else
                                    <span style="color:#94a3b8; font-size:11px;">No report</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-msg">Tidak ada PM task untuk shift ini.</div>
            @endif
        </div>

        {{-- Stock Opname Active --}}
        <div class="section">
            <div class="section-header gray">
                📦 Stock Opname Aktif ({{ $payload['stockOpnameOngoing']->count() }} jadwal)
            </div>
            @if($payload['stockOpnameOngoing']->isNotEmpty())
                <table>
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Tanggal Eksekusi</th>
                            <th>Progress</th>
                            <th>Responsible</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payload['stockOpnameOngoing'] as $so)
                        @php
                            $pct = $so->getProgressPercentage();
                            $daysLeft = $so->getDaysRemaining();
                        @endphp
                        <tr>
                            <td><strong>{{ $so->schedule_code }}</strong></td>
                            <td>
                                {{ $so->execution_date->format('d M Y') }}
                                @if($daysLeft > 0)
                                    <br><span style="color:#0369a1; font-size:11px;">{{ $daysLeft }} hari lagi</span>
                                @elseif($daysLeft === 0)
                                    <br><span style="color:#15803d; font-size:11px; font-weight:600;">Hari ini</span>
                                @endif
                            </td>
                            <td>
                                <div class="progress-bar-wrap">
                                    <div class="progress-bar-fill" style="width:{{ $pct }}%;"></div>
                                </div>
                                <span style="font-size:11px; color:#475569; margin-left:6px;">{{ $pct }}%</span>
                                <br><span style="font-size:11px; color:#94a3b8;">{{ $so->completed_items }}/{{ $so->total_items }} item</span>
                            </td>
                            <td>
                                @forelse($so->assignedUsers as $u)
                                    <span style="font-size:12px;">{{ $u->name }}</span><br>
                                @empty
                                    <span style="color:#94a3b8; font-size:11px;">-</span>
                                @endforelse
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-msg">Tidak ada stock opname aktif.</div>
            @endif
        </div>

        {{-- Stock Opname Late --}}
        @if($payload['stockOpnameLate']->isNotEmpty())
        <div class="section">
            <div class="section-header red">
                ⚠️ Stock Opname Overdue ({{ $payload['stockOpnameLate']->count() }} jadwal)
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Tanggal Eksekusi</th>
                        <th>Telat</th>
                        <th>Progress</th>
                        <th>Responsible</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payload['stockOpnameLate'] as $so)
                    @php
                        $pct      = $so->getProgressPercentage();
                        $daysLate = abs($so->getDaysRemaining());
                    @endphp
                    <tr>
                        <td><strong>{{ $so->schedule_code }}</strong></td>
                        <td>{{ $so->execution_date->format('d M Y') }}</td>
                        <td><span class="overdue-label">{{ $daysLate }} hari</span></td>
                        <td>
                            <div class="progress-bar-wrap">
                                <div class="progress-bar-fill" style="width:{{ $pct }}%; background:#ef4444;"></div>
                            </div>
                            <span style="font-size:11px; color:#475569; margin-left:6px;">{{ $pct }}%</span>
                            <br><span style="font-size:11px; color:#94a3b8;">{{ $so->completed_items }}/{{ $so->total_items }} item</span>
                        </td>
                        <td>
                            @forelse($so->assignedUsers as $u)
                                <span style="font-size:12px;">{{ $u->name }}</span><br>
                            @empty
                                <span style="color:#94a3b8; font-size:11px;">-</span>
                            @endforelse
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

    </div>

    <div class="footer">
        <p>Email ini dikirim otomatis oleh sistem 5 menit sebelum shift dimulai.<br>
        Jangan balas email ini.</p>
    </div>

</div>
</body>
</html>
