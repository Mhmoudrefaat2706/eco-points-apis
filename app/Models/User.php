<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'points_balance',
        'is_active',
        'role',
        'paypal_client_secret',
        'paypal_client_id',
        'status',
        'profile_image'
    ];

    protected $casts = [
        'status' => 'string',
    ];

    protected $hidden = ['password', 'remember_token'];
    protected $attributes = [
        'status' => 'active',
        'is_active' => true,
        'points_balance' => 0,
    ];
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
