<?php

namespace App\Http\Resources\Admin\ProductOption;

use App\Http\Resources\Admin\ProductOptionValue\ProductOptionValueResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductOptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'products_count' => $this->products_count,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'values' => ProductOptionValueResource::collection($this->values),
        ];
    }
}
