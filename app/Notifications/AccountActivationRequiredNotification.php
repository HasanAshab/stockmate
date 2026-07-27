<?php

namespace App\Notifications;

use App\Models\User;
use App\Notifications\Concerns\HasRecipients;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountActivationRequiredNotification extends Notification implements ShouldQueue
{
    use HasRecipients, Queueable;

    public function __construct(public User $user) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($notifiable->email) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function databaseType(object $notifiable): string
    {
        return 'account-activation-required';
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('New User Registration Requires Approval')
            ->greeting('Hello!')
            ->line("{$this->user->name} has registered for an account and is awaiting activation.");

        if ($this->user->email) {
            $mail->line('Email: '.$this->user->email);
        }

        if ($this->user->phone) {
            $mail->line('Phone: '.$this->user->phone);
        }

        return $mail
            ->line('Please review the account and activate it if appropriate.')
            ->action('Review User', url('/users/'.$this->user->id))
            ->line('Thank you.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'user_id' => $this->user->id,
            'name' => $this->user->name,
            'identifier' => $this->user->email ?? $this->user->phone,
            'message' => "{$this->user->name} has registered and is awaiting account activation.",
        ];
    }
}