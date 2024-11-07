<?php

namespace App\Http\Resources\Admin\Product;

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
            'status' => $this->status,
            'price' => $this->price,
            'cost' => $this->cost,
            'weight' => $this->weight,
            'long_description' => $this->long_description,
            'stock' => $this->stock,
            'discount' => $this->discount,
            'featured' => $this->featured,
            'sku' => $this->sku,
            'main_image_path' => $this->main_image_path,
            'thumbnail_image_path' => $this->thumbnail_image_path,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
