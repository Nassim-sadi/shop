<?php

namespace App\Http\Resources\Admin\ProductOptionValue;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductOptionValueResource extends JsonResource
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
            'value' => $this->value,
            'variants_count' => $this->variants_count,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
