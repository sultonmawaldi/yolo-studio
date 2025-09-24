@extends('layouts.app')

@section('title', 'Member Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6 space-y-6">

    {{-- Ringkasan --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        {{-- Total Transaksi --}}
        <div class="flex items-center p-5 bg-white rounded-xl shadow hover:shadow-lg transition">
            <div class="flex-shrink-0 w-14 h-14 flex items-center justify-center rounded-lg bg-indigo-500 text-white text-2xl">
                <i class="fas fa-wallet"></i>
            </div>
            <div class="ml-4">
                <div class="text-sm text-gray-500 font-semibold">Total Transaksi</div>
                <div class="text-2xl font-bold text-gray-800">{{ $transactions->count() }}</div>
            </div>
        </div>

        {{-- Kupon Digunakan --}}
        <div class="flex items-center p-5 bg-white rounded-xl shadow hover:shadow-lg transition">
            <div class="flex-shrink-0 w-14 h-14 flex items-center justify-center rounded-lg bg-green-500 text-white text-2xl">
                <i class="fas fa-ticket-alt"></i>
            </div>
            <div class="ml-4">
                <div class="text-sm text-gray-500 font-semibold">Kupon Digunakan</div>
                <div class="text-2xl font-bold text-gray-800">{{ $usedCoupons ?? 0 }}</div>
            </div>
        </div>
    </div>

    {{-- Kupon Aktif --}}
<div class="bg-white rounded-xl shadow p-6">
    <h2 class="text-lg font-bold text-gray-700 mb-4">🎟️ Kupon Aktif Anda</h2>

    @if(isset($coupons) && $coupons->isNotEmpty())
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            @foreach($coupons as $coupon)
                <div class="p-4 bg-indigo-50 text-indigo-800 rounded-lg shadow hover:shadow-md transition">
                    <p class="font-semibold text-lg">{{ $coupon->code }}</p>
                    
                    <p class="mt-1">
                        @if($coupon->type === 'fixed')
                            💰 Rp {{ number_format($coupon->value, 0, ',', '.') }}
                        @else
                            💸 {{ $coupon->value }}%
                        @endif
                    </p>

                    <p class="text-sm text-gray-500 mt-1">
                        Kadaluarsa: {{ $coupon->expiry_date ? \Carbon\Carbon::parse($coupon->expiry_date)->format('d M Y') : 'Tidak ada' }}
                    </p>

                    <p class="text-sm mt-2">
                        Status: 
                        <span class="px-2 py-1 rounded-full text-xs font-semibold 
                            {{ $coupon->status === 'unused' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ $coupon->status === 'unused' ? 'Belum Digunakan' : 'Sudah Digunakan' }}
                        </span>
                    </p>
                </div>
            @endforeach
        </div>
    @else
        <div class="p-4 bg-gray-50 text-gray-600 rounded-lg text-center">
            Anda belum memiliki kupon aktif. 🚫
        </div>
    @endif
</div>



    {{-- Transaksi Terakhir --}}
    @if($transactions->isNotEmpty())
        @php $latest = $transactions->first(); @endphp
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="text-lg font-bold text-gray-700 mb-4">📌 Transaksi Terakhir</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
                <div>
                    <p class="text-gray-500 text-sm">Kode</p>
                    <p class="text-indigo-600 font-semibold">{{ $latest->transaction_code }}</p>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Total</p>
                    <p class="font-semibold">Rp {{ number_format($latest->total_amount, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Pembayaran</p>
                    <span class="px-2 py-1 rounded-full text-xs font-semibold
                        @if($latest->payment_status === 'Paid') bg-green-100 text-green-700
                        @elseif($latest->payment_status === 'DP') bg-yellow-100 text-yellow-700
                        @else bg-gray-100 text-gray-600 @endif">
                        {{ $latest->payment_status }}
                    </span>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Booking</p>
                    <span class="px-2 py-1 rounded-full text-xs font-semibold
                        @if($latest->appointment_status === 'Confirmed') bg-green-100 text-green-700
                        @elseif($latest->appointment_status === 'Pending') bg-yellow-100 text-yellow-700
                        @else bg-gray-100 text-gray-600 @endif">
                        {{ $latest->appointment_status }}
                    </span>
                </div>
            </div>
        </div>
    @endif

    {{-- Riwayat Transaksi --}}
    <div class="bg-white rounded-xl shadow p-6" x-data="{ open: true }">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-bold text-gray-700">💳 Riwayat Transaksi</h2>
            <button @click="open = !open" class="text-sm text-indigo-600 hover:underline">
                <span x-show="open">Sembunyikan</span>
                <span x-show="!open">Tampilkan</span>
            </button>
        </div>

        <div x-show="open" x-transition>
            @if($transactions->isEmpty())
                <div class="p-4 bg-blue-50 text-blue-700 rounded">
                    Belum ada transaksi yang tercatat.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left border">
                        <thead class="bg-gray-100 text-gray-600 uppercase">
                            <tr>
                                <th class="px-4 py-2">Kode</th>
                                <th class="px-4 py-2">Metode</th>
                                <th class="px-4 py-2">Jumlah</th>
                                <th class="px-4 py-2">Total</th>
                                <th class="px-4 py-2">Status Bayar</th>
                                <th class="px-4 py-2">Tanggal</th>
                                <th class="px-4 py-2">Jam</th>
                                <th class="px-4 py-2">Status Booking</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $trx)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-4 py-2 font-semibold text-indigo-600">{{ $trx->transaction_code }}</td>
                                    <td class="px-4 py-2">{{ $trx->payment_method ?? '-' }}</td>
                                    <td class="px-4 py-2">Rp {{ number_format($trx->amount, 0, ',', '.') }}</td>
                                    <td class="px-4 py-2">Rp {{ number_format($trx->total_amount, 0, ',', '.') }}</td>
                                    <td class="px-4 py-2">
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold
                                            @if($trx->payment_status === 'Paid') bg-green-100 text-green-700
                                            @elseif($trx->payment_status === 'DP') bg-yellow-100 text-yellow-700
                                            @else bg-gray-100 text-gray-600 @endif">
                                            {{ $trx->payment_status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">{{ \Carbon\Carbon::parse($trx->booking_date)->format('d M Y') }}</td>
                                    <td class="px-4 py-2">{{ $trx->booking_time }}</td>
                                    <td class="px-4 py-2">
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold
                                            @if($trx->appointment_status === 'Confirmed') bg-green-100 text-green-700
                                            @elseif($trx->appointment_status === 'Pending') bg-yellow-100 text-yellow-700
                                            @else bg-gray-100 text-gray-600 @endif">
                                            {{ $trx->appointment_status }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
