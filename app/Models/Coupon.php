<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'code',
        'discount_type',   // percent / fixed
        'discount_value',
        'status',          // unused / used
        'expired_at',
    ];

    protected $casts = [
        'expired_at' => 'datetime',
    ];

    /**
     * Relasi ke user pemilik kupon
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke transaksi yang menggunakan kupon ini
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'coupon_id');
    }

    /**
     * Cek apakah kupon masih valid
     */
    public function isValid()
    {
        return $this->status === 'unused' &&
            (!$this->expired_at || $this->expired_at->isFuture());
    }
}
