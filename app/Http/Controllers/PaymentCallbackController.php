<?php

namespace App\Http\Controllers;

use App\Actions\FinalizeSalesOrderPayment;
use App\Enums\SalesOrderStatus;
use App\Models\SalesOrder;
use HasinHayder\Sslcommerz\Facades\Sslcommerz;
use Illuminate\Http\Request;

class PaymentCallbackController extends Controller
{
    public function success(Request $request, FinalizeSalesOrderPayment $finalizeSalesOrderPayment)
    {
        $payload = $request->all();
        $trxId = $payload['tran_id'];
        $amount = $payload['amount'];

        if (! Sslcommerz::verifyHash($payload)) {
            return response()->json([
                'message' => 'Payment validation failed.',
            ], 400);
        }

        if (! Sslcommerz::validatePayment($payload, $trxId, $amount)) {
            return response()->json([
                'message' => 'Payment validation failed.',
            ], 400);
        }

        $finalizeSalesOrderPayment->execute($trxId, $payload);

        return response()->json([
            'message' => 'Payment successful.',
        ]);
    }

    public function fail(Request $request)
    {
        $trxId = $request->input('tran_id');

        $salesOrder = SalesOrder::where('transaction_id', $trxId)
            ->orWhere(function ($query) use ($trxId) {
                $query->whereNull('transaction_id')
                    ->whereRaw("? LIKE CONCAT('SO-', id, '-%')", [$trxId]);
            })
            ->first();

        if ($salesOrder) {
            $salesOrder->update([
                'status' => SalesOrderStatus::Failed,
                'transaction_id' => $trxId,
                'payment_payload' => $request->all(),
            ]);
        }

        return response()->json([
            'message' => 'Payment failed.',
        ]);
    }

    public function cancel(Request $request)
    {
        $trxId = $request->input('tran_id');

        $salesOrder = SalesOrder::where('transaction_id', $trxId)
            ->orWhere(function ($query) use ($trxId) {
                $query->whereNull('transaction_id')
                    ->whereRaw("? LIKE CONCAT('SO-', id, '-%')", [$trxId]);
            })
            ->first();

        if ($salesOrder) {
            $salesOrder->update([
                'status' => SalesOrderStatus::Cancelled,
                'transaction_id' => $trxId,
                'payment_payload' => $request->all(),
            ]);
        }

        return response()->json([
            'message' => 'Payment cancelled.',
        ]);
    }

    public function ipn(Request $request, FinalizeSalesOrderPayment $finalizeSalesOrderPayment)
    {
        $payload = $request->all();
        $trxId = $payload['tran_id'];
        $status = $payload['status'];

        if (! Sslcommerz::verifyHash($payload)) {
            return response()->json(['error' => 'Hash verification failed'], 400);
        }

        if ($status === 'VALID' || $status === 'VALIDATED') {
            $finalizeSalesOrderPayment->execute($trxId, $payload);
        }

        return response()->json(['received' => true]);
    }
}
