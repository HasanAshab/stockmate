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

/**
 * @group Purchase Order Management
 *
 * APIs for managing purchase orders from suppliers
 *
 * @authenticated
 */
class PurchaseOrderController extends Controller
{
    /**
     * List Purchase Orders
     *
     * Get a paginated list of purchase orders with filtering and sorting.
     *
     * @queryParam filter[supplier] Filter by supplier ID. Example: 1
     * @queryParam filter[warehouse] Filter by warehouse ID. Example: 1
     * @queryParam filter[creator] Filter by creator user ID. Example: 1
     * @queryParam filter[product] Filter by product ID (in order items). Example: 1
     * @queryParam filter[status] Filter by order status (draft, ordered, received, cancelled). Example: ordered
     * @queryParam filter[ordered_at] Filter by ordered date range (format: start,end). Example: 2026-01-01,2026-01-31
     * @queryParam filter[received_at] Filter by received date range. Example: 2026-01-01,2026-01-31
     * @queryParam filter[created_at] Filter by creation date range. Example: 2026-01-01,2026-01-31
     * @queryParam sort Sort by field. Use - prefix for descending. Example: -created_at
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "supplier_id": 1,
     *       "warehouse_id": 1,
     *       "status": "ordered",
     *       "ordered_at": "2026-01-15T10:00:00.000000Z",
     *       "items": [
     *         {
     *           "product_id": 1,
     *           "quantity": 50,
     *           "unit_price": 10.00
     *         }
     *       ]
     *     }
     *   ],
     *   "meta": {},
     *   "links": {}
     * }
     */
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
     *
     * @bodyParam supplier_id integer required The supplier ID. Example: 1
     * @bodyParam warehouse_id integer required The warehouse ID. Example: 1
     * @bodyParam items object[] required Array of order items.
     * @bodyParam items[].product_id integer required The product ID. Example: 1
     * @bodyParam items[].quantity integer required The quantity. Example: 50
     * @bodyParam items[].unit_price number required The unit price. Example: 10.00
     * @bodyParam notes string Optional order notes. Example: Urgent order
     *
     * @response 201 {
     *   "id": 1,
     *   "supplier_id": 1,
     *   "warehouse_id": 1,
     *   "status": "draft",
     *   "created_at": "2026-01-15T10:00:00.000000Z",
     *   "items": [
     *     {
     *       "product_id": 1,
     *       "quantity": 50,
     *       "unit_price": 10.00
     *     }
     *   ]
     * }
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
     *
     * @urlParam purchaseOrder integer required The purchase order ID. Example: 1
     *
     * @response 200 {
     *   "id": 1,
     *   "supplier_id": 1,
     *   "warehouse_id": 1,
     *   "status": "ordered",
     *   "ordered_at": "2026-01-15T10:00:00.000000Z",
     *   "supplier": {
     *     "id": 1,
     *     "name": "Tech Supplier Inc"
     *   },
     *   "items": [
     *     {
     *       "product_id": 1,
     *       "quantity": 50,
     *       "unit_price": 10.00
     *     }
     *   ]
     * }
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
     *
     * @urlParam purchaseOrder integer required The purchase order ID. Example: 1
     *
     * @bodyParam supplier_id integer The supplier ID. Example: 1
     * @bodyParam warehouse_id integer The warehouse ID. Example: 1
     * @bodyParam items object[] Array of order items.
     * @bodyParam items[].product_id integer The product ID. Example: 1
     * @bodyParam items[].quantity integer The quantity. Example: 60
     * @bodyParam items[].unit_price number The unit price. Example: 12.00
     * @bodyParam notes string Optional order notes. Example: Updated notes
     *
     * @response 200 {
     *   "id": 1,
     *   "supplier_id": 1,
     *   "warehouse_id": 1,
     *   "status": "draft",
     *   "items": []
     * }
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
     *
     * @urlParam purchaseOrder integer required The purchase order ID. Example: 1
     *
     * @response 200 {
     *   "id": 1,
     *   "status": "ordered",
     *   "ordered_at": "2026-01-15T11:00:00.000000Z"
     * }
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
     *
     * @urlParam purchaseOrder integer required The purchase order ID. Example: 1
     *
     * @response 200 {
     *   "message": "Purchase order cancelled."
     * }
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
     *
     * @urlParam purchaseOrder integer required The purchase order ID. Example: 1
     *
     * @bodyParam items object[] required Array of received items with quantities.
     * @bodyParam items[].purchase_order_item_id integer required The purchase order item ID. Example: 1
     * @bodyParam items[].received_quantity integer required The quantity received. Example: 50
     *
     * @response 200 {
     *   "message": "Stock received.",
     *   "purchase_order": {
     *     "id": 1,
     *     "status": "received",
     *     "received_at": "2026-01-20T10:00:00.000000Z"
     *   }
     * }
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
