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
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\UrlParam;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

#[Group('Sales Order Management', 'APIs for managing customer sales orders')]
#[Authenticated]
class SalesOrderController extends Controller
{
    /**
     * List Sales Orders
     *
     * Get a paginated list of sales orders with filtering and sorting.
     */
    #[QueryParam('filter[creator]', 'integer', 'Filter by creator user ID.', example: 1)]
    #[QueryParam('filter[warehouse]', 'integer', 'Filter by warehouse ID.', example: 1)]
    #[QueryParam('filter[product]', 'integer', 'Filter by product ID (in order items).', example: 1)]
    #[QueryParam('filter[status]', 'string', 'Filter by order status.', example: 'paid')]
    #[QueryParam('filter[created_at]', 'string', 'Filter by creation date range (format: start,end).', example: '2026-01-01,2026-01-31')]
    #[QueryParam('filter[total_amount][gt]', 'number', 'Filter orders with total amount greater than value.', example: 1000)]
    #[QueryParam('filter[total_amount][gte]', 'number', 'Filter orders with total amount greater than or equal to value.', example: 100)]
    #[QueryParam('filter[total_amount][lt]', 'number', 'Filter orders with total amount less than value.', example: 1000)]
    #[QueryParam('filter[total_amount][lte]', 'number', 'Filter orders with total amount less than or equal to value.', example: 500)]
    #[QueryParam('filter[total_amount][eq]', 'number', 'Filter orders with exact total amount.', example: 999.99)]
    #[QueryParam('sort', 'string', 'Sort by field. Use - prefix for descending.', example: '-created_at')]
    #[ResponseFromApiResource(SalesOrderResource::class, SalesOrder::class, collection: true, paginate: 10)]
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
     */
    #[BodyParam('warehouse_id', 'integer', 'The warehouse ID.', required: true, example: 1)]
    #[BodyParam('customer_name', 'string', 'The customer name.', example: 'John Doe')]
    #[BodyParam('customer_email', 'string', 'The customer email.', example: 'customer@example.com')]
    #[BodyParam('customer_phone', 'string', 'The customer phone.', example: '+1234567890')]
    #[BodyParam('items', 'object[]', 'Array of order items.', required: true)]
    #[BodyParam('items[].product_id', 'integer', 'The product ID.', required: true, example: 1)]
    #[BodyParam('items[].quantity', 'integer', 'The quantity.', required: true, example: 2)]
    #[BodyParam('items[].unit_price', 'number', 'The unit price.', required: true, example: 499.99)]
    #[BodyParam('notes', 'string', 'Optional order notes.', example: 'Express delivery')]
    #[ResponseFromApiResource(SalesOrderResource::class, SalesOrder::class, status: 201)]
    #[Response(['message' => 'Insufficient stock for product'], 400)]
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
     */
    #[UrlParam('salesOrder', 'integer', 'The sales order ID.', required: true, example: 1)]
    #[ResponseFromApiResource(SalesOrderResource::class, SalesOrder::class, with: ['warehouse', 'creator', 'items.product'])]
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
     */
    #[UrlParam('salesOrder', 'integer', 'The sales order ID.', required: true, example: 1)]
    #[Response(['payment_url' => 'https://sandbox.sslcommerz.com/gwprocess/v4/gw.php?Q=...', 'session_key' => 'ABC123XYZ', 'transaction_id' => 'TXN123456'], 200)]
    public function initiatePayment(SalesOrder $salesOrder, InitiateSalesOrderPayment $initiateSalesOrderPayment)
    {
        Gate::authorize('initiatePayment', $salesOrder);

        return $initiateSalesOrderPayment->execute($salesOrder);
    }

    /**
     * Cancel Sales Order
     *
     * Cancel a sales order and restore stock to the warehouse. Only pending orders can be cancelled.
     */
    #[UrlParam('salesOrder', 'integer', 'The sales order ID.', required: true, example: 1)]
    #[Response([], 204, 'Success')]
    #[Response(['message' => 'Only pending orders can be cancelled'], 400)]
    public function cancel(SalesOrder $salesOrder, CancelSalesOrder $cancelSalesOrder)
    {
        Gate::authorize('cancel', $salesOrder);
        $cancelSalesOrder->execute($salesOrder);

        return response()->noContent();
    }
}
