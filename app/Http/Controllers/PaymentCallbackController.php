<?php

namespace App\Http\Controllers;

use App\Actions\SalesOrder\FinalizeSalesOrderPayment;
use App\Actions\SalesOrder\ResolveSalesOrderPaymentState;
use App\DTOs\SslcommerzPaymentPayload;
use App\Enums\SalesOrderStatus;
use App\Enums\SslcommerzPaymentStatus;
use HasinHayder\Sslcommerz\Facades\Sslcommerz;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * @group Payment Callbacks
 *
 * Webhook endpoints for SSLCommerz payment gateway callbacks
 *
 * @unauthenticated
 */
class PaymentCallbackController extends Controller
{
    /**
     * Payment Success Callback
     *
     * Handles successful payment notifications from SSLCommerz.
     *
     * @response 200 {
     *   "message": "Payment successful."
     * }
     * @response 422 {
     *   "message": "Hash validation failed.",
     *   "errors": {
     *     "payment": ["Hash validation failed."]
     *   }
     * }
     */
    public function success(Request $request, FinalizeSalesOrderPayment $finalizeSalesOrderPayment)
    {
        $payload = $request->all();

        $this->ensureHashIsValid($payload);
        $this->ensurePaymentIsValid($payload);

        $finalizeSalesOrderPayment->execute(
            SslcommerzPaymentPayload::fromArray($payload)
        );

        return response()->json([
            'message' => 'Payment successful.',
        ]);
    }

    /**
     * Payment Failure Callback
     *
     * Handles failed payment notifications from SSLCommerz.
     *
     * @response 200 {
     *   "message": "Payment failed."
     * }
     */
    public function fail(Request $request, ResolveSalesOrderPaymentState $resolver)
    {
        $resolver->execute(SalesOrderStatus::Failed, $request->all());

        return response()->json([
            'message' => 'Payment failed.',
        ]);
    }

    /**
     * Payment Cancellation Callback
     *
     * Handles cancelled payment notifications from SSLCommerz.
     *
     * @response 200 {
     *   "message": "Payment cancelled."
     * }
     */
    public function cancel(Request $request, ResolveSalesOrderPaymentState $resolver)
    {
        $resolver->execute(SalesOrderStatus::Cancelled, $request->all());

        return response()->json([
            'message' => 'Payment cancelled.',
        ]);
    }

    /**
     * IPN (Instant Payment Notification)
     *
     * Handles Instant Payment Notification from SSLCommerz for payment status updates.
     *
     * @response 200 {
     *   "received": true
     * }
     */
    public function ipn(Request $request, FinalizeSalesOrderPayment $finalizeSalesOrderPayment)
    {
        $payload = $request->all();
        $status = $payload['status'];

        $this->ensureHashIsValid($payload);

        if (SslcommerzPaymentStatus::isValid($status)) {
            $finalizeSalesOrderPayment->execute(
                SslcommerzPaymentPayload::fromArray($payload)
            );
        }

        return response()->json(['received' => true]);
    }

    private function ensureHashIsValid(array $payload): void
    {
        if (! Sslcommerz::verifyHash($payload)) {
            throw ValidationException::withMessages([
                'payment' => 'Hash validation failed.',
            ]);
        }
    }

    private function ensurePaymentIsValid(array $payload): void
    {
        $trxId = $payload['tran_id'];
        $amount = $payload['amount'];

        if (! Sslcommerz::validatePayment($payload, $trxId, $amount)) {
            throw ValidationException::withMessages([
                'payment' => 'Payment validation failed.',
            ]);
        }
    }
}
