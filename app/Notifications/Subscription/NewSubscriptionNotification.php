<?php

namespace App\Notifications\Subscription;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Kutia\Larafirebase\Messages\FirebaseMessage;

class NewSubscriptionNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected Subscription $subscription)
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $data = $this->buildData($notifiable);
        return (new MailMessage)
            ->subject($data["title"])
            ->markdown('emails.finance.subscription.new_subscription', [
                "title" => $data["title"],
                "message" => $data["message"],
                "plan" => $this->subscription->plan,
                "model" => $this->subscription->user
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
    public function toDatabase($notifiable)
    {
        return $this->buildData($notifiable);
    }

    public function toFirebase(object $notifiable)
    {
        $data = $this->buildData($notifiable);
        $deviceTokens = [$notifiable->fcm_token];

        return (new FirebaseMessage)
            ->withTitle($data["title"])
            ->withBody($data["message"])
            ->asNotification($deviceTokens);
    }

    public function buildData($notifiable)
    {
        return [
            'data' => [
                'id' => $this->subscription->id,
            ],
            'title' => "Subscription to {$this->subscription->plan->name}",
            'message' => "You have successfully subscribe to {$this->subscription->plan->name}. If you have any questions, concerns, or feedback regarding your subscription or any aspect of our services, please do not hesitate to reach out to our dedicated support team.",
            'link' => null,
            'type' => 'subscription',
            'batch_no' => null,
        ];
    }
}
