<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\Service;
use App\Models\Holiday;
use App\Models\Employee;
use App\Models\Coupon;
use Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::latest()->get();
        return view('backend.user.index', compact('users'));
    }

    public function create()
    {
        $days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
        $roles = Role::where('name', '!=', 'admin')->get();
        $services = Service::whereStatus(1)->get();
        return view('backend.user.create', compact('roles','services','days'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|unique:users',
            'phone'    => 'required|string|unique:users,phone',
            'password' => 'required|string|min:8|confirmed',
            'roles'    => 'required|exists:roles,name',
            'service'  => 'nullable|array',
            'slot_duration'  => 'nullable|numeric',
            'break_duration' => 'nullable|numeric',
            'days'     => 'nullable|array',
            'is_employee' => 'nullable|boolean',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'phone'    => $data['phone'] ?? "",
            'email_verified_at' => now(),
            'password' => Hash::make($data['password']),
        ]);

        $user->assignRole($data['roles']);

        if (!empty($data['is_employee'])) {
            $transformedDays = $this->transformOpeningHours($data['days'] ?? []);
            $employee = Employee::create([
                'user_id'        => $user->id,
                'days'           => $transformedDays,
                'slot_duration'  => $data['slot_duration'] ?? null,
                'break_duration' => $data['break_duration'] ?? null,
            ]);
            if (!empty($data['service'])) {
                $employee->services()->attach($data['service']);
            }
        }

        return redirect()->back()->withSuccess('User has been created successfully!');
    }

    public function edit(string $id)
    {
        $days   = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
        $steps  = ['5','10','15','20','30','45','60'];
        $breaks = ['5','10','15','20','25','30'];

        $user = User::with('employee.holidays')->findOrFail($id);
        $employeeDays = $this->transformAvailabilitySlotsForEdit($user->employee->days ?? []);

        $roles    = Role::all();
        $services = Service::whereStatus(1)->get();

        return view('backend.user.edit', compact(
            'user','roles','services','days','steps','breaks','employeeDays'
        ));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'sometimes|email|unique:users,email,' . $user->id,
            'phone'    => 'nullable|string|unique:users,phone,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'roles'    => 'nullable|array|exists:roles,name',
            'service'  => 'nullable|array',
            'slot_duration'  => 'nullable|numeric',
            'break_duration' => 'nullable|numeric',
            'days'     => 'nullable|array',
            'status'   => 'nullable|numeric',
            'is_employee' => 'nullable|boolean',
            'holidays.date.*' => 'sometimes|required',
            'holidays.from_time' => 'nullable',
            'holidays.to_time'   => 'nullable',
            'holidays.recurring' => 'nullable',
        ]);

        // Proteksi perubahan role/status sendiri
        if (\Auth::id() === $user->id) {
            if ($request->filled('roles') && !$user->hasAnyRole($request->roles)) {
                return back()->withErrors(['roles' => 'You cannot change your own role.']);
            }
            if ($request->has('status') && $request->status != $user->status) {
                return back()->withErrors(['status' => 'You cannot change your own status.']);
            }
        }

        // Super admin selalu admin & aktif
        if ($user->id === 1) {
            $data['status'] = 1;
            if (!in_array('admin', $data['roles'] ?? [])) {
                return back()->withErrors(['roles' => 'The first user must always have the admin role.']);
            }
        }

        $user->update([
            'name'     => $data['name'],
            'email'    => $data['email'] ?? $user->email,
            'phone'    => $data['phone'] ?? $user->phone,
            'password' => $data['password'] ? Hash::make($data['password']) : $user->password,
            'status'   => $data['status'] ?? $user->status,
        ]);

        if (!empty($data['roles'])) {
            $roles = $data['roles'];
            if ($user->id === 1 || $user->hasRole('admin')) {
                if (!in_array('admin', $roles)) $roles[] = 'admin';
            }
            $user->syncRoles($roles);
        }

        if (!empty($data['is_employee'])) {
            $transformedDays = !empty($data['days']) ? $this->transformOpeningHours($data['days']) : null;
            $employee = Employee::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'days' => $transformedDays,
                    'slot_duration'  => $data['slot_duration'] ?? null,
                    'break_duration' => $data['break_duration'] ?? null,
                ]
            );
            if (!empty($data['service'])) {
                $employee->services()->sync($data['service']);
            }

            // Holidays handling
            if ($request->has('holidays.date')) {
                $existingIds = $user->employee->holidays->pluck('id')->toArray();
                $submittedIds = [];
                foreach ($request->input('holidays.date') as $i => $date) {
                    $holidayData = [
                        'employee_id' => $user->employee->id,
                        'hours' => ($request->holidays['from_time'][$i] ?? null) && ($request->holidays['to_time'][$i] ?? null)
                            ? [$request->holidays['from_time'][$i] . '-' . $request->holidays['to_time'][$i]]
                            : [],
                        'recurring' => ($request->holidays['recurring'][$i] ?? 0) == 1,
                        'date' => ($request->holidays['recurring'][$i] ?? 0) == 1
                            ? \Carbon\Carbon::parse($date)->format('m-d')
                            : $date,
                    ];
                    if (!empty($request->holidays['id'][$i])) {
                        $holiday = Holiday::find($request->holidays['id'][$i]);
                        if ($holiday) {
                            $holiday->update($holidayData);
                            $submittedIds[] = $holiday->id;
                        }
                    } else {
                        $holiday = Holiday::create($holidayData);
                        $submittedIds[] = $holiday->id;
                    }
                }
                $toDelete = array_diff($existingIds, $submittedIds);
                if (!empty($toDelete)) Holiday::whereIn('id', $toDelete)->delete();
            } else {
                $user->employee->holidays()->delete();
            }
        }

        return redirect()->route('user.index')->with('success', 'Profile has been updated successfully!');
    }

    public function dashboard()
    {
        $userId = auth()->id();

        $transactions = DB::table('transactions')
            ->join('appointments','transactions.appointment_id','=','appointments.id')
            ->join('users','transactions.user_id','=','users.id')
            ->select(
                'transactions.id as transaction_id',
                'transactions.transaction_code',
                'transactions.payment_method',
                'transactions.amount',
                'transactions.total_amount',
                'transactions.payment_status',
                'appointments.booking_date',
                'appointments.booking_time',
                'appointments.status as appointment_status',
                'users.name','users.email','users.phone'
            )
            ->where('transactions.user_id',$userId)
            ->orderBy('transactions.created_at','desc')
            ->get();

        // Kupon diambil dari observer otomatis
        $coupons = Coupon::where('user_id',$userId)->where('active',1)->get();
        $usedCoupons = Coupon::where('user_id',$userId)->where('status','used')->count();

        return view('frontend.member.dashboard', compact('transactions','coupons','usedCoupons'));
    }

    protected function transformOpeningHours($data)
    {
        $result = [];
        foreach ($data as $day => $times) {
            $dayHours = [];
            for ($i = 0; $i < count($times); $i += 2) {
                if (isset($times[$i+1])) $dayHours[] = $times[$i] . '-' . $times[$i+1];
            }
            $result[$day] = $dayHours;
        }
        return $result;
    }

    protected function transformAvailabilitySlotsForEdit(array $employeeDays)
    {
        foreach ($employeeDays as $day => $slots) {
            $employeeDays[$day] = collect($slots)->flatMap(function ($slot) {
                [$start,$end] = explode('-', $slot);
                return [$start,$end];
            })->toArray();
        }
        return $employeeDays;
    }
}
