<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SummerNoteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\CouponController;
use Illuminate\Http\Request;
use App\Models\Coupon;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\MidtransController;


Auth::routes();

Route::get('/',[FrontendController::class,'index'])->name('home');

Route::middleware(['auth'])->group(function () {

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Coupons tanpa role:admin, hanya auth dan permission
    Route::prefix('coupons')->name('coupons.')->group(function () {

        Route::get('/', [CouponController::class, 'index'])
            ->name('index')
            ->middleware('permission:coupons.view');

        Route::get('/create', [CouponController::class, 'create'])
            ->name('create')
            ->middleware('permission:coupons.create');

        Route::post('/', [CouponController::class, 'store'])
            ->name('store')
            ->middleware('permission:coupons.create');

        Route::get('/{coupon}/edit', [CouponController::class, 'edit'])
            ->name('edit')
            ->middleware('permission:coupons.edit');

        Route::put('/{coupon}', [CouponController::class, 'update'])
            ->name('update')
            ->middleware('permission:coupons.edit');

        Route::delete('/{coupon}', [CouponController::class, 'destroy'])
            ->name('destroy')
            ->middleware('permission:coupons.delete');

    });

    // ... route lainnya tetap sama ...

});





    //user
    Route::resource('user',UserController::class)->middleware('permission:users.view| users.create | users.edit | users.delete');
    //update user password

    //profile page
    Route::get('profile',[ProfileController::class,'index'])->name('profile');
    //user profile update
    Route::patch('profile-update/{user}',[ProfileController::class,'profileUpdate'])->name('user.profile.update');
    Route::patch('user/pasword-update/{user}',[UserController::class,'password_update'])->name('user.password.update');
    Route::put('user/profile-pic/{user}',[UserController::class,'updateProfileImage'])->name('user.profile.image.update');

    //delete profile image
    Route::patch('delete-profile-image/{user}',[UserController::class,'deleteProfileImage'])->name('delete.profile.image');
    //trash view for users
    Route::get('user-trash', [UserController::class, 'trashView'])->name('user.trash');
    Route::get('user-restore/{id}', [UserController::class, 'restore'])->name('user.restore');
    //deleted permanently
    Route::delete('user-delete/{id}', [UserController::class, 'force_delete'])->name('user.force.delete');

    Route::get('settings', [SettingController::class, 'index'])->name('setting')->middleware('permission:setting update');
    Route::post('settings/{setting}', [SettingController::class, 'update'])->name('setting.update');


    Route::resource('category', CategoryController::class)->middleware('permission:categories.view| categories.create | categories.edit | categories.delete');


    // Services
    Route::resource('service', ServiceController::class)->middleware('permission:services.view| services.create | services.edit | services.delete');
    Route::get('service-trash', [ServiceController::class, 'trashView'])->name('service.trash');
    Route::get('service-restore/{id}', [ServiceController::class, 'restore'])->name('service.restore');
    //deleted permanently
    Route::delete('service-delete/{id}', [ServiceController::class, 'force_delete'])->name('service.force.delete');


    //summernote image
    Route::post('summernote',[SummerNoteController::class,'summerUpload'])->name('summer.upload.image');
    Route::post('summernote/delete',[SummerNoteController::class,'summerDelete'])->name('summer.delete.image');


    //employee
    // Route::resource('user',UserController::class);
    Route::get('employee-booking',[UserController::class,'EmployeeBookings'])->name('employee.bookings');
    Route::get('my-booking/{id}',[UserController::class,'show'])->name('employee.booking.detail');

    // employee profile self data update
    Route::patch('employe-profile-update/{employee}',[ProfileController::class,'employeeProfileUpdate'])->name('employee.profile.update');

    //employee bio
    Route::put('employee-bio/{employee}',[EmployeeController::class,'updateBio'])->name('employee.bio.update');





    Route::get('test',function(Request $request){
        return view('test',  [
            'request' => $request
        ]);
    });



    Route::post('test', function (Request $request) {
        dd($request->all())->toArray();
    })->name('test');

});

//transaction
Route::resource('transactions', TransactionController::class)->middleware('auth');

//frontend routes
//fetch services from categories
Route::get('/categories/{id}/services', [FrontendController::class, 'getServices']);

//fetch employee from category
Route::get('/services/{service}/employees', [FrontendController::class, 'getEmployees'])->name('get.employees');

//get availibility
Route::get('/employees/{employee}/availability/{date?}', [FrontendController::class, 'getEmployeeAvailability'])
    ->name('employee.availability');

//create appointment
Route::post('/bookings', [AppointmentController::class, 'store'])->name('bookings.store');
Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments')->middleware('permission:appointments.view| appointments.create | services.appointments | appointments.delete');

Route::post('/appointments/update-status', [AppointmentController::class, 'updateStatus'])->name('appointments.update.status');

//update status from dashbaord
Route::post('/update-status', [DashboardController::class, 'updateStatus'])->name('dashboard.update.status');

//payment
Route::post('/midtrans/token', [PaymentController::class, 'getSnapToken']);

//webhook
Route::post('/midtrans/notification', [MidtransController::class, 'notificationHandler']);

Route::get('/transactions/history', [TransactionController::class, 'history'])
     ->name('transactions.history')
     ->middleware('auth');


//coupon
Route::post('/validate-coupon', function (Request $request) {
    $request->validate([
        'code' => 'required|string'
    ]);

    $coupon = Coupon::where('code', strtoupper($request->code))
        ->where('active', true)
        ->where(function ($query) {
            $query->whereNull('expiry_date')->orWhere('expiry_date', '>=', now());
        })
        ->first();

    if (!$coupon) {
        return response()->json(['message' => 'Kupon tidak valid.'], 404);
    }

    return response()->json([
        'type' => $coupon->type,
        'value' => $coupon->value
    ]);
});

//transaksi

// User booking → buat transaksi (pakai AJAX)
Route::post('/transactions/store', [TransactionController::class, 'store'])->name('transactions.store');

// Callback Midtrans (pastikan URL ini di-set di dashboard Midtrans)
Route::post('/midtrans/callback', [TransactionController::class, 'callback'])->name('midtrans.callback');

// History transaksi (hanya user login)
Route::middleware('auth')->group(function () {
    Route::get('/transactions/history', [TransactionController::class, 'history'])->name('transactions.history');
});



