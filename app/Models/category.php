<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class category extends Model
{
    public function materials()
    {
        return $this->hasMany(Material::class);
    }
}
