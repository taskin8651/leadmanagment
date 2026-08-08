<?php

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeadAssigned extends Notification
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
            'icon' => 'bi-person-check',
            'title' => 'Lead assigned to you',
            'body' => $this->lead->name . ' (' . $this->lead->lead_number . ')',
            'url' => route('client.leads.show', $this->lead->id),
        ];
    }
}
