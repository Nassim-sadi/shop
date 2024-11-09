<?php

namespace App\Models;

use App\Traits\FormateDate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory, FormateDate;

    protected $fillable = [
        'user_id',
        'address',
        'city',
        'state',
        'country',
        'zip_code',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
