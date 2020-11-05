<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderLineRefund extends Model
{
    use HasFactory;

    public function orderLine(){
        return $this->belongsTo(OrderLine::class);
    }
}
