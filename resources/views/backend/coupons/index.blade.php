@extends('adminlte::page')

@section('title', 'Daftar Kupon')

@section('content_header')
    <h1>Daftar Kupon</h1>
@stop

@section('content')
<div class="container-fluid">

    {{-- Notifikasi sukses --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Tombol tambah kupon --}}
    <a href="{{ route('coupons.create') }}" class="btn btn-primary mb-3">
        <i class="fas fa-plus"></i> Tambah Kupon
    </a>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Jenis</th>
                        <th>Nilai</th>
                        <th>Minimal Transaksi</th>
                        <th>Kadaluarsa</th>
                        <th>Aktif</th>
                        <th>Status</th>
                        <th>User</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($coupons as $coupon)
                        <tr>
                            <td>{{ $coupon->code }}</td>
                            <td>{{ ucfirst($coupon->type) }}</td>
                            <td>
                                @if($coupon->type === 'fixed')
                                    Rp {{ number_format($coupon->value, 0, ',', '.') }}
                                @elseif($coupon->type === 'percentage')
                                    {{ $coupon->value }}%
                                @endif
                            </td>
                            <td>
                                {{ $coupon->minimum_cart_value ? 'Rp ' . number_format($coupon->minimum_cart_value, 0, ',', '.') : '-' }}
                            </td>
                            <td>
                                {{ $coupon->expiry_date ? \Carbon\Carbon::parse($coupon->expiry_date)->format('Y-m-d') : '-' }}
                            </td>
                            <td>
                                <span class="badge {{ $coupon->active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $coupon->active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $coupon->status === 'unused' ? 'bg-success' : 'bg-danger' }}">
                                    {{ $coupon->status === 'unused' ? 'Belum Digunakan' : 'Sudah Digunakan' }}
                                </span>
                            </td>
                            <td>
                                {{ $coupon->user ? $coupon->user->name : 'Semua User' }}
                            </td>
                            <td>
                                {{-- Edit --}}
                                <a href="{{ route('coupons.edit', $coupon) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>

                                {{-- Hapus --}}
                                <form action="{{ route('coupons.destroy', $coupon) }}" method="POST" style="display: inline-block" onsubmit="return confirm('Yakin ingin menghapus kupon ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">Belum ada kupon.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Pagination --}}
            <div class="mt-3">
                {{ $coupons->links() }}
            </div>
        </div>
    </div>

</div>
@stop
