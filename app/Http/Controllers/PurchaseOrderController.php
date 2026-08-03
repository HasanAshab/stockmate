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
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Support\Facades\Gate;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PurchaseOrderController extends Controller
{
    /**
     * List Purchase Orders
     *
     * Get a paginated list of purchase orders with filtering and sorting.
     */
    #[QueryParameter(name: 'filter[supplier]', description: 'Filter by supplier. Pass supplier ID. Example: `filter[supplier]=5`', example: 5)]
    #[QueryParameter(name: 'filter[warehouse]', description: 'Filter by warehouse. Pass warehouse ID. Example: `filter[warehouse]=2`', example: 2)]
    #[QueryParameter(name: 'filter[creator]', description: 'Filter by creator. Pass user ID. Example: `filter[creator]=1`', example: 1)]
    #[QueryParameter(name: 'filter[product]', description: 'Filter by product in order items. Pass product ID. Example: `filter[product]=10`', example: 10)]
    #[QueryParameter(name: 'filter[status]', description: 'Filter by order status. Allowed values: `draft`, `ordered`, `partially_received`, `received`, `cancelled`. Example: `filter[status]=ordered`', example: 'ordered')]
    #[QueryParameter(name: 'filter[ordered_at][from]', description: 'Filter orders placed from this date. Format: `YYYY-MM-DD` or ISO 8601. Example: `filter[ordered_at][from]=2024-01-01`', example: '2024-01-01')]
    #[QueryParameter(name: 'filter[ordered_at][to]', description: 'Filter orders placed until this date. Format: `YYYY-MM-DD` or ISO 8601. Example: `filter[ordered_at][to]=2024-12-31`', example: '2024-12-31')]
    #[QueryParameter(name: 'filter[received_at][from]', description: 'Filter orders received from this date. Format: `YYYY-MM-DD` or ISO 8601. Example: `filter[received_at][from]=2024-01-01`', example: '2024-01-01')]
    #[QueryParameter(name: 'filter[received_at][to]', description: 'Filter orders received until this date. Format: `YYYY-MM-DD` or ISO 8601. Example: `filter[received_at][to]=2024-12-31`', example: '2024-12-31')]
    #[QueryParameter(name: 'filter[created_at][from]', description: 'Filter orders created from this date. Format: `YYYY-MM-DD` or ISO 8601. Example: `filter[created_at][from]=2024-01-01`', example: '2024-01-01')]
    #[QueryParameter(name: 'filter[created_at][to]', description: 'Filter orders created until this date. Format: `YYYY-MM-DD` or ISO 8601. Example: `filter[created_at][to]=2024-12-31`', example: '2024-12-31')]
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

    /**
     * Create Purchase Order
     *
     * Create a new purchase order with line items.
     */
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

    /**
     * Get Purchase Order
     *
     * Retrieve details of a specific purchase order by ID.
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        Gate::authorize('view', $purchaseOrder);

        $purchaseOrder->load(['supplier', 'warehouse', 'creator', 'items.product']);

        return new PurchaseOrderResource($purchaseOrder);
    }

    /**
     * Update Purchase Order
     *
     * Update an existing purchase order. Only draft orders can be updated.
     */
    public function update(UpdatePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder, UpdatePurchaseOrder $updatePurchaseOrder)
    {
        Gate::authorize('update', $purchaseOrder);

        $purchaseOrder = $updatePurchaseOrder->execute($purchaseOrder, $request->validated());

        $purchaseOrder->load(['supplier', 'warehouse', 'creator', 'items.product']);

        return new PurchaseOrderResource($purchaseOrder);
    }

    /**
     * Mark Purchase Order as Ordered
     *
     * Mark a draft purchase order as ordered (sent to supplier).
     */
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

    /**
     * Cancel Purchase Order
     *
     * Cancel a purchase order. Only draft or ordered purchase orders can be cancelled.
     */
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

    /**
     * Receive Purchase Order
     *
     * Receive stock from a purchase order and update warehouse inventory.
     */
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
