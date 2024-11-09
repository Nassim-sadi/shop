<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\FormateDate;


class ProductVariant extends Model
{
    use FormateDate;
    protected $fillable = ['product_id', 'sku', 'price', 'quantity', 'weight', 'status'];

    public function images()
    {
        return $this->morphMany(ProductImage::class, 'imageable');
    }
}
