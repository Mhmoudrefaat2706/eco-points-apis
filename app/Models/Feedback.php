<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Feedback extends Model
{
    use HasFactory;

    protected $table = 'feedback';
    public $timestamps = true; // Change to true to use created_at and updated_at

    protected $fillable = [
        'seller_id',
        'buyer_id',
        'rating',
        'comment'
    ];

    protected $with = ['seller', 'buyer']; // Eager load by default

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id')
            ->select(['id', 'first_name', 'last_name']);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id')
            ->select(['id', 'first_name', 'last_name']);
    }
}
