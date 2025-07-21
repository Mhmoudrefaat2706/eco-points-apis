<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
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
        'status'
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
