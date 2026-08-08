<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionRenewed extends Notification
{
    use Queueable;

    public function __construct(private Subscription $subscription)
    {
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your subscription has been renewed')
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line('Your ' . $this->subscription->plan->name . ' plan has been renewed.')
            ->line('New expiry date: ' . $this->subscription->ends_at->format('d M Y'))
            ->action('View Dashboard', route('client.dashboard'));
    }

    public function toArray($notifiable): array
    {
        return [
            'icon' => 'bi-check2-circle',
            'title' => 'Subscription renewed',
            'body' => $this->subscription->plan->name . ' plan — valid until ' . $this->subscription->ends_at->format('d M Y'),
            'url' => route('client.dashboard'),
        ];
    }
}
