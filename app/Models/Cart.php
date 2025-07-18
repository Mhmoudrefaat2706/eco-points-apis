<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
        protected $fillable = ['user_id', 'material_id', 'quantity'];

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
    
}
