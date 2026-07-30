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

class SalesOrderController extends Controller
{
    /**
     * List Sales Orders
     *
     * Get a paginated list of sales orders with filtering and sorting.
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
