<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getCreatedAtAttribute($value)
    {
        return $this->formatDate($value);
    }

    public function getUpdatedAtAttribute($value)
    {
        return $this->formatDate($value);
    }

    public function getEmailVerifiedAtAttribute($value)
    {
        return $value ? $this->formatDate($value) : null;
    }

    protected function formatDate($value)
    {
        $format = env('DATE_FORMAT', 'Y-m-d H:i:s'); // default format
        return \Carbon\Carbon::parse($value)->format($format);
    }



    public function sendPasswordResetNotification($token)
    {

        $url = config('app.frontend_url') . '/auth/reset-password?token=' . $token . '&email=' . urlencode($this->email);

        $this->notify(new ResetPasswordNotification($url));
    }

    /**
     * Get the user's image.
     *
     * @param  string  $value
     * @return string
     */
    public function getImageAttribute($value)
    {
        if (is_null($value)) {
            return $value;
        }
        return asset('/storage/images/profile/' . $value);
    }
}
