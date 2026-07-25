<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\Twilio\TwilioSmsMessage;
use Spatie\OneTimePasswords\Notifications\OneTimePasswordNotification;

class AuthOtpNotification extends OneTimePasswordNotification implements ShouldQueue
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $notifiable->email ? ['mail'] : ['twilio'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your verification code')
            ->line("Your code is: {$this->oneTimePassword->password}")
            ->line('It expires shortly.');
    }

    public function toTwilio($notifiable): TwilioSmsMessage
    {
        return (new TwilioSmsMessage)
            ->content("Your verification code is: {$this->oneTimePassword->password}");
    }
}
