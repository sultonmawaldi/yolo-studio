<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::orderByDesc('created_at')->paginate(10);
        return view('backend.coupons.index', compact('coupons'));
    }

    public function create()
    {
        return view('backend.coupons.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|unique:coupons,code',
            'type' => 'required|in:fixed,percentage',
            'value' => 'required|numeric|min:0',
            'minimum_cart_value' => 'nullable|numeric|min:0',
            'expiry_date' => 'nullable|date|after_or_equal:today',
        ]);

        Coupon::create($data);

        return redirect()->route('coupons.index')->with('success', 'Coupon created.');
    }

    public function edit(Coupon $coupon)
    {
        return view('backend.coupons.edit', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $data = $request->validate([
            'code' => 'required|unique:coupons,code,' . $coupon->id,
            'type' => 'required|in:fixed,percentage',
            'value' => 'required|numeric|min:0',
            'minimum_cart_value' => 'nullable|numeric|min:0',
            'expiry_date' => 'nullable|date|after_or_equal:today',
        ]);

        $coupon->update($data);

        return redirect()->route('coupons.index')->with('success', 'Coupon updated.');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return redirect()->route('coupons.index')->with('success', 'Coupon deleted.');
    }
}
