<?php

namespace App\Models;

use App\Traits\FormateDate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityHistory extends Model
{
    use FormateDate;
    use HasFactory;
    protected $table = 'activity_histories';

    protected $fillable = ['model', 'action', 'data', 'platform', 'browser', 'user_id'];

    protected $casts = [
        'data' => 'object',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
