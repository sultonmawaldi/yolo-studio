@extends('adminlte::page')

@section('title', 'Tambah Kupon')

@section('content_header')
    <h1>Tambah Kupon</h1>
@stop

@section('content')
<div class="container-fluid">

    <div class="card">
        <div class="card-body">
            <form action="{{ route('coupons.store') }}" method="POST">
                @csrf

                {{-- Kode Kupon --}}
                <div class="form-group">
                    <label for="code">Kode Kupon</label>
                    <input type="text" name="code" id="code" class="form-control" value="{{ old('code') }}" required>
                </div>

                {{-- Jenis --}}
                <div class="form-group">
                    <label for="type">Jenis</label>
                    <select name="type" id="type" class="form-control" required>
                        <option value="fixed" {{ old('type') == 'fixed' ? 'selected' : '' }}>Fixed (Rp)</option>
                        <option value="percentage" {{ old('type') == 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                    </select>
                </div>

                {{-- Nilai --}}
                <div class="form-group">
                    <label for="value">Nilai</label>
                    <input type="number" step="0.01" name="value" id="value" class="form-control" value="{{ old('value') }}" required>
                </div>

                {{-- Minimal Transaksi --}}
                <div class="form-group">
                    <label for="minimum_cart_value">Minimal Transaksi</label>
                    <input type="number" step="0.01" name="minimum_cart_value" id="minimum_cart_value" class="form-control" value="{{ old('minimum_cart_value') }}">
                </div>

                {{-- Tanggal Kadaluarsa --}}
                <div class="form-group">
                    <label for="expiry_date">Tanggal Kadaluarsa</label>
                    <input type="date" name="expiry_date" id="expiry_date" class="form-control" value="{{ old('expiry_date') }}">
                </div>

                {{-- Aktif --}}
                <div class="form-group">
                    <label for="active">Aktif</label>
                    <select name="active" id="active" class="form-control" required>
                        <option value="1" {{ old('active') == '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ old('active') == '0' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>

                {{-- Status --}}
                <div class="form-group">
                    <label for="status">Status</label>
                    <select name="status" id="status" class="form-control" required>
                        <option value="unused" {{ old('status') == 'unused' ? 'selected' : '' }}>Belum Digunakan</option>
                        <option value="used" {{ old('status') == 'used' ? 'selected' : '' }}>Sudah Digunakan</option>
                    </select>
                </div>

                {{-- User --}}
                <div class="form-group">
                    <label for="user_id">User</label>
                    <select name="user_id" id="user_id" class="form-control">
                        <option value="">Semua User</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-success">Simpan</button>
                <a href="{{ route('coupons.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>

</div>
@stop