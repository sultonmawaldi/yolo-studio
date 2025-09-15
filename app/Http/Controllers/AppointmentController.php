<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Events\BookingCreated;
use App\Events\StatusUpdated;
use App\Models\Transaction;


class AppointmentController extends Controller
{

    public function index()
    {
        $appointments = Appointment::latest()->get();
        // dd($appointments); // for debugging only
        return view('backend.appointment.index', compact('appointments'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) 
{
    $validated = $request->validate([
        'user_id' => 'nullable|exists:users,id',
        'employee_id' => 'required|exists:employees,id',
        'service_id' => 'required|exists:services,id',
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'phone' => 'required|string|max:20',
        'notes' => 'nullable|string',
        'amount' => 'required|numeric',
        'booking_date' => 'required|date',
        'booking_time' => 'required',
        'status' => 'required|in:Pending,Processing,Confirmed,Completed,Cancelled',
        'payment_status' => 'nullable|in:Pending,DP,Paid,Failed',
        'people_count' => 'required|integer|min:1',
        'payment_method' => 'nullable|string',
        'total_amount' => 'nullable|numeric',
        'midtrans_order_id' => 'nullable|string',
    ]);

    // Tentukan user_id
    if(auth()->check()) {
        $user = auth()->user();
        $validated['user_id'] = ($user->hasRole('admin') || $user->hasRole('moderator') || $user->hasRole('employee'))
            ? null
            : $user->id;
    } else {
        $validated['user_id'] = null; // guest
    }

    // Generate booking_id unik
    $validated['booking_id'] = 'BK-' . strtoupper(uniqid());

    // Simpan data appointment
    $appointment = Appointment::create([
        'user_id' => $validated['user_id'],
        'employee_id' => $validated['employee_id'],
        'service_id' => $validated['service_id'],
        'name' => $validated['name'],
        'email' => $validated['email'],
        'phone' => $validated['phone'],
        'notes' => $validated['notes'] ?? null,
        'amount' => $validated['amount'],
        'booking_date' => $validated['booking_date'],
        'booking_time' => $validated['booking_time'],
        'status' => $validated['status'],
        'people_count' => $validated['people_count'],
        'booking_id' => $validated['booking_id'],
    ]);

    // Simpan transaksi di tabel transactions
    Transaction::create([
        'user_id' => $validated['user_id'],
        'appointment_id' => $appointment->id,
        'transaction_code' => 'TRX-' . strtoupper(uniqid()),
        'amount' => $validated['amount'],
        'total_amount' => $validated['total_amount'] ?? $validated['amount'],
        'payment_status' => $validated['payment_status'] ?? 'Pending',
        'payment_method' => $validated['payment_method'] ?? null,
        'midtrans_order_id' => $validated['midtrans_order_id'] ?? null,
    ]);

    // Event booking baru
    event(new BookingCreated($appointment));

    return response()->json([
        'success' => true,
        'message' => 'Appointment booked successfully!',
        'booking_id' => $appointment->booking_id,
        'appointment' => $appointment
    ]);
}




    /**
     * Display the specified resource.
     */
    public function show(Appointment $appointment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Appointment $appointment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Appointment $appointment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Appointment $appointment)
    {
        //
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'status' => 'required|string',
        ]);

        $appointment = Appointment::findOrFail($request->appointment_id);
        $appointment->status = $request->status;
        $appointment->save();

        event(new StatusUpdated($appointment));

        return redirect()->back()->with('success', 'Appointment status updated successfully.');
    }

}
