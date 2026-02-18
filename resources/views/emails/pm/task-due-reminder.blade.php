<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PM Task Due Today</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #3B82F6; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f8f9fa; padding: 20px; border: 1px solid #ddd; }
        .task-info { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .task-info table { width: 100%; border-collapse: collapse; }
        .task-info td { padding: 8px; border-bottom: 1px solid #eee; }
        .task-info td:first-child { font-weight: bold; width: 40%; color: #666; }
        .badge { display: inline-block; padding: 5px 10px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .badge-pending { background: #3B82F6; color: white; }
        .badge-shift { background: #EAB308; color: white; }
        .btn { display: inline-block; padding: 12px 24px; background: #3B82F6; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .btn:hover { background: #2563EB; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        .alert-box { background: #FEF3C7; border: 1px solid #F59E0B; border-radius: 5px; padding: 12px; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin: 0;">PM Task Due Today</h1>
            <p style="margin: 10px 0 0 0;">You have a preventive maintenance task scheduled for today</p>
        </div>

        <div class="content">
            <p>Dear <strong>{{ $task->assignedUser->name ?? 'Team Member' }}</strong>,</p>

            <div class="alert-box">
                <strong>Reminder:</strong> You have a PM task that is due today. Please complete it during your assigned shift.
            </div>

            <div class="task-info">
                <h3 style="margin-top: 0;">Task Details</h3>
                <table>
                    <tr>
                        <td>Task Name</td>
                        <td><strong style="color: #3B82F6;">{{ $task->task_name }}</strong></td>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <td><span class="badge badge-pending">{{ strtoupper($task->status) }}</span></td>
                    </tr>
                    @if($task->task_description)
                    <tr>
                        <td>Description</td>
                        <td>{{ $task->task_description }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td>Scheduled Date</td>
                        <td><strong>{{ \Carbon\Carbon::parse($task->task_date)->format('d M Y') }}</strong></td>
                    </tr>
                    <tr>
                        <td>Assigned Shift</td>
                        <td>
                            <span class="badge badge-shift">
                                @if($task->assigned_shift_id == 1)
                                    SHIFT 1 (22:00-05:00)
                                @elseif($task->assigned_shift_id == 2)
                                    SHIFT 2 (06:00-13:00)
                                @elseif($task->assigned_shift_id == 3)
                                    SHIFT 3 (14:00-21:00)
                                @else
                                    N/A
                                @endif
                            </span>
                        </td>
                    </tr>
                    @if($task->equipment_type)
                    <tr>
                        <td>Equipment Type</td>
                        <td>{{ $task->equipment_type }}</td>
                    </tr>
                    @endif
                </table>
            </div>

            <p style="text-align: center; margin: 25px 0;">
                <a href="{{ route('supervisor.my-tasks.preventive-maintenance') }}" class="btn">
                    View My PM Tasks
                </a>
            </p>

            <p><strong>Important:</strong> Please complete this task during your assigned shift and update the task status once done.</p>
        </div>

        <div class="footer">
            <p>This is an automated reminder from the Warehouse Maintenance System.<br>
            Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
