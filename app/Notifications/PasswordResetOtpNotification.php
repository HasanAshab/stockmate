<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\Twilio\TwilioSmsMessage;

class PasswordResetOtpNotification extends OtpChannelNotification implements ShouldQueue
{
    use Queueable;

    public function via($notifiable): array
    {
        return $notifiable->email ? ['mail'] : ['twilio'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Reset your password')
            ->line("Your password reset code is: {$this->oneTimePassword->password}")
            ->line('If you did not request this, you can ignore this message.');
    }

    public function toTwilio($notifiable): TwilioSmsMessage
    {
        return (new TwilioSmsMessage)
            ->content("Your password reset code is: {$this->oneTimePassword->password}");
    }
}