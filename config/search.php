<?php

use App\Enums\SearchContext;
use App\Http\Resources\Search\ProductSearchResultResource;
use App\Http\Resources\Search\PurchaseOrderSearchResultResource;
use App\Http\Resources\Search\SalesOrderSearchResultResource;
use App\Http\Resources\Search\StockLogSearchResultResource;
use App\Http\Resources\Search\UserSearchResultResource;
use App\Http\Resources\Search\WarehouseSearchResultResource;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\StockLog;
use App\Models\User;
use App\Models\Warehouse;

/*
|--------------------------------------------------------------------------
| Search Configuration
|--------------------------------------------------------------------------
|
| Defines every resource exposed through SearchController@search, both
| for the global "quick search" and for per-screen scoped search boxes.
|
| This file is the single source of truth for what is searchable. The
| controller never resolves a model class from client-supplied input;
| it only ever looks up a client-supplied "scope" slug against the keys
| defined below. Adding a new searchable resource is a config change,
| not a controller change.
|
*/

return [

    /*
    |----------------------------------------------------------------------
    | Searchable Scopes
    |----------------------------------------------------------------------
    |
    | Each key is the "for" slug clients pass to scope a search to one
    | resource (e.g. ?for=products). Omitting "for" searches every scope
    | the current user is authorized to view.
    |
    | Keys:
    |   model    - Eloquent model class being searched. Must implement
    |              the Suggestable contract (see App\Contracts\Suggestable).
    |   ability  - Policy ability checked via the model's Gate before this
    |              scope is queried. Determines both whether a scoped
    |              request is allowed at all, and whether this scope is
    |              silently included in a global search.
    |   resource - JsonResource class used to shape each matched row for
    |              the response.
    |
    */
    'scopes' => [
        'sales_orders' => [
            'model' => SalesOrder::class,
            'ability' => 'viewAny',
            'resource' => SalesOrderSearchResultResource::class,
        ],

        'products' => [
            'model' => Product::class,
            'ability' => 'viewAny',
            'resource' => ProductSearchResultResource::class,
        ],

        'warehouses' => [
            'model' => Warehouse::class,
            'ability' => 'viewAny',
            'resource' => WarehouseSearchResultResource::class,
        ],

        'purchase_orders' => [
            'model' => PurchaseOrder::class,
            'ability' => 'viewAny',
            'resource' => PurchaseOrderSearchResultResource::class,
        ],

        'stock_logs' => [
            'model' => StockLog::class,
            'ability' => 'viewAny',
            'resource' => StockLogSearchResultResource::class,
        ],

        'users' => [
            'model' => User::class,
            'ability' => 'viewAny',
            'resource' => UserSearchResultResource::class,
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | Minimum Query Length
    |----------------------------------------------------------------------
    |
    | Terms shorter than this are rejected before any query runs. The
    | global scope uses a higher minimum than a single-resource scope
    | because it multiplies into one query per authorized resource, so
    | it's more expensive per keystroke and benefits more from cutting
    | off noisy short-term requests.
    |
    */
    'min_length' => [
        SearchContext::Global->value => 3,
        SearchContext::Scoped->value => 2,
    ],

    /*
    |----------------------------------------------------------------------
    | Result Limit Per Scope
    |----------------------------------------------------------------------
    |
    | Scoped search (a single resource's own search box) can afford to
    | show more rows. Global search caps each resource lower so a
    | multi-section dropdown stays scannable.
    |
    */
    'limit' => [
        SearchContext::Global->value => 5,
        SearchContext::Scoped->value => 10,
    ],
];
