<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'description',
        'price',
        'price_unit',
        'image_url',
        'seller_id'
    ];

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
}