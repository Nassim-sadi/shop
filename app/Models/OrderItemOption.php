<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\FormateDate;

class OrderItemOption extends Model
{
    use HasFactory, FormateDate;

    protected $fillable = [
        'order_item_id',
        'product_option_value_id',
    ];

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }
}
