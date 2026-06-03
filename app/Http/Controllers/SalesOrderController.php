<?php

namespace App\Http\Controllers;

use App\Enums\SalesOrderStatus;
use App\Http\Filters\FiltersDateRange;
use App\Http\Requests\SalesOrder\StoreSalesOrderRequest;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\WarehouseStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SalesOrderController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', SalesOrder::class);

        return QueryBuilder::for(SalesOrder::class)
            ->with(['warehouse', 'createdBy', 'items'])
            ->allowedFilters(
                AllowedFilter::exact('status'),
                AllowedFilter::custom('created_at', new FiltersDateRange),
            )
            ->defaultSort('-created_at')
            ->paginate(15)
            ->appends(request()->query())
            ->toResourceCollection();
    }

    public function store(StoreSalesOrderRequest $request)
    {
        Gate::authorize('create', SalesOrder::class);

        foreach ($request->validated('items') as $item) {
            $warehouseStock = WarehouseStock::where('warehouse_id', $request->validated('warehouse_id'))
                ->where('product_id', $item['product_id'])
                ->first();

            $product = Product::find($item['product_id']);

            if (! $warehouseStock || $warehouseStock->quantity < $item['quantity']) {
                return response()->json([
                    'message' => "Insufficient stock for product {$product->name} in selected warehouse.",
                ], 422);
            }
        }

        $salesOrder = DB::transaction(function () use ($request) {
            $totalAmount = collect($request->validated('items'))
                ->sum(fn ($item) => $item['quantity'] * $item['unit_price']);

            $salesOrder = SalesOrder::create([
                'customer_name' => $request->validated('customer_name'),
                'customer_email' => $request->validated('customer_email'),
                'customer_phone' => $request->validated('customer_phone'),
                'warehouse_id' => $request->validated('warehouse_id'),
                'total_amount' => $totalAmount,
                'created_by' => $request->user()->id,
            ]);

            foreach ($request->validated('items') as $item) {
                $salesOrder->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                ]);
            }

            return $salesOrder;
        });

        return $salesOrder->load(['warehouse', 'createdBy', 'items.product'])
            ->toResource()
            ->response()
            ->setStatusCode(201);
    }

    public function show(SalesOrder $salesOrder)
    {
        Gate::authorize('view', $salesOrder);

        return $salesOrder->load(['warehouse', 'createdBy', 'items.product'])
            ->toResource();
    }

    public function initiatePayment(SalesOrder $salesOrder)
    {
        Gate::authorize('initiatePayment', $salesOrder);

        if ($salesOrder->status !== SalesOrderStatus::Pending) {
            return response()->json([
                'message' => 'Only pending sales orders can initiate payment.',
            ], 422);
        }

        $tranId = "SO-{$salesOrder->id}-".time();

        $paymentData = [
            'total_amount' => $salesOrder->total_amount,
            'currency' => 'BDT',
            'tran_id' => $tranId,
            'success_url' => url('/api/v1/payment/success'),
            'fail_url' => url('/api/v1/payment/fail'),
            'cancel_url' => url('/api/v1/payment/cancel'),
            'cus_name' => $salesOrder->customer_name,
            'cus_email' => $salesOrder->customer_email,
            'cus_add1' => 'Dhaka',
            'cus_city' => 'Dhaka',
            'cus_country' => 'Bangladesh',
            'cus_phone' => $salesOrder->customer_phone ?: 'N/A',
            'shipping_method' => 'NO',
            'product_name' => "Sales Order #{$salesOrder->id}",
            'product_category' => 'General',
            'product_profile' => 'general',
        ];

        $sslc = new SslCommerzNotification;
        $paymentUrl = $sslc->makePayment($paymentData, 'hosted');

        return response()->json([
            'payment_url' => $paymentUrl,
            'transaction_id' => $tranId,
        ]);
    }

    public function cancel(SalesOrder $salesOrder)
    {
        Gate::authorize('cancel', $salesOrder);

        if ($salesOrder->status === SalesOrderStatus::Paid) {
            return response()->json([
                'message' => 'Cannot cancel a paid order.',
            ], 422);
        }

        if ($salesOrder->status !== SalesOrderStatus::Pending) {
            return response()->json([
                'message' => 'Only pending sales orders can be cancelled.',
            ], 422);
        }

        $salesOrder->update([
            'status' => SalesOrderStatus::Cancelled,
        ]);

        return response()->json([
            'message' => 'Sales order cancelled.',
        ]);
    }
}
