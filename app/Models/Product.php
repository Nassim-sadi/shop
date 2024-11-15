<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\FormateDate;

class Product extends Model
{
    use HasFactory, FormateDate;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'long_description',
        'base_price',
        'listing_price',
        'base_quantity',
        'status',
        'featured',
        'thumbnail_image_path',
        'created_by',
        'updated_by',
    ];

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function options()
    {
        return $this->belongsToMany(ProductOption::class, 'product_product_option');
    }

    public function images()
    {
        return $this->morphMany(ProductImage::class, 'imageable');
    }
}
