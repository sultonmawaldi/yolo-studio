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
        'payment_status', // Pending, DP, Paid
        'midtrans_order_id',
        'payment_result',
        'payload',
        'coupon_id',
    ];

    protected $casts = [
        'payment_result' => 'array',
        'payload' => 'array',
    ];

    /**
     * Relasi ke appointment
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * Relasi ke user pemilik transaksi
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke kupon (jika ada)
     */
    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }

    /**
     * Scope transaksi Paid
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'Paid');
    }

    /**
     * Scope transaksi DP
     */
    public function scopeDp($query)
    {
        return $query->where('payment_status', 'DP');
    }
}
