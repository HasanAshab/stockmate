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
            ->with(['supplier', 'warehouse', 'createdBy', 'items'])
            ->allowedFilters(
                AllowedFilter::exact('status'),
                AllowedFilter::exact('supplier_id'),
                AllowedFilter::custom('created_at', new FiltersDateRange),
            )
            ->defaultSort('-created_at')
            ->paginate(15)
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

        return $purchaseOrder->load(['supplier', 'warehouse', 'createdBy', 'items.product'])
            ->toResource()
            ->response()
            ->setStatusCode(201);
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        Gate::authorize('view', $purchaseOrder);

        return $purchaseOrder->load(['supplier', 'warehouse', 'createdBy', 'items.product'])
            ->toResource();
    }

    public function update(UpdatePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder, UpdatePurchaseOrder $updatePurchaseOrder)
    {
        Gate::authorize('update', $purchaseOrder);

        if ($purchaseOrder->status->isReceived() || $purchaseOrder->status->isCancelled()) {
            return response()->json([
                'message' => 'This purchase order can no longer be edited.',
            ], 422);
        }

        $purchaseOrder = $updatePurchaseOrder->execute($purchaseOrder, $request->validated());

        return $purchaseOrder->load(['supplier', 'warehouse', 'createdBy', 'items.product'])
            ->toResource();
    }

    public function markOrdered(PurchaseOrder $purchaseOrder)
    {
        Gate::authorize('markOrdered', $purchaseOrder);

        if ($purchaseOrder->status !== PurchaseOrderStatus::Draft) {
            return response()->json([
                'message' => 'Only draft purchase orders can be marked as ordered.',
            ], 422);
        }

        $purchaseOrder->update([
            'status' => PurchaseOrderStatus::Ordered,
            'ordered_at' => now(),
        ]);

        return $purchaseOrder->load(['supplier', 'warehouse', 'createdBy', 'items.product'])
            ->toResource();
    }

    public function cancel(PurchaseOrder $purchaseOrder)
    {
        Gate::authorize('cancel', $purchaseOrder);

        if ($purchaseOrder->status->isPartiallyReceived() || $purchaseOrder->status->isReceived()) {
            return response()->json([
                'message' => 'Cannot cancel a PO that has already received stock.',
            ], 422);
        }

        if ($purchaseOrder->status !== PurchaseOrderStatus::Draft && $purchaseOrder->status !== PurchaseOrderStatus::Ordered) {
            return response()->json([
                'message' => 'Only draft or ordered purchase orders can be cancelled.',
            ], 422);
        }

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

        if ($purchaseOrder->status !== PurchaseOrderStatus::Ordered && $purchaseOrder->status !== PurchaseOrderStatus::PartiallyReceived) {
            return response()->json([
                'message' => 'Only ordered or partially received purchase orders can receive stock.',
            ], 422);
        }

        $purchaseOrder = $receivePurchaseOrder->execute(
            $request->user(),
            $purchaseOrder,
            $request->validated('items')
        );

        return response()->json([
            'message' => 'Stock received.',
            'purchase_order' => $purchaseOrder->load(['supplier', 'warehouse', 'createdBy', 'items.product'])
                ->toResource(),
        ]);
    }
}
