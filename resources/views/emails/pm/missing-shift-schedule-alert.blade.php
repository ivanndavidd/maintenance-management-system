<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Missing Shift Schedule Alert</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #F59E0B; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f8f9fa; padding: 20px; border: 1px solid #ddd; }
        .alert-box { background: #FEF2F2; border: 1px solid #EF4444; border-radius: 5px; padding: 12px; margin: 15px 0; }
        .task-list { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .task-list table { width: 100%; border-collapse: collapse; }
        .task-list th { padding: 8px; border-bottom: 2px solid #ddd; text-align: left; color: #666; font-size: 13px; }
        .task-list td { padding: 8px; border-bottom: 1px solid #eee; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 3px; font-size: 11px; font-weight: bold; }
        .badge-shift { background: #EAB308; color: white; }
        .btn { display: inline-block; padding: 12px 24px; background: #F59E0B; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .btn:hover { background: #D97706; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        .stat { display: inline-block; background: white; padding: 10px 20px; border-radius: 5px; margin: 5px; text-align: center; }
        .stat-number { font-size: 24px; font-weight: bold; color: #EF4444; }
        .stat-label { font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin: 0;">Missing Shift Schedule Alert</h1>
            <p style="margin: 10px 0 0 0;">{{ $siteName }}</p>
        </div>

        <div class="content">
            <div class="alert-box">
                <strong>Warning:</strong> There is no active shift schedule covering <strong>{{ $date->format('l, d M Y') }}</strong>, but there are <strong>{{ $unassignedTasks->count() }}</strong> PM task(s) scheduled for that date without assigned operators.
            </div>

            <div style="text-align: center; margin: 20px 0;">
                <div class="stat">
                    <div class="stat-number">{{ $unassignedTasks->count() }}</div>
                    <div class="stat-label">Unassigned Tasks</div>
                </div>
                <div class="stat">
                    <div class="stat-number">{{ $date->format('d') }}</div>
                    <div class="stat-label">{{ $date->format('M Y') }}</div>
                </div>
            </div>

            <div class="task-list">
                <h3 style="margin-top: 0;">Affected PM Tasks</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Task Name</th>
                            <th>Shift</th>
                            <th>Equipment</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($unassignedTasks as $task)
                        <tr>
                            <td>{{ $task->task_name }}</td>
                            <td>
                                <span class="badge badge-shift">
                                    @if($task->assigned_shift_id == 1)
                                        Shift 1
                                    @elseif($task->assigned_shift_id == 2)
                                        Shift 2
                                    @elseif($task->assigned_shift_id == 3)
                                        Shift 3
                                    @else
                                        -
                                    @endif
                                </span>
                            </td>
                            <td>{{ $task->equipment_type ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p style="text-align: center; margin: 25px 0;">
                <a href="{{ route('admin.shifts.create') }}" class="btn">
                    Create Shift Schedule
                </a>
            </p>

            <p><strong>Action Required:</strong> Please create and activate a shift schedule covering {{ $date->format('d M Y') }} so that PM tasks can be assigned to operators.</p>
        </div>

        <div class="footer">
            <p>This is an automated alert from the Warehouse Maintenance System.<br>
            Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
