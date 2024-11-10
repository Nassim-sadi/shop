<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\FormateDate;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductOptionValue extends Model
{
    use HasFactory, FormateDate, SoftDeletes;

    protected $fillable = [
        'product_option_id',
        'value',
    ];



    public function variants()
    {
        return $this->belongsToMany(ProductVariant::class, 'product_variant_option_value', 'product_option_value_id', 'product_variant_id');
    }
}
