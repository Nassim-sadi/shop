<?php

namespace App\Http\Resources\Admin\Product;

use App\Http\Resources\Admin\Product\Options\ProductOptionsResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'category_id' => $this->category_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'long_description' => $this->long_description,
            'status' => $this->status,
            'featured' => $this->featured,
            'base_price' => $this->base_price,
            'currency' => $this->currency,
            'base_quantity' => $this->base_quantity,
            'listing_price' => $this->listing_price,
            'thumbnail_image_path' => $this->thumbnail_image_path,
            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name
            ],
            "weight" => $this->weight,
            "weight_unit" => $this->weight_unit,
            'options' => ProductOptionsResource::collection($this->options),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            "variants_count" => $this->variants_count,
        ];
    }
}
