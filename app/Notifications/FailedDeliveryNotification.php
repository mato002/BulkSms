<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FailedDeliveryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $failureCount;
    public $timeframe;
    public $details;

    /**
     * Create a new notification instance.
     */
    public function __construct($failureCount, $timeframe = '1 hour', $details = [])
    {
        $this->failureCount = $failureCount;
        $this->timeframe = $timeframe;
        $this->details = $details;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        $settings = $notifiable->client->notificationSettings ?? null;
        
        $channels = ['database'];
        
        if ($settings && $settings->notify_via_email) {
            $channels[] = 'mail';
        }
        
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('⚠️ High Failure Rate Alert')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('We detected multiple failed message deliveries.')
            ->line('**Failed Messages:** ' . $this->failureCount)
            ->line('**Timeframe:** Last ' . $this->timeframe);

        if (!empty($this->details)) {
            $mail->line('**Common Issues:**');
            foreach ($this->details as $detail) {
                $mail->line('• ' . $detail);
            }
        }

        return $mail
            ->action('View Messages', url('/messages'))
            ->line('Please check your message logs for more details.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'failed_delivery',
            'title' => 'High Failure Rate Detected',
            'message' => $this->failureCount . ' messages failed in the last ' . $this->timeframe,
            'failure_count' => $this->failureCount,
            'timeframe' => $this->timeframe,
            'details' => $this->details,
            'action_url' => url('/messages?status=failed'),
        ];
    }
}


