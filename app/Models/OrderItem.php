<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = ['order_id','seller_id', 'material_id', 'quantity', 'price'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }



       public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
}


