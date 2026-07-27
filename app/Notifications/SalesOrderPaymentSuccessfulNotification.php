<?php

namespace App\Notifications;

use App\Models\SalesOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SalesOrderPaymentSuccessfulNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(SalesOrder $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(SalesOrder $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Payment Successful')
            ->greeting('Hello '.$notifiable->customer_name.'!')
            ->line('Your payment has been successfully received.')
            ->line('Order #: '.$notifiable->id)
            ->line('Transaction Ref: '.$notifiable->transaction_reference)
            ->line('Amount: '.number_format((float) $notifiable->total_amount, 2))
            ->line('Thank you for your payment.');
    }
}