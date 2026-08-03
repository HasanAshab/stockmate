<?php

namespace App\Http\Controllers;

use App\Actions\SalesOrder\CancelSalesOrder;
use App\Actions\SalesOrder\CreateSalesOrder;
use App\Actions\SalesOrder\InitiateSalesOrderPayment;
use App\Http\Filters\FiltersDateRange;
use App\Http\Requests\SalesOrder\StoreSalesOrderRequest;
use App\Http\Resources\SalesOrderResource;
use App\Models\SalesOrder;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Support\Facades\Gate;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

class SalesOrderController extends Controller
{
    /**
     * List Sales Orders
     *
     * Get a paginated list of sales orders with filtering and sorting.
     */
    #[QueryParameter(name: 'filter[creator]', description: 'Filter by creator. Pass user ID. Example: `filter[creator]=1`', example: 1)]
    #[QueryParameter(name: 'filter[warehouse]', description: 'Filter by warehouse. Pass warehouse ID. Example: `filter[warehouse]=2`', example: 2)]
    #[QueryParameter(name: 'filter[product]', description: 'Filter by product in order items. Pass product ID. Example: `filter[product]=10`', example: 10)]
    #[QueryParameter(name: 'filter[status]', description: 'Filter by order status. Allowed values: `pending`, `processing`, `paid`, `cancelled`. Example: `filter[status]=paid`', example: 'paid')]
    #[QueryParameter(name: 'filter[created_at][from]', description: 'Filter orders created from this date. Format: `YYYY-MM-DD` or ISO 8601. Example: `filter[created_at][from]=2024-01-01`', example: '2024-01-01')]
    #[QueryParameter(name: 'filter[created_at][to]', description: 'Filter orders created until this date. Format: `YYYY-MM-DD` or ISO 8601. Example: `filter[created_at][to]=2024-12-31`', example: '2024-12-31')]
    #[QueryParameter(name: 'filter[total_amount]', description: 'Filter by total amount. Prefix the value with an operator: `=`, `<`, `>`, `<=`, `>=`, `<>`. Omit the prefix to match equal. Examples: `filter[total_amount]=<1000`, `filter[total_amount]=>500`', example: '>500')]
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
    public function store(StoreSalesOrderRequest $request, CreateSalesOrder $createSalesOrder)
    {
        Gate::authorize('create', SalesOrder::class);

        $salesOrder = $createSalesOrder->execute(
            $request->validated(),
            $request->user()->id,
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
    public function cancel(SalesOrder $salesOrder, CancelSalesOrder $cancelSalesOrder)
    {
        Gate::authorize('cancel', $salesOrder);
        $cancelSalesOrder->execute($salesOrder);

        return response()->noContent();
    }
}
