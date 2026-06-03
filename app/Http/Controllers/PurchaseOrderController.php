<?php

namespace App\Http\Controllers;

use App\Enums\PurchaseOrderStatus;
use App\Enums\StockLogType;
use App\Http\Filters\FiltersDateRange;
use App\Http\Requests\PurchaseOrder\ReceivePurchaseOrderRequest;
use App\Http\Requests\PurchaseOrder\StorePurchaseOrderRequest;
use App\Http\Requests\PurchaseOrder\UpdatePurchaseOrderRequest;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\WarehouseStock;
use Illuminate\Support\Facades\DB;
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

    public function store(StorePurchaseOrderRequest $request)
    {
        Gate::authorize('create', PurchaseOrder::class);

        $purchaseOrder = DB::transaction(function () use ($request) {
            $purchaseOrder = PurchaseOrder::create([
                'supplier_id' => $request->validated('supplier_id'),
                'warehouse_id' => $request->validated('warehouse_id'),
                'note' => $request->validated('note'),
                'created_by' => $request->user()->id,
            ]);

            foreach ($request->validated('items') as $item) {
                $purchaseOrder->items()->create([
                    'product_id' => $item['product_id'],
                    'ordered_quantity' => $item['ordered_quantity'],
                    'unit_cost' => $item['unit_cost'],
                ]);
            }

            return $purchaseOrder;
        });

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

    public function update(UpdatePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder)
    {
        Gate::authorize('update', $purchaseOrder);

        if ($purchaseOrder->status === PurchaseOrderStatus::Received || $purchaseOrder->status === PurchaseOrderStatus::Cancelled) {
            return response()->json([
                'message' => 'This purchase order can no longer be edited.',
            ], 422);
        }

        DB::transaction(function () use ($request, $purchaseOrder) {
            $purchaseOrder->update($request->safe()->except('items'));

            if ($request->has('items')) {
                $purchaseOrder->items()->delete();

                foreach ($request->validated('items') as $item) {
                    $purchaseOrder->items()->create([
                        'product_id' => $item['product_id'],
                        'ordered_quantity' => $item['ordered_quantity'],
                        'unit_cost' => $item['unit_cost'],
                    ]);
                }
            }
        });

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

        if ($purchaseOrder->status === PurchaseOrderStatus::PartiallyReceived || $purchaseOrder->status === PurchaseOrderStatus::Received) {
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

    public function receive(ReceivePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder)
    {
        Gate::authorize('receive', $purchaseOrder);

        if ($purchaseOrder->status !== PurchaseOrderStatus::Ordered && $purchaseOrder->status !== PurchaseOrderStatus::PartiallyReceived) {
            return response()->json([
                'message' => 'Only ordered or partially received purchase orders can receive stock.',
            ], 422);
        }

        DB::transaction(function () use ($request, $purchaseOrder) {
            foreach ($request->validated('items') as $item) {
                $purchaseOrderItem = PurchaseOrderItem::with('product')->findOrFail($item['purchase_order_item_id']);

                $outstanding = $purchaseOrderItem->ordered_quantity - $purchaseOrderItem->received_quantity;

                if ($item['quantity_received'] > $outstanding) {
                    throw new \Exception("Received quantity exceeds outstanding quantity for product {$purchaseOrderItem->product->name}.");
                }

                $purchaseOrderItem->increment('received_quantity', $item['quantity_received']);

                $warehouseStock = WarehouseStock::firstOrCreate(
                    [
                        'warehouse_id' => $purchaseOrder->warehouse_id,
                        'product_id' => $purchaseOrderItem->product_id,
                    ],
                    ['quantity' => 0]
                );

                $warehouseStock->increment('quantity', $item['quantity_received']);

                $request->user()->stockLogs()->create([
                    'product_id' => $purchaseOrderItem->product_id,
                    'warehouse_id' => $purchaseOrder->warehouse_id,
                    'type' => StockLogType::In,
                    'quantity' => $item['quantity_received'],
                    'unit_cost' => $purchaseOrderItem->unit_cost,
                    'note' => "Received from PO #{$purchaseOrder->id}",
                ]);
            }

            $allFullyReceived = $purchaseOrder->items()->get()->every(fn ($item) => $item->isFullyReceived());

            if ($allFullyReceived) {
                $purchaseOrder->update([
                    'status' => PurchaseOrderStatus::Received,
                    'received_at' => now(),
                ]);
            } else {
                $purchaseOrder->update([
                    'status' => PurchaseOrderStatus::PartiallyReceived,
                ]);
            }
        });

        return response()->json([
            'message' => 'Stock received.',
            'purchase_order' => $purchaseOrder->load(['supplier', 'warehouse', 'createdBy', 'items.product'])
                ->toResource(),
        ]);
    }
}
