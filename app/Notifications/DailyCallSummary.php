<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DailyCallSummary extends Notification
{
    use Queueable;

    public function __construct(private int $count)
    {
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Aaj ' . $this->count . ' call(s) schedule hain')
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line('Aaj aapke ' . $this->count . ' lead(s) ko call karna hai.')
            ->action('Today\'s Calls dekhein', route('client.follow-ups.index', ['when' => 'today']))
            ->line('Rescheduled follow-ups bhi is list mein shamil hain.');
    }

    public function toArray($notifiable): array
    {
        return [
            'icon' => 'bi-telephone-outbound',
            'title' => "Today's calls",
            'body' => $this->count . ' follow-up(s) scheduled for today',
            'url' => route('client.follow-ups.index', ['when' => 'today']),
        ];
    }
}
