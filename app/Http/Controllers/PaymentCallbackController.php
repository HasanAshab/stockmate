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
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Unauthenticated;

#[Group('Payment Callbacks', 'Webhook endpoints for SSLCommerz payment gateway callbacks')]
#[Unauthenticated]
class PaymentCallbackController extends Controller
{
    /**
     * Payment Success Callback
     *
     * Handles successful payment notifications from SSLCommerz.
     */
    #[Response(['message' => 'Payment successful.'], 200)]
    #[Response(['message' => 'Hash validation failed.', 'errors' => ['payment' => ['Hash validation failed.']]], 422)]
    public function success(Request $request, FinalizeSalesOrderPayment $finalizeSalesOrderPayment)
    {
        $payload = $request->all();

        $this->ensureHashIsValid($payload);
        $this->ensurePaymentIsValid($payload);

        $finalizeSalesOrderPayment->execute(
            SslcommerzPaymentPayload::fromArray($payload)
        );

        return [
            'message' => 'Payment successful.',
        ];
    }

    /**
     * Payment Failure Callback
     *
     * Handles failed payment notifications from SSLCommerz.
     *
     * SSLCommerz expects a successful HTTP response (200 OK) to confirm that the
     * callback was received and processed successfully, even when the payment
     * itself has failed. The actual payment outcome is represented by the
     * application response body and the updated sales order status, not the HTTP
     * status code.
     */
    #[Response(['message' => 'Payment failed.'], 200)]
    public function fail(Request $request, ResolveSalesOrderPaymentState $resolver)
    {
        $resolver->execute(SalesOrderStatus::Failed, $request->all());

        // Return 200 OK to acknowledge receipt of the callback.
        // The payment status is "failed", but the callback itself
        // was processed successfully.
        return [
            'message' => 'Payment failed.',
        ];
    }

    /**
     * Payment Cancellation Callback
     *
     * Handles cancelled payment notifications from SSLCommerz.
     *
     * SSLCommerz expects a successful HTTP response (200 OK) to confirm that the
     * callback was received and processed successfully, even when the payment
     * was cancelled. The actual payment outcome is represented by the
     * application response body and the updated sales order status, not the HTTP
     * status code.
     */
    #[Response(['message' => 'Payment cancelled.'], 200)]
    public function cancel(Request $request, ResolveSalesOrderPaymentState $resolver)
    {
        $resolver->execute(SalesOrderStatus::Cancelled, $request->all());

        // Return 200 OK to acknowledge receipt of the callback.
        // The payment was cancelled, but the callback itself
        // was processed successfully.
        return [
            'message' => 'Payment cancelled.',
        ];
    }

    /**
     * IPN (Instant Payment Notification)
     *
     * Handles Instant Payment Notification from SSLCommerz for payment status updates.
     */
    #[Response(['received' => true], 200)]
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

        return ['received' => true];
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
