<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'appointment_id',
        'transaction_code',
        'payment_method',
        'amount',
        'total_amount',
        'payment_status',
        'midtrans_order_id',
        'payment_result',
        'payload',
    ];

    protected $casts = [
        'payment_result' => 'array',
        'payload' => 'array',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}