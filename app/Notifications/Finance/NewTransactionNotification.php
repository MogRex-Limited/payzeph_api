<?php

namespace App\Notifications\Finance;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewTransactionNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(public Transaction $transaction)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $data = $this->buildData($notifiable);
        return (new MailMessage)
            ->subject($data['title'])
            ->greeting("Hello " . $notifiable->full_name)
            ->line("Kindly see the details of the transaction:")
            ->line("Type: {$this->transaction->type}")
            ->line("Amount: {$this->transaction->formattedAmount()}")
            ->line("Description: {$this->transaction->description}")
            ->action("View Transaction", $this->transaction->viewUrl());

    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }

    public function toDatabase($notifiable)
    {
        return $this->buildData($notifiable);
    }

    public function buildData($notifiable)
    {
        return [
            'data' => [
                'transaction_id' => $this->transaction->id,
            ],
            'title' => 'New Transaction Notification',
            'message' => 'A new transaction occurred on your account. Click to see more details.',
            'link' => null,
            'type' => 'transaction',
            'batch_no' => null,
        ];
    }
}
