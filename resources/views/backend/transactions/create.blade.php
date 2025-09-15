@extends('adminlte::page')

@section('title', 'Create Transaction')

@section('content_header')
    <h1>Create Transaction</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('transactions.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label>Appointment</label>
                    <select name="appointment_id" class="form-control" required>
                        <option value="">-- Select Appointment --</option>
                        @foreach($appointments as $appt)
                            <option value="{{ $appt->id }}">
                                {{ $appt->booking_id }} - {{ $appt->name }} ({{ optional($appt->service)->title ?? '-' }}) - {{ optional(optional($appt->employee)->user)->name ?? '-' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Payment Method</label>
                    <select name="payment_method" class="form-control" required>
                        <option value="">-- Select Method --</option>
                        <option value="cash">Cash</option>
                        <option value="dp">DP</option>
                        <option value="credit">Credit</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Total Amount</label>
                    <input type="number" name="total_amount" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="payment_status" class="form-control" required>
                        <option value="Pending">Pending</option>
                        <option value="DP">DP</option>
                        <option value="Paid">Paid</option>
                        <option value="Failed">Failed</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-success">Save</button>
                <a href="{{ route('transactions.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@stop