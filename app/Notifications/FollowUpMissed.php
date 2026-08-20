<?php

namespace App\Notifications;

use App\Models\FollowUp;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class FollowUpMissed extends Notification
{
    use Queueable;

    public function __construct(private FollowUp $followUp)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'icon' => 'bi-x-octagon',
            'title' => 'Follow-up missed',
            'body' => $this->followUp->lead->name . ' — was due ' . $this->followUp->follow_up_at->format('d M Y, h:i A'),
            'url' => route('client.leads.show', $this->followUp->lead_id),
        ];
    }
}
