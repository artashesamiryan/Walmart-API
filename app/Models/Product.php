<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    public function type()
    {
        return $this->belongsTo(ProductType::class);
    }

    public function assets()
    {
        return $this->hasMany(ProductAsset::class);
    }

    public function fitments()
    {
        return $this->belongsToMany(Fitment::class);
    }
}
