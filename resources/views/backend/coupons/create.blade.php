@extends('adminlte::page')

@section('title', 'Tambah Kupon')

@section('content_header')
    <h1>Tambah Kupon</h1>
@stop

@section('content')
<div class="container">

    <form action="{{ route('coupons.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="code">Kode Kupon</label>
            <input type="text" name="code" class="form-control" required value="{{ old('code') }}">
        </div>

        <div class="mb-3">
            <label for="type">Jenis Diskon</label>
            <select name="type" class="form-control" required>
                <option value="fixed">Potongan Langsung (Rp)</option>
                <option value="percentage">Persentase (%)</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="value">Nilai Diskon</label>
            <input type="number" name="value" class="form-control" required value="{{ old('value') }}">
        </div>

        <div class="mb-3">
            <label for="minimum_cart_value">Minimal Transaksi</label>
            <input type="number" name="minimum_cart_value" class="form-control" value="{{ old('minimum_cart_value') }}">
        </div>

        <div class="mb-3">
            <label for="expiry_date">Tanggal Kadaluarsa (opsional)</label>
            <input type="date" name="expiry_date" class="form-control" value="{{ old('expiry_date') }}">
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="active" id="active" {{ old('active', true) ? 'checked' : '' }}>
            <label class="form-check-label" for="active">Aktif</label>
        </div>

        <button type="submit" class="btn btn-success">Simpan</button>
        <a href="{{ route('coupons.index') }}" class="btn btn-secondary">Kembali</a>
    </form>

</div>
@stop
