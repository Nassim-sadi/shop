<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\FormateDate;

class Category extends Model
{
    use HasFactory, FormateDate;

    protected $fillable = [
        'name',
        'slug',
        'image',
        'description',
        'order',
        'status',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }


    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->with('children');
    }

    // A category can have one parent category
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function getImageAttribute($value)
    {
        if (is_null($value)) {
            return $value;
        }
        return asset('/storage/images/categories/' . $value);
    }
}
