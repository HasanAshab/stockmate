<?php

namespace App\Http\Controllers;

use App\Actions\SalesOrder\CancelSalesOrder;
use App\Actions\SalesOrder\CreateSalesOrder;
use App\Actions\SalesOrder\InitiateSalesOrderPayment;
use App\Http\Filters\FiltersDateRange;
use App\Http\Requests\SalesOrder\StoreSalesOrderRequest;
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

        return QueryBuilder::for(SalesOrder::class)
            ->with(['warehouse', 'createdBy', 'items'])
            ->allowedFilters(
                AllowedFilter::belongsTo('created_by'),
                AllowedFilter::belongsTo('warehouse'),
                AllowedFilter::exact('status'),
                AllowedFilter::custom('created_at', new FiltersDateRange),
                AllowedFilter::operator('total_amount', FilterOperator::DYNAMIC),
                AllowedFilter::groupOr('search', [
                    AllowedFilter::partial('customer_name'),
                    AllowedFilter::partial('customer_email'),
                    AllowedFilter::partial('customer_phone'),
                    AllowedFilter::partial('transaction_id'),
                ])
            )
            ->defaultSort('-created_at')
            ->cursorPaginate(10)
            ->appends(request()->query())
            ->toResourceCollection();
    }

    public function store(StoreSalesOrderRequest $request, CreateSalesOrder $createSalesOrder)
    {
        Gate::authorize('create', SalesOrder::class);

        $salesOrder = $createSalesOrder->execute(
            $request->user(),
            $request->validated(),
        );

        return $salesOrder
            ->load(['warehouse', 'createdBy', 'items.product'])
            ->toResource()
            ->response()
            ->setStatusCode(201);
    }

    public function show(SalesOrder $salesOrder)
    {
        Gate::authorize('view', $salesOrder);

        return $salesOrder->load(['warehouse', 'createdBy', 'items.product'])
            ->toResource();
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
