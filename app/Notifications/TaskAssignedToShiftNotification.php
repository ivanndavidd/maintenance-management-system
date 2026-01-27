<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAssignedToShiftNotification extends Notification
{
    use Queueable;

    protected $task;
    protected $taskType; // 'stock_opname', 'maintenance', 'incident', 'task_request'
    protected $shiftInfo;

    /**
     * Create a new notification instance.
     */
    public function __construct($task, $taskType, $shiftInfo)
    {
        $this->task = $task;
        $this->taskType = $taskType;
        $this->shiftInfo = $shiftInfo;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $taskName = $this->getTaskName();
        $taskUrl = $this->getTaskUrl();

        return (new MailMessage)
            ->subject('New Task Assigned to Your Shift')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A new task has been assigned to your shift.')
            ->line('**Task Type:** ' . $this->getTaskTypeLabel())
            ->line('**Task:** ' . $taskName)
            ->line('**Shift:** ' . $this->shiftInfo['shift_name'])
            ->line('**Date:** ' . $this->shiftInfo['shift_date'])
            ->line('**Time:** ' . $this->shiftInfo['shift_time'])
            ->line('Please check the task details and complete it during your shift.')
            ->action('View Task', $taskUrl)
            ->line('Thank you for your cooperation!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable)
    {
        return [
            'task_id' => $this->task->id,
            'task_type' => $this->taskType,
            'task_name' => $this->getTaskName(),
            'shift_info' => $this->shiftInfo,
            'message' => 'New ' . $this->getTaskTypeLabel() . ' assigned to your shift',
            'url' => $this->getTaskUrl(),
        ];
    }

    /**
     * Get task name based on type
     */
    private function getTaskName()
    {
        switch ($this->taskType) {
            case 'stock_opname':
                return $this->task->schedule_code ?? 'Stock Opname';
            case 'maintenance':
                return $this->task->title ?? 'Maintenance Job';
            case 'incident':
                return $this->task->incident_title ?? 'Incident Report';
            case 'task_request':
                return $this->task->title ?? 'Task Request';
            default:
                return 'Task';
        }
    }

    /**
     * Get task type label
     */
    private function getTaskTypeLabel()
    {
        $labels = [
            'stock_opname' => 'Stock Opname',
            'maintenance' => 'Maintenance Job',
            'incident' => 'Incident Report',
            'task_request' => 'Task Request',
        ];

        return $labels[$this->taskType] ?? 'Task';
    }

    /**
     * Get task URL
     */
    private function getTaskUrl()
    {
        switch ($this->taskType) {
            case 'stock_opname':
                return route('admin.opname.schedules.show', $this->task->id);
            case 'maintenance':
                return route('admin.jobs.show', $this->task->id);
            case 'incident':
                return route('admin.incident-reports.show', $this->task->id);
            case 'task_request':
                return route('admin.task-requests.show', $this->task->id);
            default:
                return route('admin.dashboard');
        }
    }
}
