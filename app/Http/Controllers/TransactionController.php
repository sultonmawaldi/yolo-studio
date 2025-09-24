<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Appointment;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Midtrans\Snap;
use Midtrans\Config;

class TransactionController extends Controller
{
    /**
     * Daftar transaksi member
     */
    public function memberIndex()
    {
        $user = Auth::user();

        $transactions = Transaction::where('user_id', $user->id)
            ->latest()
            ->get();

        $coupons = Coupon::where('user_id', $user->id)->get();
        $usedCoupons = Coupon::where('user_id', $user->id)
            ->where('status', 'used')
            ->count();

        return view('frontend.member.dashboard', compact('transactions', 'coupons', 'usedCoupons'));
    }

    public function memberShow(Transaction $transaction)
    {
        $this->authorize('view', $transaction);
        return view('frontend.member.transactions.show', compact('transaction'));
    }

    /**
     * Daftar transaksi admin
     */
    public function index()
    {
        $transactions = Transaction::with('appointment.service', 'appointment.employee.user')
            ->latest()
            ->get();

        return view('backend.transactions.index', compact('transactions'));
    }

    public function create()
    {
        $appointments = Appointment::with('service', 'employee.user')->get();
        return view('backend.transactions.create', compact('appointments'));
    }

    /**
     * Simpan transaksi baru (Admin/Member)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'payment_method' => 'required|string',
            'amount' => 'required|numeric',
            'total_amount' => 'required|numeric',
            'coupon_id' => 'nullable|exists:coupons,id',
        ]);

        $user = Auth::user();

        // ✅ Validasi kupon hanya untuk user ini & status unused
        if (!empty($validated['coupon_id'])) {
            $coupon = Coupon::where('id', $validated['coupon_id'])
                ->where('user_id', $user->id)
                ->where('status', 'unused')
                ->first();

            if (!$coupon) {
                return back()->withErrors(['coupon_id' => 'Kupon tidak valid atau sudah digunakan.']);
            }
        }

        $validated['user_id'] = $user->id;
        $validated['transaction_code'] = 'TRX-' . strtoupper(uniqid());
        $validated['payment_status'] = 'Pending';

        Transaction::create($validated);

        return redirect()->route('transactions.index')->with('success', 'Transaction created successfully.');
    }

    public function edit(Transaction $transaction)
    {
        $appointments = Appointment::with('service', 'employee.user')->get();
        return view('backend.transactions.edit', compact('transaction', 'appointments'));
    }

    public function update(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'payment_method' => 'required|string',
            'amount' => 'required|numeric',
            'total_amount' => 'required|numeric',
            'coupon_id' => 'nullable|exists:coupons,id',
        ]);

        $user = Auth::user();

        // ✅ Validasi kupon saat update
        if (!empty($validated['coupon_id'])) {
            $coupon = Coupon::where('id', $validated['coupon_id'])
                ->where('user_id', $user->id)
                ->where('status', 'unused')
                ->first();

            if (!$coupon) {
                return back()->withErrors(['coupon_id' => 'Kupon tidak valid atau sudah digunakan.']);
            }
        }

        $transaction->update($validated);

        return redirect()->route('transactions.index')->with('success', 'Transaction updated successfully.');
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->delete();
        return redirect()->route('transactions.index')->with('success', 'Transaction deleted successfully.');
    }

    /**
     * Pelunasan via Midtrans
     */
    public function payRemainingMidtrans(Transaction $transaction)
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');

        $params = [
            'transaction_details' => [
                'order_id' => $transaction->transaction_code,
                'gross_amount' => $transaction->total_amount - $transaction->amount,
            ],
            'customer_details' => [
                'first_name' => $transaction->appointment->name,
                'email' => $transaction->appointment->email ?? 'noemail@test.com',
            ]
        ];

        $snapToken = Snap::getSnapToken($params);

        $transaction->update([
            'midtrans_order_id' => $transaction->transaction_code,
        ]);

        return view('backend.transactions.pay_midtrans', compact('transaction', 'snapToken'));
    }

    /**
     * Pelunasan via Cash
     */
    public function payRemainingCash(Transaction $transaction)
    {
        $transaction->update([
            'payment_status' => 'Paid',
            'amount' => $transaction->total_amount,
            'payment_method' => 'Cash'
        ]);

        if ($transaction->appointment) {
            $transaction->appointment->update(['status' => 'Confirmed']);
        }

        return redirect()->route('transactions.index')->with('success', 'Pelunasan tunai berhasil.');
    }

    /**
     * Update status transaksi (callback Midtrans)
     */
    public function updateStatus(Request $request, Transaction $transaction)
    {
        try {
            $validated = $request->validate([
                'midtrans_response' => 'required'
            ]);

            if ($transaction->payment_status === 'DP') {
                $transaction->payment_status = 'Paid';
                $transaction->midtrans_response = json_encode($validated['midtrans_response'], JSON_INVALID_UTF8_IGNORE);
                $transaction->save();

                if ($transaction->appointment && $transaction->appointment->status === 'Processing') {
                    $transaction->appointment->status = 'Confirmed';
                    $transaction->appointment->save();
                }

                return response()->json(['success' => true]);
            }

            return response()->json(['success' => false, 'message' => 'Transaksi bukan DP atau sudah lunas']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->errors()]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
