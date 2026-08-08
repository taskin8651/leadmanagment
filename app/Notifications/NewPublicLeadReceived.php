<?php

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewPublicLeadReceived extends Notification
{
    use Queueable;

    public function __construct(private Lead $lead)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'icon' => 'bi-person-plus',
            'title' => 'New lead from your public form',
            'body' => $this->lead->name . ' (' . $this->lead->phone . ')',
            'url' => route('client.leads.show', $this->lead),
        ];
    }
}
