<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\FormateDate;


class ProductImage extends Model
{
    use HasFactory, FormateDate;

    protected $fillable = ['url', 'alt_text'];

    public function imageable()
    {
        return $this->morphTo();
    }
}
