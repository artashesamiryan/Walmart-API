<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    public function info(){
        return $this->hasOne(OrderInfo::class);
    }

    public function orderLine(){
        return $this->hasMany(OrderLine::class);
    }
}
