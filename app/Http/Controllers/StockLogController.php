<?php

namespace App\Http\Controllers;

use App\Actions\Product\CreateStockLog;
use App\Enums\StockLogExportFormat;
use App\Enums\StockLogType;
use App\Exports\StockLogExport;
use App\Http\Requests\Product\ExportStockLogRequest;
use App\Http\Requests\Product\StoreStockLogRequest;
use App\Http\Requests\Product\TransferStockRequest;
use App\Models\StockLog;
use App\Models\WarehouseStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class StockLogController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', StockLog::class);

        return QueryBuilder::for(StockLog::class)
            ->allowedFilters(
                AllowedFilter::belongsTo('product'),
                AllowedFilter::belongsTo('user'),
                AllowedFilter::belongsTo('warehouse'),
                AllowedFilter::exact('type'),
                AllowedFilter::callback('from', function ($query, $value) {
                    $query->where('created_at', '>=', $value);
                }),
                AllowedFilter::callback('to', function ($query, $value) {
                    $query->where('created_at', '<=', $value);
                }),
            )
            ->allowedSorts(
                'created_at',
                'quantity',
                'unit_cost',
            )
            ->allowedIncludes('user', 'product', 'warehouse')
            ->defaultSort('-created_at')
            ->paginate(10)
            ->appends(request()->query())
            ->toResourceCollection();
    }

    public function store(StoreStockLogRequest $request, CreateStockLog $createStockLog)
    {
        Gate::authorize('create', StockLog::class);

        $stockLog = $createStockLog->execute(
            $request->user(),
            $request->validated(),
        );

        return $stockLog->toResource()
            ->response()
            ->setStatusCode(201);
    }

    public function export(ExportStockLogRequest $request, StockLogExportFormat $format)
    {
        Gate::authorize('viewAny', StockLog::class);

        $from = $request->validated('from');
        $to = $request->validated('to');
        $dateSlug = match (true) {
            $from && $to => "from_{$from}_to_{$to}",
            $from => "from_{$from}",
            $to => "until_{$to}",
            default => 'all',
        };

        $fileName = "stock-logs-{$dateSlug}.{$format->extension()}";

        return Excel::download(new StockLogExport($from, $to), $fileName, $format->contentType());
    }

    public function transfer(TransferStockRequest $request)
    {
        Gate::authorize('create', StockLog::class);

        $validated = $request->validated();

        return DB::transaction(function () use ($request, $validated) {
            $sourceStock = WarehouseStock::query()
                ->where('warehouse_id', $validated['from_warehouse_id'])
                ->where('product_id', $validated['product_id'])
                ->lockForUpdate()
                ->first();

            if (! $sourceStock || $sourceStock->quantity < $validated['quantity']) {
                throw ValidationException::withMessages([
                    'quantity' => 'Insufficient stock in source warehouse.',
                ]);
            }

            $sourceStock->decrement('quantity', $validated['quantity']);

            $request->user()->stockLogs()->create([
                'product_id' => $validated['product_id'],
                'warehouse_id' => $validated['from_warehouse_id'],
                'type' => StockLogType::Out,
                'quantity' => $validated['quantity'],
                'unit_cost' => null,
                'note' => 'Transfer to warehouse #'.$validated['to_warehouse_id'].($validated['note'] ? ': '.$validated['note'] : ''),
            ]);

            $destinationStock = WarehouseStock::firstOrCreate(
                [
                    'warehouse_id' => $validated['to_warehouse_id'],
                    'product_id' => $validated['product_id'],
                ],
                [
                    'quantity' => 0,
                    'reorder_threshold' => 10,
                ]
            );

            $destinationStock->increment('quantity', $validated['quantity']);

            $request->user()->stockLogs()->create([
                'product_id' => $validated['product_id'],
                'warehouse_id' => $validated['to_warehouse_id'],
                'type' => StockLogType::In,
                'quantity' => $validated['quantity'],
                'unit_cost' => null,
                'note' => 'Transfer from warehouse #'.$validated['from_warehouse_id'].($validated['note'] ? ': '.$validated['note'] : ''),
            ]);

            return response()->json(['message' => 'Stock transferred successfully.']);
        });
    }
}
