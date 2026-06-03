<?php

namespace App\Actions\SalesOrder;

use App\Models\SalesOrder;
use HasinHayder\Sslcommerz\Facades\Sslcommerz;
use Illuminate\Validation\ValidationException;

class InitiateSalesOrderPayment
{
    public function execute(SalesOrder $salesOrder): string
    {
        $this->ensureStatusIsPending($salesOrder);
        $tranId = $this->generateTransactionId($salesOrder);
        $productName = $this->getProductName($salesOrder);

        try {
            $response = Sslcommerz::setOrder(
                (float) $salesOrder->total_amount,
                $tranId,
                $productName
            )
            ->setCustomer(
                $salesOrder->customer_name,
                $salesOrder->customer_email,
                $salesOrder->customer_phone
            )
            ->makePayment();

            return $response->gatewayPageURL();
        } 
        catch (\Exception $e) {
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

    private function generateTransactionId(SalesOrder $salesOrder): string
    {
        return "SO-{$salesOrder->id}-".time();
    }

    private function getProductName(SalesOrder $salesOrder): string
    {
        return "Sales Order #{$salesOrder->id}";
    }
}