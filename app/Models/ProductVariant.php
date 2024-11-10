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

    public function optionValues()
    {
        return $this->belongsToMany(ProductOptionValue::class, 'product_variant_option_value', 'product_variant_id', 'product_option_value_id');
    }
}
