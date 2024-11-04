<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'long_description',
        'status',
        'price',
        'stock',
        'discount',
        'featured',
        'sku',
        'main_image_path',
        'thumbnail_image_path',
        'created_by',
        'updated_by',
    ];

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function options()
    {
        return $this->hasMany(ProductOption::class);
    }
}
