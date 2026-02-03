<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PM Task Assigned</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #22C55E; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f8f9fa; padding: 20px; border: 1px solid #ddd; }
        .task-info { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .task-info table { width: 100%; border-collapse: collapse; }
        .task-info td { padding: 8px; border-bottom: 1px solid #eee; }
        .task-info td:first-child { font-weight: bold; width: 40%; color: #666; }
        .badge { display: inline-block; padding: 5px 10px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .badge-pending { background: #3B82F6; color: white; }
        .badge-shift { background: #EAB308; color: white; }
        .btn { display: inline-block; padding: 12px 24px; background: #22C55E; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .btn:hover { background: #16A34A; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin: 0;">Preventive Maintenance Task Assigned</h1>
            <p style="margin: 10px 0 0 0;">You have been assigned a new PM task</p>
        </div>

        <div class="content">
            <p>Dear <strong>{{ $task->assignedUser->name ?? 'Team Member' }}</strong>,</p>

            <p>A new preventive maintenance task has been assigned to you. Please review the details below and complete the task on the scheduled date.</p>

            <div class="task-info">
                <h3 style="margin-top: 0;">Task Details</h3>
                <table>
                    <tr>
                        <td>Task Name</td>
                        <td><strong style="color: #22C55E;">{{ $task->task_name }}</strong></td>
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
                                    SHIFT 1 (07:00-15:00)
                                @elseif($task->assigned_shift_id == 2)
                                    SHIFT 2 (15:00-23:00)
                                @elseif($task->assigned_shift_id == 3)
                                    SHIFT 3 (23:00-07:00)
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
                    @if($task->is_recurring)
                    <tr>
                        <td>Recurring Task</td>
                        <td>Yes ({{ ucfirst($task->recurrence_pattern) }})</td>
                    </tr>
                    @endif
                    @if($task->pmScheduleDate && $task->pmScheduleDate->pmSchedule)
                    <tr>
                        <td>Schedule Month</td>
                        <td>{{ \Carbon\Carbon::parse($task->pmScheduleDate->pmSchedule->scheduled_month)->format('F Y') }}</td>
                    </tr>
                    @endif
                </table>
            </div>

            <p style="text-align: center; margin: 25px 0;">
                <a href="{{ route('supervisor.my-tasks.preventive-maintenance') }}" class="btn">
                    View My PM Tasks
                </a>
            </p>

            <p><strong>Important:</strong> Please complete this task on the scheduled date during your assigned shift. Update the task status once completed.</p>

            <p>If you have any questions or need assistance, please contact your supervisor.</p>
        </div>

        <div class="footer">
            <p>This is an automated message from the Warehouse Maintenance System.<br>
            Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
