<?php

namespace App\Http\Resources\Variants;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
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
            'product_id' => $this->product_id,
            "price" => $this->price,
            "quantity" => $this->quantity,
            "status" => $this->status,
            "discount" => $this->discount,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "option_values" => $this->optionValues,
        ];
    }
}
