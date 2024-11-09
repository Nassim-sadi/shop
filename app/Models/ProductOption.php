<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\FormateDate;

class ProductOption extends Model
{
    use HasFactory, FormateDate;

    protected $fillable = [
        'name',
    ];

    public function values()
    {
        return $this->hasMany(ProductOptionValue::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_product_option');
    }
}
