<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\Coupon;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $setting = Setting::firstOrFail();
        $user = auth()->user();

        /**
         * ===========================================================
         * ADMIN DASHBOARD
         * ===========================================================
         */
        if ($user->hasRole('admin')) {
            $appointments = Appointment::with(['employee.user', 'service', 'user'])->get();

            $appointments = $appointments->map(function ($appointment) {
                try {
                    if (!str_contains($appointment->booking_time ?? '', '-')) {
                        throw new \Exception("Invalid time format");
                    }

                    $bookingDate = Carbon::parse($appointment->booking_date);
                    [$startTime, $endTime] = array_map('trim', explode('-', $appointment->booking_time));

                    $startDateTime = Carbon::createFromFormat('h:i A', $startTime)
                        ->setDate($bookingDate->year, $bookingDate->month, $bookingDate->day);

                    $endDateTime = Carbon::createFromFormat('h:i A', $endTime)
                        ->setDate($bookingDate->year, $bookingDate->month, $bookingDate->day);

                    if ($endDateTime->lt($startDateTime)) {
                        $endDateTime->addDay();
                    }

                    return [
                        'id' => $appointment->id,
                        'title' => sprintf('%s - %s',
                            $appointment->name,
                            $appointment->service->title ?? 'Service'
                        ),
                        'start' => $startDateTime->toIso8601String(),
                        'end' => $endDateTime->toIso8601String(),
                        'description' => $appointment->notes,
                        'email' => $appointment->email,
                        'phone' => $appointment->phone,
                        'amount' => $appointment->amount,
                        'status' => $appointment->status,
                        'staff' => $appointment->employee->user->name ?? 'Unassigned',
                        'color' => $this->getStatusColor($appointment->status),
                        'service_title' => $appointment->service->title ?? 'Service',
                        'name' => $appointment->name,
                        'notes' => $appointment->notes,
                    ];
                } catch (\Exception $e) {
                    \Log::error("Format error for appointment {$appointment->id}: {$e->getMessage()}");
                    return null;
                }
            })->filter();

            return view('backend.dashboard.index', compact('appointments'));
        }

        /**
         * ===========================================================
         * MEMBER DASHBOARD
         * ===========================================================
         */

        // Ambil semua transaksi milik user
        $transactions = Transaction::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        // Ambil semua kupon milik user (aktif & reward)
        $coupons = Coupon::where('user_id', $user->id)
            ->where('active', 1)
            ->get();

        // Hitung kupon yang sudah digunakan
        $usedCoupons = Coupon::where('user_id', $user->id)
            ->where('status', 'used')
            ->count();

        return view('frontend.member.dashboard', compact('transactions', 'coupons', 'usedCoupons'));
    }

    // Helper warna status appointment
    private function getStatusColor($status)
    {
        $colors = [
            'Pending' => '#f39c12',
            'Processing' => '#3498db',
            'Confirmed' => '#2ecc71',
            'Cancelled' => '#ff0000',
            'Completed' => '#008000',
            'On Hold' => '#95a5a6',
            'Rescheduled' => '#f1c40f',
            'No Show' => '#e67e22',
        ];

        return $colors[$status] ?? '#7f8c8d';
    }

    // Update status appointment
    public function updateStatus(Request $request)
    {
        $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'status' => 'required|in:Pending,Processing,Confirmed,Cancelled,Completed,On Hold,No Show'
        ]);

        $appointment = Appointment::findOrFail($request->appointment_id);
        $appointment->status = $request->status;
        $appointment->save();

        event(new \App\Events\StatusUpdated($appointment));

        return back()->with('success', 'Status updated successfully');
    }
}
