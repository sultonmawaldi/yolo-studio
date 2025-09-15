@extends('adminlte::page')

@section('title', 'Edit Kupon')

@section('content_header')
    <h1>Edit Kupon</h1>
@stop

@section('content')
<div class="container">

    <form action="{{ route('coupons.update', $coupon) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Kode Kupon</label>
            <input type="text" name="code" class="form-control" value="{{ old('code', $coupon->code) }}" required>
        </div>

        <div class="mb-3">
            <label>Jenis Diskon</label>
            <select name="type" class="form-control" required>
                <option value="fixed" {{ $coupon->type === 'fixed' ? 'selected' : '' }}>Potongan Langsung (Rp)</option>
                <option value="percentage" {{ $coupon->type === 'percentage' ? 'selected' : '' }}>Persentase (%)</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Nilai Diskon</label>
            <input type="number" name="value" class="form-control" required value="{{ old('value', $coupon->value) }}">
        </div>

        <div class="mb-3">
            <label>Minimal Transaksi</label>
            <input type="number" name="minimum_cart_value" class="form-control" value="{{ old('minimum_cart_value', $coupon->minimum_cart_value) }}">
        </div>

        <div class="mb-3">
            <label>Tanggal Kadaluarsa</label>
            <input type="date" name="expiry_date" class="form-control" value="{{ old('expiry_date', optional($coupon->expiry_date)->format('Y-m-d')) }}">
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="active" id="active" {{ $coupon->active ? 'checked' : '' }}>
            <label class="form-check-label" for="active">Aktif</label>
        </div>

        <button type="submit" class="btn btn-success">Perbarui</button>
        <a href="{{ route('coupons.index') }}" class="btn btn-secondary">Batal</a>
    </form>

</div>
@stop
