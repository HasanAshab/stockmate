<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\Twilio\TwilioSmsMessage;

class AuthOtpNotification extends OtpChannelNotification implements ShouldQueue
{
    use Queueable;

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
