<?php

namespace App\Http\Resources;

use App\DTOs\PaymentInitiationDTO;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PaymentInitiationDTO
 */
class PaymentInitiationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'transaction_reference' => $this->transactionId,
            'payment_url' => $this->paymentUrl,
        ];
    }
}
