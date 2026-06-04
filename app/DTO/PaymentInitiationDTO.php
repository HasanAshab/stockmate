<?php

namespace App\DTOs;

class PaymentInitiationDTO
{
    public function __construct(
        public readonly string $transactionId,
        public readonly string $paymentUrl,
    ) {}

    public function toArray(): array
    {
        return [
            'transaction_id' => $this->transactionId,
            'payment_url'    => $this->paymentUrl,
        ];
    }
}