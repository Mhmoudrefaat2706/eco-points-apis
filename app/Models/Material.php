<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_id',
        'description',
        'price',
        'price_unit',
        'image_url',
        'quantity',
        'seller_id'
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    // Relationship with Category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Relationship with Seller (User)
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
    
}
