<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $table = 'currencies';
    public $timestamps = false;

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
