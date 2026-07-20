<?php

namespace App\Actions\SalesOrder;

use App\DTOs\PaymentInitiationDTO;
use App\Models\SalesOrder;
use HasinHayder\Sslcommerz\Facades\Sslcommerz;
use Illuminate\Validation\ValidationException;

class InitiateSalesOrderPayment
{
    public function execute(SalesOrder $salesOrder): PaymentInitiationDTO
    {
        $productName = $this->getProductName($salesOrder);
        $salesOrder->generateTransactionId();

        try {
            $response = Sslcommerz::setOrder(
                (float) $salesOrder->total_amount,
                $salesOrder->transaction_reference,
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
                transactionId: $salesOrder->transaction_reference,
                paymentUrl: $response->gatewayPageURL(),
            );
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'message' => 'Connection error. Please try again later.',
            ]);
        }
    }

    private function getProductName(SalesOrder $salesOrder): string
    {
        return "Sales Order #{$salesOrder->id}";
    }
}
