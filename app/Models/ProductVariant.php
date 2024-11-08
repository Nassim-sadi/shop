<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = ['product_id', 'sku', 'price', 'quantity', 'weight', 'status'];

    public function images()
    {
        return $this->morphMany(ProductImage::class, 'imageable');
    }
}
