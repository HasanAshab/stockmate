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

        return StockLog::with(['user', 'product'])->get()->toResourceCollection();
    }

    public function store(StoreStockLogRequest $request)
    {
        Gate::authorize('create', StockLog::class);

        $data = $request->validated();
        $userId = $request->user()->id;

        $stockLog = DB::transaction(function () use ($data, $userId) {
            $product = Product::query()
                ->lockForUpdate()
                ->findOrFail($data['product_id']);

            $type = StockLogType::from($data['type']);
            $quantity = $data['quantity'];

            $beforeQuantity = $product->quantity;

            if ($type === StockLogType::Out && $beforeQuantity < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'Not enough stock available.',
                ]);
            }

            $log = StockLog::create([
                ...$data,
                'user_id' => $userId,
            ]);

            if ($type === StockLogType::In) {
                $product->increment('quantity', $quantity);
            } else {
                $product->decrement('quantity', $quantity);
            }

            $product->refresh();

            $shouldNotifyLowStock =
                $beforeQuantity >= $product->reorder_threshold &&
                $product->quantity < $product->reorder_threshold;

            DB::afterCommit(function () use ($shouldNotifyLowStock, $product) {
                if (! $shouldNotifyLowStock) {
                    return;
                }

                $admins = User::query()
                    ->where('role', Role::Admin)
                    ->get();

                Notification::send($admins, new LowStockAlert($product));
            });

            return $log->load(['user', 'product']);
        });

        return $stockLog->toResource()
            ->response()
            ->setStatusCode(201);
    }
}