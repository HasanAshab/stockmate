<?php

namespace App\Http\Controllers;

use App\Enums\StockLogType;
use App\Http\Requests\StoreStockLogRequest;
use App\Models\Product;
use App\Models\StockLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class StockLogController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', StockLog::class);

        return StockLog::with(['user', 'product'])->get()->toResourceCollection();
    }

    public function store(StoreStockLogRequest $request)
    {
        Gate::authorize('create', StockLog::class);

        $user = $request->user();
        $data = $request->validated();

        $stockLog = DB::transaction(function () use ($user, $data) {
            $product = Product::query()
                ->lockForUpdate()
                ->findOrFail($data['product_id']);

            $type = StockLogType::from($data['type']);
            $quantity = $data['quantity'];

            $newQuantity = $type === StockLogType::In
                ? $product->quantity + $quantity
                : $product->quantity - $quantity;

            if ($newQuantity < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'Not enough stock available.',
                ]);
            }

            $log = $user->stockLogs()->create($data);

            $product->update([
                'quantity' => $newQuantity,
            ]);

            return $log;
        });

        return $stockLog->toResource()
            ->response()
            ->setStatusCode(201);
    }
}