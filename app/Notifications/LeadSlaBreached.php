<?php

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeadSlaBreached extends Notification
{
    use Queueable;

    public function __construct(private Lead $lead)
    {
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('SLA breach: ' . $this->lead->name . ' has not been contacted')
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line($this->lead->name . ' (' . $this->lead->lead_number . ') has had no response for over 24 hours since it came in.')
            ->line('Please follow up as soon as possible.');
    }

    public function toArray($notifiable): array
    {
        return [
            'icon' => 'bi-exclamation-triangle',
            'title' => 'SLA breach — lead not contacted',
            'body' => $this->lead->name . ' (' . $this->lead->lead_number . ') has been waiting over 24 hours',
            'url' => route('client.leads.show', $this->lead->id),
        ];
    }
}
