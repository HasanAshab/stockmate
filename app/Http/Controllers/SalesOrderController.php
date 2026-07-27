<?php

namespace App\Http\Controllers;

use App\Actions\SalesOrder\CancelSalesOrder;
use App\Actions\SalesOrder\CreateSalesOrder;
use App\Actions\SalesOrder\InitiateSalesOrderPayment;
use App\Http\Filters\FiltersDateRange;
use App\Http\Requests\SalesOrder\StoreSalesOrderRequest;
use App\Http\Resources\SalesOrderResource;
use App\Models\SalesOrder;
use Illuminate\Support\Facades\Gate;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Sales Order Management
 *
 * APIs for managing customer sales orders
 *
 * @authenticated
 */
class SalesOrderController extends Controller
{
    /**
     * List Sales Orders
     *
     * Get a paginated list of sales orders with filtering and sorting.
     *
     * @queryParam filter[creator] Filter by creator user ID. Example: 1
     * @queryParam filter[warehouse] Filter by warehouse ID. Example: 1
     * @queryParam filter[product] Filter by product ID (in order items). Example: 1
     * @queryParam filter[status] Filter by order status (pending, paid, cancelled). Example: paid
     * @queryParam filter[created_at] Filter by creation date range (format: start,end). Example: 2026-01-01,2026-01-31
     * @queryParam filter[total_amount][gt] Filter orders with total amount greater than value. Example: 1000
     * @queryParam filter[total_amount][gte] Filter orders with total amount greater than or equal to value. Example: 100
     * @queryParam filter[total_amount][lt] Filter orders with total amount less than value. Example: 1000
     * @queryParam filter[total_amount][lte] Filter orders with total amount less than or equal to value. Example: 500
     * @queryParam filter[total_amount][eq] Filter orders with exact total amount. Example: 999.99
     * @queryParam sort Sort by field. Use - prefix for descending. Example: -created_at
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "warehouse_id": 1,
     *       "total_amount": 999.99,
     *       "status": "pending",
     *       "created_at": "2026-01-15T10:00:00.000000Z",
     *       "items": [
     *         {
     *           "product_id": 1,
     *           "quantity": 2,
     *           "unit_price": 499.99
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
        Gate::authorize('viewAny', SalesOrder::class);

        $salesOrders = QueryBuilder::for(SalesOrder::class)
            ->with(['warehouse', 'creator', 'items'])
            ->allowedFilters(
                AllowedFilter::belongsTo('creator'),
                AllowedFilter::belongsTo('warehouse'),
                AllowedFilter::belongsTo('product', 'items.product'),
                AllowedFilter::exact('status'),
                AllowedFilter::custom('created_at', new FiltersDateRange),
                AllowedFilter::operator('total_amount', FilterOperator::DYNAMIC),
            )
            ->defaultSort('-created_at')
            ->cursorPaginate(10)
            ->appends(request()->query());

        return SalesOrderResource::collection($salesOrders);
    }

    /**
     * Create Sales Order
     *
     * Create a new sales order with line items. Stock will be reserved from the specified warehouse.
     *
     * @bodyParam warehouse_id integer required The warehouse ID. Example: 1
     * @bodyParam customer_name string The customer name. Example: John Doe
     * @bodyParam customer_email string The customer email. Example: customer@example.com
     * @bodyParam customer_phone string The customer phone. Example: +1234567890
     * @bodyParam items object[] required Array of order items.
     * @bodyParam items[].product_id integer required The product ID. Example: 1
     * @bodyParam items[].quantity integer required The quantity. Example: 2
     * @bodyParam items[].unit_price number required The unit price. Example: 499.99
     * @bodyParam notes string Optional order notes. Example: Express delivery
     *
     * @response 201 {
     *   "id": 1,
     *   "warehouse_id": 1,
     *   "total_amount": 999.98,
     *   "status": "pending",
     *   "created_at": "2026-01-15T10:00:00.000000Z",
     *   "items": [
     *     {
     *       "product_id": 1,
     *       "quantity": 2,
     *       "unit_price": 499.99
     *     }
     *   ]
     * }
     * @response 400 {
     *   "message": "Insufficient stock for product"
     * }
     */
    public function store(StoreSalesOrderRequest $request, CreateSalesOrder $createSalesOrder)
    {
        Gate::authorize('create', SalesOrder::class);

        $salesOrder = $createSalesOrder->execute(
            $request->user(),
            $request->validated(),
        );

        $salesOrder->load(['warehouse', 'creator', 'items.product']);

        return (new SalesOrderResource($salesOrder))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Get Sales Order
     *
     * Retrieve details of a specific sales order by ID.
     *
     * @urlParam salesOrder integer required The sales order ID. Example: 1
     *
     * @response 200 {
     *   "id": 1,
     *   "warehouse_id": 1,
     *   "total_amount": 999.98,
     *   "status": "pending",
     *   "customer_name": "John Doe",
     *   "customer_email": "customer@example.com",
     *   "warehouse": {
     *     "id": 1,
     *     "name": "Main Warehouse"
     *   },
     *   "items": [
     *     {
     *       "product_id": 1,
     *       "quantity": 2,
     *       "unit_price": 499.99
     *     }
     *   ]
     * }
     */
    public function show(SalesOrder $salesOrder)
    {
        Gate::authorize('view', $salesOrder);

        $salesOrder->load(['warehouse', 'creator', 'items.product']);

        return new SalesOrderResource($salesOrder);
    }

    /**
     * Initiate Payment
     *
     * Initiate payment for a sales order using SSLCommerz payment gateway.
     *
     * @urlParam salesOrder integer required The sales order ID. Example: 1
     *
     * @response 200 {
     *   "payment_url": "https://sandbox.sslcommerz.com/gwprocess/v4/gw.php?Q=...",
     *   "session_key": "ABC123XYZ",
     *   "transaction_id": "TXN123456"
     * }
     */
    public function initiatePayment(SalesOrder $salesOrder, InitiateSalesOrderPayment $initiateSalesOrderPayment)
    {
        Gate::authorize('initiatePayment', $salesOrder);

        return $initiateSalesOrderPayment->execute($salesOrder);
    }

    /**
     * Cancel Sales Order
     *
     * Cancel a sales order and restore stock to the warehouse. Only pending orders can be cancelled.
     *
     * @urlParam salesOrder integer required The sales order ID. Example: 1
     *
     * @response 204 scenario="Success"
     * @response 400 {
     *   "message": "Only pending orders can be cancelled"
     * }
     */
    public function cancel(SalesOrder $salesOrder, CancelSalesOrder $cancelSalesOrder)
    {
        Gate::authorize('cancel', $salesOrder);
        $cancelSalesOrder->execute($salesOrder);

        return response()->noContent();
    }
}
