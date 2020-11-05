<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalmartToken extends Model
{
    use HasFactory;

    protected $dates = ['expires_at'];
}
