<?php

namespace App\Notifications;

use App\Models\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CampaignCompleteNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $campaign;

    /**
     * Create a new notification instance.
     */
    public function __construct(Campaign $campaign)
    {
        $this->campaign = $campaign;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        $settings = $notifiable->client->notificationSettings ?? null;
        
        $channels = ['database'];
        
        if ($settings && $settings->campaign_complete_enabled && $settings->notify_via_email) {
            $channels[] = 'mail';
        }
        
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $successRate = $this->campaign->total_recipients > 0 
            ? round(($this->campaign->sent_count / $this->campaign->total_recipients) * 100, 1)
            : 0;

        return (new MailMessage)
            ->subject('✅ Campaign Completed: ' . $this->campaign->name)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your campaign has been completed successfully.')
            ->line('**Campaign:** ' . $this->campaign->name)
            ->line('**Total Recipients:** ' . number_format($this->campaign->total_recipients))
            ->line('**Successfully Sent:** ' . number_format($this->campaign->sent_count))
            ->line('**Failed:** ' . number_format($this->campaign->failed_count))
            ->line('**Success Rate:** ' . $successRate . '%')
            ->line('**Total Cost:** KES ' . number_format($this->campaign->total_cost, 2))
            ->action('View Campaign', url('/campaigns/' . $this->campaign->id))
            ->line('Thank you for using our service!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'campaign_complete',
            'title' => 'Campaign Completed',
            'message' => 'Campaign "' . $this->campaign->name . '" has been completed.',
            'campaign_id' => $this->campaign->id,
            'campaign_name' => $this->campaign->name,
            'total_recipients' => $this->campaign->total_recipients,
            'sent_count' => $this->campaign->sent_count,
            'failed_count' => $this->campaign->failed_count,
            'total_cost' => $this->campaign->total_cost,
            'action_url' => url('/campaigns/' . $this->campaign->id),
        ];
    }
}


