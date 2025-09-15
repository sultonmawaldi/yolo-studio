<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Appointment;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::with('appointment.service', 'appointment.employee.user')
            ->latest()
            ->get();

        return view('backend.transactions.index', compact('transactions'));
    }

    public function create()
    {
        $appointments = Appointment::with('service', 'employee.user')->get();
        return view('backend.transactions.create', compact('appointments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'appointment_id'   => 'required|exists:appointments,id',
            'payment_method'   => 'required|string',
            'amount'           => 'required|numeric',
            'total_amount'     => 'required|numeric',
            'payment_status'   => 'required|string',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['transaction_code'] = 'TRX-' . strtoupper(uniqid());

        Transaction::create($validated);

        return redirect()->route('transactions.index')->with('success', 'Transaction created successfully.');
    }

    public function edit(Transaction $transaction)
    {
        $appointments = Appointment::with('service', 'employee.user')->get();
        return view('backend.transactions.edit', compact('transaction', 'appointments'));
    }

    public function update(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'appointment_id'   => 'required|exists:appointments,id',
            'payment_method'   => 'required|string',
            'amount'           => 'required|numeric',
            'total_amount'     => 'required|numeric',
            'payment_status'   => 'required|string',
        ]);

        $transaction->update($validated);

        return redirect()->route('transactions.index')->with('success', 'Transaction updated successfully.');
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->delete();
        return redirect()->route('transactions.index')->with('success', 'Transaction deleted successfully.');
    }
}
