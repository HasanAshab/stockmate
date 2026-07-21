<?php

namespace App\Http\Resources\Search;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderSearchResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => 'purchase_order',
            'note' => $this->note,
            'status' => $this->status,
            'ordered_at' => $this->ordered_at,
            'received_at' => $this->received_at,
            'created_at' => $this->created_at,
        ];
    }
}
