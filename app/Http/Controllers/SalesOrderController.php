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

    public function show(SalesOrder $salesOrder)
    {
        Gate::authorize('view', $salesOrder);

        $salesOrder->load(['warehouse', 'creator', 'items.product']);

        return new SalesOrderResource($salesOrder);
    }

    public function initiatePayment(SalesOrder $salesOrder, InitiateSalesOrderPayment $initiateSalesOrderPayment)
    {
        Gate::authorize('initiatePayment', $salesOrder);

        return $initiateSalesOrderPayment->execute($salesOrder);
    }

    public function cancel(SalesOrder $salesOrder, CancelSalesOrder $cancelSalesOrder)
    {
        Gate::authorize('cancel', $salesOrder);
        $cancelSalesOrder->execute($salesOrder);

        return response()->noContent();
    }
}
