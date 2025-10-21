<?php

namespace App\Notifications;

use App\Models\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowBalanceNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $client;
    public $balance;
    public $threshold;

    /**
     * Create a new notification instance.
     */
    public function __construct(Client $client, $balance, $threshold)
    {
        $this->client = $client;
        $this->balance = $balance;
        $this->threshold = $threshold;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        $settings = $notifiable->client->notificationSettings ?? null;
        
        $channels = ['database']; // Always store in database
        
        if ($settings) {
            if ($settings->notify_via_email) {
                $channels[] = 'mail';
            }
            // SMS could be added here if enabled
        }
        
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $units = $this->client->getBalanceInUnits();
        
        return (new MailMessage)
            ->subject('⚠️ Low Balance Alert')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your account balance is running low.')
            ->line('**Current Balance:** KES ' . number_format($this->balance, 2))
            ->line('**Available Units:** ' . number_format($units, 0))
            ->line('**Threshold:** KES ' . number_format($this->threshold, 2))
            ->action('Top Up Now', url('/wallet'))
            ->line('Please top up your account to continue sending messages.')
            ->salutation('Best regards, ' . config('app.name'));
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'low_balance',
            'title' => 'Low Balance Alert',
            'message' => 'Your balance (KES ' . number_format($this->balance, 2) . ') is below the threshold.',
            'balance' => $this->balance,
            'threshold' => $this->threshold,
            'client_id' => $this->client->id,
            'action_url' => url('/wallet'),
        ];
    }
}


