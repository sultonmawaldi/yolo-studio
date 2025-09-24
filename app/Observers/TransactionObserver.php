<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Models\Coupon;
use Illuminate\Support\Str;

class TransactionObserver
{
    /**
     * Event saat transaksi dibuat
     */
    public function created(Transaction $transaction)
    {
        $this->handlePaidTransaction($transaction);
    }

    /**
     * Event saat transaksi diupdate
     */
    public function updated(Transaction $transaction)
    {
        // Kalau status berubah menjadi "Paid"
        if ($transaction->wasChanged('payment_status') && $transaction->payment_status === 'Paid') {
            $this->handlePaidTransaction($transaction);
        }
    }

    /**
     * Logic saat transaksi sukses Paid
     */
    protected function handlePaidTransaction(Transaction $transaction)
    {
        $user = $transaction->user;

        if (!$user) {
            return;
        }

        // ✅ Jika transaksi pakai kupon, tandai kupon sebagai used
        if ($transaction->coupon_id) {
            $coupon = $transaction->coupon;

            if (
                $coupon &&
                $coupon->status === 'unused' &&
                $coupon->user_id === $user->id
            ) {
                $coupon->update(['status' => 'used']);
            }
        }

        // ✅ Hitung total transaksi Paid user
        $paidCount = $user->transactions()->where('payment_status', 'Paid')->count();

        // ✅ Berikan kupon reward setiap kelipatan 3
        if ($paidCount > 0 && $paidCount % 3 === 0) {
            Coupon::create([
                'user_id'            => $user->id,
                'code'               => 'REWARD-' . strtoupper(Str::random(6)),
                'type'               => 'fixed', // bisa juga percent
                'value'              => 50000, // contoh diskon Rp 50.000
                'minimum_cart_value' => 100000,
                'expiry_date'        => now()->addDays(30),
                'active'             => 1,
                'status'             => 'unused',
            ]);
        }
    }
}
