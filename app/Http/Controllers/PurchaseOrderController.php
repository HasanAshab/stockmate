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
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\UrlParam;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

#[Group('Purchase Order Management', 'APIs for managing purchase orders from suppliers')]
#[Authenticated]
class PurchaseOrderController extends Controller
{
    /**
     * List Purchase Orders
     *
     * Get a paginated list of purchase orders with filtering and sorting.
     */
    #[QueryParam('filter[supplier]', 'integer', 'Filter by supplier ID.', example: 1)]
    #[QueryParam('filter[warehouse]', 'integer', 'Filter by warehouse ID.', example: 1)]
    #[QueryParam('filter[creator]', 'integer', 'Filter by creator user ID.', example: 1)]
    #[QueryParam('filter[product]', 'integer', 'Filter by product ID (in order items).', example: 1)]
    #[QueryParam('filter[status]', 'string', 'Filter by order status.', example: 'ordered')]
    #[QueryParam('filter[ordered_at]', 'string', 'Filter by ordered date range (format: start,end).', example: '2026-01-01,2026-01-31')]
    #[QueryParam('filter[received_at]', 'string', 'Filter by received date range.', example: '2026-01-01,2026-01-31')]
    #[QueryParam('filter[created_at]', 'string', 'Filter by creation date range.', example: '2026-01-01,2026-01-31')]
    #[QueryParam('sort', 'string', 'Sort by field. Use - prefix for descending.', example: '-created_at')]
    #[ResponseFromApiResource(PurchaseOrderResource::class, PurchaseOrder::class, collection: true, paginate: 10)]
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
    #[BodyParam('supplier_id', 'integer', 'The supplier ID.', required: true, example: 1)]
    #[BodyParam('warehouse_id', 'integer', 'The warehouse ID.', required: true, example: 1)]
    #[BodyParam('items', 'object[]', 'Array of order items.', required: true)]
    #[BodyParam('items[].product_id', 'integer', 'The product ID.', required: true, example: 1)]
    #[BodyParam('items[].quantity', 'integer', 'The quantity.', required: true, example: 50)]
    #[BodyParam('items[].unit_price', 'number', 'The unit price.', required: true, example: 10.00)]
    #[BodyParam('notes', 'string', 'Optional order notes.', example: 'Urgent order')]
    #[ResponseFromApiResource(PurchaseOrderResource::class, PurchaseOrder::class, status: 201)]
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
    #[UrlParam('purchaseOrder', 'integer', 'The purchase order ID.', required: true, example: 1)]
    #[ResponseFromApiResource(PurchaseOrderResource::class, PurchaseOrder::class, with: ['supplier', 'warehouse', 'creator', 'items.product'])]
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
    #[UrlParam('purchaseOrder', 'integer', 'The purchase order ID.', required: true, example: 1)]
    #[BodyParam('supplier_id', 'integer', 'The supplier ID.', example: 1)]
    #[BodyParam('warehouse_id', 'integer', 'The warehouse ID.', example: 1)]
    #[BodyParam('items', 'object[]', 'Array of order items.')]
    #[BodyParam('items[].product_id', 'integer', 'The product ID.', example: 1)]
    #[BodyParam('items[].quantity', 'integer', 'The quantity.', example: 60)]
    #[BodyParam('items[].unit_price', 'number', 'The unit price.', example: 12.00)]
    #[BodyParam('notes', 'string', 'Optional order notes.', example: 'Updated notes')]
    #[ResponseFromApiResource(PurchaseOrderResource::class, PurchaseOrder::class, with: ['supplier', 'warehouse', 'creator', 'items.product'])]
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
    #[UrlParam('purchaseOrder', 'integer', 'The purchase order ID.', required: true, example: 1)]
    #[ResponseFromApiResource(PurchaseOrderResource::class, PurchaseOrder::class, with: ['supplier', 'warehouse', 'creator', 'items.product'])]
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
    #[UrlParam('purchaseOrder', 'integer', 'The purchase order ID.', required: true, example: 1)]
    #[Response(['message' => 'Purchase order cancelled.'], 200)]
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
    #[UrlParam('purchaseOrder', 'integer', 'The purchase order ID.', required: true, example: 1)]
    #[BodyParam('items', 'object[]', 'Array of received items with quantities.', required: true)]
    #[BodyParam('items[].purchase_order_item_id', 'integer', 'The purchase order item ID.', required: true, example: 1)]
    #[BodyParam('items[].received_quantity', 'integer', 'The quantity received.', required: true, example: 50)]
    #[Response(['message' => 'Stock received.', 'purchase_order' => []], 200)]
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
