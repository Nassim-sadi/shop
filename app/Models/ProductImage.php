<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'option_value_id',
        'url',
        'is_main',
    ];

    public function optionValue()
    {
        return $this->belongsTo(ProductOptionValue::class);
    }
}
