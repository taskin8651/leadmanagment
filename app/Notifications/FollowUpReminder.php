<?php

namespace App\Notifications;

use App\Models\FollowUp;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FollowUpReminder extends Notification
{
    use Queueable;

    public function __construct(private FollowUp $followUp)
    {
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Follow-up due soon: ' . $this->followUp->lead->name)
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line('You have a follow-up due at ' . $this->followUp->follow_up_at->format('d M Y, h:i A') . ' with ' . $this->followUp->lead->name . '.')
            ->line($this->followUp->subject ?: ucfirst($this->followUp->type));
    }

    public function toArray($notifiable): array
    {
        return [
            'icon' => 'bi-calendar-event',
            'title' => 'Follow-up due soon',
            'body' => $this->followUp->lead->name . ' — ' . $this->followUp->follow_up_at->format('h:i A'),
            'url' => route('client.leads.show', $this->followUp->lead_id),
        ];
    }
}
