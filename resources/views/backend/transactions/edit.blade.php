@extends('adminlte::page')

@section('title', 'Edit Transaction')

@section('content_header')
    <h1>Edit Transaction</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('transactions.update', $transaction->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label>Appointment</label>
                    <select name="appointment_id" class="form-control" required>
                        <option value="">-- Select Appointment --</option>
                        @foreach($appointments as $appt)
                            <option value="{{ $appt->id }}" {{ $transaction->appointment_id == $appt->id ? 'selected' : '' }}>
                                {{ $appt->booking_id }} - {{ $appt->name }} ({{ optional($appt->service)->title ?? '-' }}) - {{ optional(optional($appt->employee)->user)->name ?? '-' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Payment Method</label>
                    <select name="payment_method" class="form-control" required>
                        <option value="">-- Select Method --</option>
                        <option value="cash" {{ $transaction->payment_method == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="dp" {{ $transaction->payment_method == 'dp' ? 'selected' : '' }}>DP</option>
                        <option value="credit" {{ $transaction->payment_method == 'credit' ? 'selected' : '' }}>Credit</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Total Amount</label>
                    <input type="number" name="total_amount" class="form-control" value="{{ $transaction->total_amount }}" required>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="payment_status" class="form-control" required>
                        <option value="Pending" {{ $transaction->payment_status == 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="DP" {{ $transaction->payment_status == 'DP' ? 'selected' : '' }}>DP</option>
                        <option value="Paid" {{ $transaction->payment_status == 'Paid' ? 'selected' : '' }}>Paid</option>
                        <option value="Failed" {{ $transaction->payment_status == 'Failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-success">Update</button>
                <a href="{{ route('transactions.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@stop