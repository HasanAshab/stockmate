<?php

namespace App\Listeners;

use App\Actions\Auth\SendVerificationOtp;
use Illuminate\Auth\Events\Registered;

class SendOneTimePassword
{
    public function __construct(
        protected SendVerificationOtp $sendVerificationOtp,
    ) {}

    public function handle(Registered $event): void
    {
        $this->sendVerificationOtp->execute($event->user);
    }
}
