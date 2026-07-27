<?php

namespace App\Http\Controllers;

use App\Actions\PurchaseOrder\CreatePurchaseOrder;
use App\Actions\PurchaseOrder\ReceivePurchaseOrder;
use App\Actions\PurchaseOrder\UpdatePurchaseOrder;
use App\Enums\PurchaseOrderStatus;
use App\Http\Filters\FiltersDateRange;
use App\Http\Requests\PurchaseOrder\ReceivePurchaseOrderRequest;
use App\Http\Requests\PurchaseOrder\StorePurchaseOrderRequest;
use App\Http\Requests\PurchaseOrder\UpdatePurchaseOrderRequest;
use App\Http\Resources\PurchaseOrderResource;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Gate;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', PurchaseOrder::class);

        $purchaseOrders = QueryBuilder::for(PurchaseOrder::class)
            ->with(['supplier', 'warehouse', 'creator', 'items'])
            ->allowedFilters(
                AllowedFilter::belongsTo('supplier'),
                AllowedFilter::belongsTo('warehouse'),
                AllowedFilter::belongsTo('creator'),
                AllowedFilter::belongsTo('product', 'items.product'),
                AllowedFilter::exact('status'),
                AllowedFilter::custom('ordered_at', new FiltersDateRange),
                AllowedFilter::custom('received_at', new FiltersDateRange),
                AllowedFilter::custom('created_at', new FiltersDateRange),
            )
            ->defaultSort('-created_at')
            ->cursorPaginate(10)
            ->appends(request()->query());

        return PurchaseOrderResource::collection($purchaseOrders);
    }

    public function store(StorePurchaseOrderRequest $request, CreatePurchaseOrder $createPurchaseOrder)
    {
        Gate::authorize('create', PurchaseOrder::class);

        $purchaseOrder = $createPurchaseOrder->execute(
            $request->user(),
            $request->validated()
        );

        $purchaseOrder->load(['supplier', 'warehouse', 'creator', 'items.product']);

        return (new PurchaseOrderResource($purchaseOrder))
            ->response()
            ->setStatusCode(201);
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        Gate::authorize('view', $purchaseOrder);

        $purchaseOrder->load(['supplier', 'warehouse', 'creator', 'items.product']);

        return new PurchaseOrderResource($purchaseOrder);
    }

    public function update(UpdatePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder, UpdatePurchaseOrder $updatePurchaseOrder)
    {
        Gate::authorize('update', $purchaseOrder);

        $purchaseOrder = $updatePurchaseOrder->execute($purchaseOrder, $request->validated());

        $purchaseOrder->load(['supplier', 'warehouse', 'creator', 'items.product']);

        return new PurchaseOrderResource($purchaseOrder);
    }

    public function markOrdered(PurchaseOrder $purchaseOrder)
    {
        Gate::authorize('markOrdered', $purchaseOrder);

        $purchaseOrder->update([
            'status' => PurchaseOrderStatus::Ordered,
            'ordered_at' => now(),
        ]);

        $purchaseOrder->load(['supplier', 'warehouse', 'creator', 'items.product']);

        return new PurchaseOrderResource($purchaseOrder);
    }

    public function cancel(PurchaseOrder $purchaseOrder)
    {
        Gate::authorize('cancel', $purchaseOrder);

        $purchaseOrder->update([
            'status' => PurchaseOrderStatus::Cancelled,
        ]);

        return response()->json([
            'message' => 'Purchase order cancelled.',
        ]);
    }

    public function receive(ReceivePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder, ReceivePurchaseOrder $receivePurchaseOrder)
    {
        Gate::authorize('receive', $purchaseOrder);

        $purchaseOrder = $receivePurchaseOrder->execute(
            $request->user(),
            $purchaseOrder,
            $request->validated('items')
        );

        $purchaseOrder->load(['supplier', 'warehouse', 'creator', 'items.product']);

        return response()->json([
            'message' => 'Stock received.',
            'purchase_order' => new PurchaseOrderResource($purchaseOrder),
        ]);
    }
}
