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
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Gate;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', PurchaseOrder::class);

        return QueryBuilder::for(PurchaseOrder::class)
            ->with(['supplier', 'warehouse', 'creator', 'items'])
            ->allowedFilters(
                AllowedFilter::belongsTo('supplier'),
                AllowedFilter::belongsTo('warehouse'),
                AllowedFilter::belongsTo('creator'),
                AllowedFilter::exact('status'),
                AllowedFilter::custom('ordered_at', new FiltersDateRange),
                AllowedFilter::custom('received_at', new FiltersDateRange),
                AllowedFilter::custom('created_at', new FiltersDateRange),
                AllowedFilter::groupOr('search', [
                    AllowedFilter::partial('note'),
                    AllowedFilter::partial('items.product.name'),
                    AllowedFilter::partial('items.product.sku'),
                ])
            )
            ->defaultSort('-created_at')
            ->cursorPaginate(10)
            ->appends(request()->query())
            ->toResourceCollection();
    }

    public function store(StorePurchaseOrderRequest $request, CreatePurchaseOrder $createPurchaseOrder)
    {
        Gate::authorize('create', PurchaseOrder::class);

        $purchaseOrder = $createPurchaseOrder->execute(
            $request->user(),
            $request->validated()
        );

        return $purchaseOrder->load(['supplier', 'warehouse', 'creator', 'items.product'])
            ->toResource()
            ->response()
            ->setStatusCode(201);
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        Gate::authorize('view', $purchaseOrder);

        return $purchaseOrder->load(['supplier', 'warehouse', 'creator', 'items.product'])
            ->toResource();
    }

    public function update(UpdatePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder, UpdatePurchaseOrder $updatePurchaseOrder)
    {
        Gate::authorize('update', $purchaseOrder);

        $purchaseOrder = $updatePurchaseOrder->execute($purchaseOrder, $request->validated());

        return $purchaseOrder->load(['supplier', 'warehouse', 'creator', 'items.product'])
            ->toResource();
    }

    public function markOrdered(PurchaseOrder $purchaseOrder)
    {
        Gate::authorize('markOrdered', $purchaseOrder);

        $purchaseOrder->update([
            'status' => PurchaseOrderStatus::Ordered,
            'ordered_at' => now(),
        ]);

        return $purchaseOrder->load(['supplier', 'warehouse', 'creator', 'items.product'])
            ->toResource();
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

        return response()->json([
            'message' => 'Stock received.',
            'purchase_order' => $purchaseOrder->load(['supplier', 'warehouse', 'creator', 'items.product'])
                ->toResource(),
        ]);
    }
}
