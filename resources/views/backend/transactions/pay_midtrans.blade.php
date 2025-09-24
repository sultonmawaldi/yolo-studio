@extends('adminlte::page')

@section('title', 'Pelunasan Midtrans')

@section('content_header')
<h1>Pelunasan Midtrans</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <h4>Transaksi: {{ $transaction->transaction_code }}</h4>
        <p>Total: <strong>Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</strong></p>
        <p>Sudah dibayar: <strong>Rp {{ number_format($transaction->amount, 0, ',', '.') }}</strong></p>
        <p>Sisa bayar: <strong id="remaining-amount">Rp {{ number_format($transaction->total_amount - $transaction->amount, 0, ',', '.') }}</strong></p>

        <button id="pay-button" class="btn btn-primary">Bayar Sekarang</button>
    </div>
</div>
@endsection

@section('js')
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
<script type="text/javascript">
document.getElementById('pay-button').onclick = function() {
    snap.pay('{{ $snapToken }}', {
        onSuccess: function(result) {
            fetch("{{ route('transactions.updateStatus', $transaction->id) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    midtrans_response: result
                })
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    // Update sisa bayar di frontend
                    document.getElementById('remaining-amount').innerText = 'Rp 0';
                    alert("Pelunasan berhasil! Status backend terupdate.");
                    window.location.href = "{{ route('transactions.index') }}";
                } else {
                    alert("Pembayaran berhasil tapi backend gagal update: " + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert("Terjadi error saat update backend!");
            });
        },
        onPending: function(result) {
            alert("Menunggu pembayaran.");
        },
        onError: function(result) {
            alert("Pembayaran gagal.");
        },
        onClose: function() {
            alert("Popup ditutup sebelum pembayaran selesai.");
        }
    });
};
</script>
@endsection
