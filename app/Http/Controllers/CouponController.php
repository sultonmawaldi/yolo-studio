<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    /**
     * Tampilkan daftar kupon.
     */
    public function index()
    {
        $coupons = Coupon::with('user')->orderByDesc('created_at')->paginate(10);
        return view('backend.coupons.index', compact('coupons'));
    }

    /**
     * Tampilkan form tambah kupon.
     */
    public function create()
    {
        $users = User::all();
        return view('backend.coupons.create', compact('users'));
    }

    /**
     * Simpan kupon baru.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|unique:coupons,code',
            'type' => 'required|in:fixed,percentage',
            'value' => 'required|numeric|min:0',
            'minimum_cart_value' => 'nullable|numeric|min:0',
            'expiry_date' => 'nullable|date',
            'user_id' => 'nullable|exists:users,id',
            'active' => 'required|in:0,1',
        ]);

        // Status selalu 'unused', tidak bisa diisi dari form
        $data['status'] = 'unused';

        Coupon::create($data);

        return redirect()->route('coupons.index')->with('success', 'Kupon berhasil dibuat.');
    }

    /**
     * Tampilkan form edit kupon.
     */
    public function edit(Coupon $coupon)
    {
        $users = User::all();
        return view('backend.coupons.edit', compact('coupon', 'users'));
    }

    /**
     * Update kupon.
     */
    public function update(Request $request, Coupon $coupon)
    {
        $data = $request->validate([
            'code' => 'required|unique:coupons,code,' . $coupon->id,
            'type' => 'required|in:fixed,percentage',
            'value' => 'required|numeric|min:0',
            'minimum_cart_value' => 'nullable|numeric|min:0',
            'expiry_date' => 'nullable|date',
            'user_id' => 'nullable|exists:users,id',
            'active' => 'required|in:0,1',
            'status' => 'required|in:unused,used,expired', // admin bisa ubah status
        ]);

        $coupon->update($data);

        return redirect()->route('coupons.index')->with('success', 'Kupon berhasil diperbarui.');
    }

    /**
     * Hapus kupon.
     */
    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return redirect()->route('coupons.index')->with('success', 'Kupon berhasil dihapus.');
    }
}
