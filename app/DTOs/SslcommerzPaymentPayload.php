<?php

namespace App\DTOs;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class SslcommerzPaymentPayload implements Castable
{
    public function __construct(
        public string $tranId,
        public string $status,
        public float $amount,
        public string $currency = 'BDT',
        public ?string $tranDate = null,
        public ?string $bankTranId = null,
        public ?string $valId = null,
        public ?string $method = null,
        public ?string $brand = null,
        public ?string $issuer = null,
        public ?int $riskLevel = null,
        public ?string $riskTitle = null,
    ) {}

    public static function fromArray(array $payload): self
    {
        return new self(
            tranId: $payload['tran_id'],
            status: $payload['status'],
            amount: (float) $payload['amount'],
            currency: $payload['currency'] ?? 'BDT',
            tranDate: $payload['tran_date'] ?? null,
            bankTranId: $payload['bank_tran_id'] ?? null,
            valId: $payload['val_id'] ?? null,
            method: $payload['card_type'] ?? null,
            brand: $payload['card_brand'] ?? null,
            issuer: $payload['card_issuer'] ?? null,
            riskLevel: $payload['risk_level'] ?? null,
            riskTitle: $payload['risk_title'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'tran_id' => $this->tranId,
            'status' => $this->status,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'tran_date' => $this->tranDate,
            'bank_tran_id' => $this->bankTranId,
            'val_id' => $this->valId,
            'method' => $this->method,
            'brand' => $this->brand,
            'issuer' => $this->issuer,
            'risk_level' => $this->riskLevel,
            'risk_title' => $this->riskTitle,
        ];
    }

    public static function castUsing(array $arguments): CastsAttributes
    {
        return new class implements CastsAttributes {
            public function get($model, string $key, $value, array $attributes)
            {
                if (is_null($value)) {
                    return null;
                }

                $decoded = is_string($value) ? json_decode($value, true) : $value;

                return SslcommerzPaymentPayload::fromArray($decoded);
            }

            public function set($model, string $key, $value, array $attributes)
            {
                if (is_null($value)) {
                    return null;
                }

                if (is_array($value)) {
                    $value = SslcommerzPaymentPayload::fromArray($value);
                }

                return json_encode($value->toArray());
            }
        };
    }
}