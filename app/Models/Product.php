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
        'currency_id',
        'name',
        'slug',
        'description',
        'long_description',
        'base_price',
        'listing_price',
        'base_quantity',
        "currency",
        'status',
        'featured',
        'weight',
        'weight_unit',
        'thumbnail_image_path',
    ];

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function options()
    {
        return $this->belongsToMany(ProductOption::class, 'product_product_option');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->morphMany(ProductImage::class, 'imageable');
    }

    public function getThumbnailImagePathAttribute($value)
    {
        return asset($value);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
