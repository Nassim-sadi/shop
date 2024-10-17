<?php

namespace App\Traits;

trait FormateDate
{
    public function getCreatedAtAttribute($value)
    {
        return $this->formatDate($value);
    }

    public function getUpdatedAtAttribute($value)
    {
        return $this->formatDate($value);
    }


    public function getDeletedAtAttribute($value)
    {
        return $value ? $this->formatDate($value) : null;
    }

    protected function formatDate($value)
    {
        $format = env('DATE_FORMAT', 'Y-m-d H:i:s'); // default format
        return \Carbon\Carbon::parse($value)->format($format);
    }
}
