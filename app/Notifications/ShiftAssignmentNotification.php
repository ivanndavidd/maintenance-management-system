<?php

namespace App\Notifications;

use App\Models\ShiftSchedule;
use App\Models\ShiftAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ShiftAssignmentNotification extends Notification
{
    use Queueable;

    protected $shiftSchedule;
    protected $assignments;
    protected $type; // 'assigned', 'removed', 'updated'

    /**
     * Create a new notification instance.
     */
    public function __construct(ShiftSchedule $shiftSchedule, $assignments, $type = 'assigned')
    {
        $this->shiftSchedule = $shiftSchedule;
        $this->assignments = $assignments;
        $this->type = $type;
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
        $message = (new MailMessage)
            ->subject('Shift Assignment Notification')
            ->greeting('Hello ' . $notifiable->name . '!');

        if ($this->type === 'assigned') {
            $message->line('You have been assigned to the following shift schedule:')
                    ->line('**Schedule:** ' . $this->shiftSchedule->name)
                    ->line('**Period:** ' . $this->shiftSchedule->start_date->format('d M Y') . ' - ' . $this->shiftSchedule->end_date->format('d M Y'));

            foreach ($this->assignments as $assignment) {
                $message->line('- ' . $assignment->day_name . ': ' . $assignment->shift_name . ' (' . $assignment->start_time . ' - ' . $assignment->end_time . ')');
            }

            $message->line('Please check your schedule and be prepared for your assigned shifts.')
                    ->action('View Schedule', route('admin.shifts.edit', $this->shiftSchedule->id));
        } elseif ($this->type === 'removed') {
            $message->line('You have been removed from the shift schedule: ' . $this->shiftSchedule->name)
                    ->line('If you have any questions, please contact your supervisor.');
        } elseif ($this->type === 'activated') {
            $message->line('The shift schedule "' . $this->shiftSchedule->name . '" has been activated.')
                    ->line('**Period:** ' . $this->shiftSchedule->start_date->format('d M Y') . ' - ' . $this->shiftSchedule->end_date->format('d M Y'))
                    ->line('Please check your assignments and be prepared for your shifts.')
                    ->action('View Schedule', route('admin.shifts.edit', $this->shiftSchedule->id));
        }

        return $message;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable)
    {
        $data = [
            'shift_schedule_id' => $this->shiftSchedule->id,
            'shift_schedule_name' => $this->shiftSchedule->name,
            'type' => $this->type,
            'message' => $this->getMessage(),
        ];

        if ($this->type === 'assigned') {
            $data['assignments'] = $this->assignments->map(function ($assignment) {
                return [
                    'day' => $assignment->day_name,
                    'shift' => $assignment->shift_name,
                    'time' => $assignment->start_time . ' - ' . $assignment->end_time,
                ];
            });
        }

        return $data;
    }

    /**
     * Get the notification message
     */
    private function getMessage()
    {
        if ($this->type === 'assigned') {
            return 'You have been assigned to shift schedule: ' . $this->shiftSchedule->name;
        } elseif ($this->type === 'removed') {
            return 'You have been removed from shift schedule: ' . $this->shiftSchedule->name;
        } elseif ($this->type === 'activated') {
            return 'Shift schedule "' . $this->shiftSchedule->name . '" has been activated';
        }

        return 'Shift schedule updated';
    }
}
