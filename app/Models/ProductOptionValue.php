<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\FormateDate;


class ProductOptionValue extends Model
{
    use HasFactory, FormateDate;

    protected $fillable = [
        'product_option_id',
        'value',
    ];
}
