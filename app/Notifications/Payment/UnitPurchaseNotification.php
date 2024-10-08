<?php

namespace App\Notifications\Payment;

use App\Models\Subscription;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Kutia\Larafirebase\Messages\FirebaseMessage;

class UnitPurchaseNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected Transaction $transaction)
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
            ->markdown('emails.finance.payment.new_payment', [
                "title" => $data["title"],
                "message" => $data["message"],
                "transaction" => $this->transaction,
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
                'id' => $this->transaction->id,
            ],
            'title' => "Payment Successful",
            'message' => "You have just made a payment of " . format_money($this->transaction->amount)  . " for the purchase of {$this->transaction->unit_quanity} units. If you have any questions, concerns, or feedback regarding your transaction or any aspect of our services, please do not hesitate to reach out to our dedicated support team.",
            'link' => null,
            'type' => 'transaction',
            'batch_no' => null,
        ];
    }
}
