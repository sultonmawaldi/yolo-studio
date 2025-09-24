<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;

class MidtransController extends Controller
{
    public function notificationHandler(Request $request)
{
    Config::$serverKey    = config('midtrans.server_key');
    Config::$isProduction = config('midtrans.is_production');
    Config::$isSanitized  = config('midtrans.is_sanitized');
    Config::$is3ds        = config('midtrans.is_3ds');

    $notification = new \Midtrans\Notification();

    $orderId  = $notification->order_id;
    $status   = $notification->transaction_status;

    $transaction = Transaction::where('transaction_code', $orderId)->first();
    if (! $transaction) {
        return response()->json(['message' => 'Transaction not found'], 404);
    }

    if ($status == 'capture' || $status == 'settlement') {
        // kalau belum lunas berarti DP
        if ($transaction->amount < $transaction->total_amount) {
            $transaction->update([
                'payment_status' => 'DP',
            ]);
            $transaction->appointment->update([
                'status' => 'Processing',
            ]);
        } else {
            // sudah lunas
            $transaction->update([
                'payment_status' => 'Paid',
                'amount'         => $transaction->total_amount,
            ]);
            $transaction->appointment->update([
                'status' => 'Confirmed',
            ]);
        }
    } elseif (in_array($status, ['deny','expire','cancel'])) {
        $transaction->update(['payment_status' => 'Failed']);
        $transaction->appointment->update(['status' => 'Cancelled']);
    }

    return response()->json(['message' => 'Notification processed']);
}

}
