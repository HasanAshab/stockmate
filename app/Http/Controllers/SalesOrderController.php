<?php

namespace App\Http\Controllers;

use App\Actions\SalesOrder\CreateSalesOrder;
use App\Enums\SalesOrderStatus;
use App\Http\Filters\FiltersDateRange;
use App\Http\Requests\SalesOrder\StoreSalesOrderRequest;
use App\Models\SalesOrder;
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

    public function store(StoreSalesOrderRequest $request, CreateSalesOrder $createSalesOrder)
    {
        Gate::authorize('create', SalesOrder::class);

        $salesOrder = $createSalesOrder->execute(
            $request->user(),
            $request->validated(),
        );

        return $salesOrder
            ->load(['warehouse', 'createdBy', 'items.product'])
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

        if (! $salesOrder->status->isPending()) {
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

        if ($salesOrder->status->isPaid()) {
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
