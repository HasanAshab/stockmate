<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Enums\StockLogType;
use App\Http\Requests\StoreStockLogRequest;
use App\Models\Product;
use App\Models\StockLog;
use App\Models\User;
use App\Notifications\LowStockAlert;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;

class StockLogController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', StockLog::class);

        return StockLog::all()->toResourceCollection();
    }

    public function store(StoreStockLogRequest $request)
    {
        Gate::authorize('create', StockLog::class);

        $validated = $request->validated();
        $validated['user_id'] = $request->user()->id;

        $stockLog = DB::transaction(function () use ($validated) {
            $log = StockLog::create($validated);

            $product = Product::findOrFail($log->product_id);

            if ($log->type === StockLogType::In) {
                $product->increment('quantity', $log->quantity);
            } else {
                $product->decrement('quantity', $log->quantity);
            }

            $product->refresh();

            if ($product->quantity < $product->reorder_threshold) {
                $admins = User::where('role', Role::Admin)->get();
                Notification::send($admins, new LowStockAlert($product));
            }

            return $log;
        });

        return $stockLog->toResource()
            ->response()
            ->setStatusCode(201);
    }
}
