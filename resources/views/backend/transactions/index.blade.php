@extends('adminlte::page')

@section('title', 'All Transactions')

@section('content_header')
    <h1>All Transactions</h1>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissable">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <a href="{{ route('transactions.create') }}" class="btn btn-primary">+ Add Transaction</a>
        </div>
        <div class="card-body table-responsive">
            <table id="transactionsTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Code</th>
                        <th>Booking</th>
                        <th>User</th>
                        <th>Service</th>
                        <th>Staff</th>
                        <th>Method</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transactions as $transaction)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $transaction->transaction_code }}</td>
                            <td>{{ $transaction->appointment->booking_id ?? '-' }}</td>
                            <td>{{ $transaction->appointment->name ?? '-' }}</td>
                            <td>{{ $transaction->appointment->service->title ?? '-' }}</td>
                            <td>{{ $transaction->appointment->employee->user->name ?? '-' }}</td>
                            <td>{{ $transaction->payment_method ?? '-' }}</td>
                            <td>Rp {{ number_format($transaction->total_amount ?? 0, 0, ',', '.') }}</td>
                            <td>
                                @php
                                    $status = $transaction->payment_status ?? 'Pending';
                                    switch ($status) {
                                        case 'Paid':
                                            $badgeClass = 'success';
                                            break;
                                        case 'DP':
                                            $badgeClass = 'warning';
                                            break;
                                        case 'Failed':
                                            $badgeClass = 'danger';
                                            break;
                                        case 'Pending':
                                        default:
                                            $badgeClass = 'secondary';
                                            break;
                                    }
                                @endphp
                                <span class="badge badge-{{ $badgeClass }}">
                                    {{ $status }}
                                </span>
                            </td>
                            <td>{{ $transaction->created_at->format('d M Y H:i') }}</td>
                            <td>
                                <a href="{{ route('transactions.edit', $transaction->id) }}" class="btn btn-sm btn-info">Edit</a>
                                <form action="{{ route('transactions.destroy', $transaction->id) }}" method="POST" style="display:inline-block;">
                                    @csrf @method('DELETE')
                                    <button type="submit" onclick="return confirm('Delete this transaction?')" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('js')
<script>
    $(function () {
        $('#transactionsTable').DataTable();
        $(".alert").delay(4000).slideUp(300);
    });
</script>
@stop
