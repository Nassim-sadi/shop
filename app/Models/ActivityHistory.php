<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityHistory extends Model
{
    use HasFactory;
    protected $table = 'activity_histories';

    protected $fillable = ['model', 'action', 'data', 'platform', 'browser', 'user_id'];

    protected $casts = [
        'data' => 'object',
    ];

    public function getCreatedAtAttribute($value)
    {
        return $this->formatDate($value);
    }

    public function getUpdatedAtAttribute($value)
    {
        return $this->formatDate($value);
    }

    protected function formatDate($value)
    {
        $format = env('DATE_FORMAT', 'Y-m-d H:i:s'); // default format
        return \Carbon\Carbon::parse($value)->format($format);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
