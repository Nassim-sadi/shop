<?php

namespace App\Models;

use App\Traits\FormateDate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory, FormateDate;
    protected $fillable = ['order_id', 'product_id', 'quantity', 'price'];
    public function options()
    {
        return $this->hasMany(OrderItemOption::class);
    }
}
