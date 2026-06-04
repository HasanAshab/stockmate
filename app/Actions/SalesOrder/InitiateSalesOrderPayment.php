<?php

namespace App\Actions\SalesOrder;

use App\Models\SalesOrder;
use App\DTOs\PaymentInitiationDTO;
use HasinHayder\Sslcommerz\Facades\Sslcommerz;
use Illuminate\Validation\ValidationException;

class InitiateSalesOrderPayment
{
    public function execute(SalesOrder $salesOrder): PaymentInitiationDTO
    {
        $this->ensureStatusIsPending($salesOrder);
        $productName = $this->getProductName($salesOrder);
        $salesOrder->generateTransactionId();

        try {
            $response = Sslcommerz::setOrder(
                (float) $salesOrder->total_amount,
                $salesOrder->transaction_id,
                $productName
            )
                ->setCustomer(
                    $salesOrder->customer_name,
                    $salesOrder->customer_email,
                    $salesOrder->customer_phone
                )
                ->makePayment();

            $salesOrder->save();
            return new PaymentInitiationDTO(
                transactionId: $salesOrder->transaction_id,
                paymentUrl: $response->gatewayPageURL(),
            );
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'message' => 'Connection error. Please try again later.',
            ]);
        }
    }

    private function ensureStatusIsPending(SalesOrder $salesOrder): void
    {
        if (! $salesOrder->status->isPending()) {
            throw ValidationException::withMessages([
                'status' => 'Only pending sales orders can initiate payment.',
            ]);
        }
    }

    private function getProductName(SalesOrder $salesOrder): string
    {
        return "Sales Order #{$salesOrder->id}";
    }
}
