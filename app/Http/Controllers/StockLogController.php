<?php

namespace App\Http\Controllers;

use App\Actions\CreateStockLog;
use App\Http\Requests\StoreStockLogRequest;
use App\Models\StockLog;
use Illuminate\Support\Facades\Gate;

class StockLogController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', StockLog::class);

        return StockLog::with(['user', 'product'])->get()->toResourceCollection();
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
}