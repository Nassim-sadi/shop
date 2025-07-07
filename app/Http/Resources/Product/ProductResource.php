<?php

namespace App\Http\Resources\Product;

use App\Http\Resources\Variants\ProductVariantResource;
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
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'long_description' => $this->long_description,
            'price' => $this->price,
            'currency' => $this->currency,
            'images' =>  $this->images,
            'category' =>
            [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug
            ],
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'listing_price' => $this->listing_price,
            'weight' => $this->weight,
            'weight_unit' => $this->weight_unit,
            'featured' => $this->featured,
            'thumbnail_image_path' => $this->thumbnail_image_path,
            'variants' => ProductVariantResource::collection($this->variants),

        ];
    }
}
