<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Booking;
use Illuminate\Support\Facades\Log;

class MidtransController extends Controller
{
    public function notificationHandler(Request $request)
{
    $payload = $request->all();
    \Log::info('Midtrans Notification:', $payload);

    $transaction = Transaction::where('midtrans_order_id', $payload['order_id'])->first();
    if(!$transaction) return response()->json(['message' => 'Transaction not found'], 404);

    switch($payload['transaction_status']) {
        case 'capture':
        case 'settlement':
            $transaction->payment_status = 'Paid';
            break;
        case 'Pending':
            $transaction->payment_status = 'Pending';
            break;
        case 'deny':
        case 'cancel':
        case 'expire':
            $transaction->payment_status = 'Failed';
            break;
    }

    $transaction->payload = $payload;
    $transaction->save();

    return response()->json(['message' => 'Notification processed']);
}
}
