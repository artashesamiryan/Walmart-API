<?php

namespace App\Models;

use App\Models\Order\Order;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderLine extends Model
{
    use HasFactory;

    public function order(){
        return $this->belongsTo(Order::class);
    }

    public function product(){
        return $this->belongsTo(Product::class);
    }

    public function status(){
        return $this->hasOne(OrderLineStatus::class);
    }

    public function fulfilment(){
        return $this->hasMany(OrderLineFulfilment::class);
    }

    public function trackingInfo(){
        return $this->hasOne(OrderLineTrackingInfo::class);
    }

    public function charges(){
        return $this->hasMany(Charge::class);
    }

    public function refund(){
        return $this->hasOne(OrderLineRefund::class);
    }
}
