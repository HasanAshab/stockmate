<?php

namespace App\Http\Controllers;

use App\Enums\SalesOrderStatus;
use App\Enums\StockLogType;
use App\Models\SalesOrder;
use App\Models\WarehouseStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Library\SslCommerz\SslCommerzNotification;

class PaymentCallbackController extends Controller
{
    public function success(Request $request)
    {
        $valId = $request->input('val_id');
        $tranId = $request->input('tran_id');

        $sslc = new SslCommerzNotification;
        $validation = $sslc->orderValidate($request->all(), $tranId, $request->input('amount'), $request->input('currency'));

        if (! $validation) {
            return response()->json([
                'message' => 'Payment validation failed.',
            ], 400);
        }

        $salesOrder = SalesOrder::where('transaction_id', $tranId)
            ->orWhere(function ($query) use ($tranId) {
                $query->whereNull('transaction_id')
                    ->whereRaw("? LIKE CONCAT('SO-', id, '-%')", [$tranId]);
            })
            ->first();

        if (! $salesOrder || $salesOrder->status !== SalesOrderStatus::Pending) {
            return response()->json([
                'message' => 'Invalid order or order is not pending.',
            ], 400);
        }

        try {
            DB::transaction(function () use ($salesOrder, $request, $tranId) {
                foreach ($salesOrder->items as $item) {
                    $warehouseStock = WarehouseStock::where('warehouse_id', $salesOrder->warehouse_id)
                        ->where('product_id', $item->product_id)
                        ->lockForUpdate()
                        ->first();

                    if (! $warehouseStock || $warehouseStock->quantity < $item->quantity) {
                        throw new \Exception("Insufficient stock for product {$item->product->name}");
                    }

                    $warehouseStock->decrement('quantity', $item->quantity);

                    $salesOrder->createdBy->stockLogs()->create([
                        'product_id' => $item->product_id,
                        'warehouse_id' => $salesOrder->warehouse_id,
                        'type' => StockLogType::Out,
                        'quantity' => $item->quantity,
                        'unit_cost' => $item->unit_price,
                        'note' => "Sold via Sales Order #{$salesOrder->id}",
                    ]);
                }

                $salesOrder->update([
                    'status' => SalesOrderStatus::Paid,
                    'transaction_id' => $tranId,
                    'payment_payload' => $request->all(),
                ]);
            });

            return response()->json([
                'message' => 'Payment successful.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function fail(Request $request)
    {
        $tranId = $request->input('tran_id');

        $salesOrder = SalesOrder::where('transaction_id', $tranId)
            ->orWhere(function ($query) use ($tranId) {
                $query->whereNull('transaction_id')
                    ->whereRaw("? LIKE CONCAT('SO-', id, '-%')", [$tranId]);
            })
            ->first();

        if ($salesOrder) {
            $salesOrder->update([
                'status' => SalesOrderStatus::Failed,
                'transaction_id' => $tranId,
                'payment_payload' => $request->all(),
            ]);
        }

        return response()->json([
            'message' => 'Payment failed.',
        ]);
    }

    public function cancel(Request $request)
    {
        $tranId = $request->input('tran_id');

        $salesOrder = SalesOrder::where('transaction_id', $tranId)
            ->orWhere(function ($query) use ($tranId) {
                $query->whereNull('transaction_id')
                    ->whereRaw("? LIKE CONCAT('SO-', id, '-%')", [$tranId]);
            })
            ->first();

        if ($salesOrder) {
            $salesOrder->update([
                'status' => SalesOrderStatus::Cancelled,
                'transaction_id' => $tranId,
                'payment_payload' => $request->all(),
            ]);
        }

        return response()->json([
            'message' => 'Payment cancelled.',
        ]);
    }
}
