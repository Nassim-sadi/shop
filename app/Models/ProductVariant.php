<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = ['product_id', 'sku', 'price', 'quantity', 'weight', 'status'];


    public function optionValues()
    {
        return $this->belongsToMany(ProductOptionValue::class, 'product_variant_option_value');
    }
}
