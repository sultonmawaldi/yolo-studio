<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'minimum_cart_value',
        'expiry_date',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'minimum_cart_value' => 'decimal:2',
        'expiry_date' => 'date',
    ];
}
