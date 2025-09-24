<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rappasoft\LaravelAuthenticationLog\Traits\AuthenticationLoggable;
use Illuminate\Support\Facades\Auth;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes, AuthenticationLoggable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'status',
        'image',
        'role_uid',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relasi ke data employee (jika ada)
     */
    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    /**
     * Relasi many-to-many dengan services
     */
    public function services()
    {
        return $this->belongsToMany(Service::class);
    }

    /**
     * Relasi ke appointments milik user
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Relasi ke transaksi milik user
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Relasi ke kupon milik user
     */
    public function coupons()
    {
        return $this->hasMany(Coupon::class, 'user_id');
    }

    /**
     * URL profil untuk AdminLTE
     */
    public function adminlte_profile_url()
    {
        return "/profile";
    }

    /**
     * Gambar user untuk AdminLTE
     */
    public function adminlte_image()
    {
        $userImage = Auth::user()->image;

        if ($userImage) {
            if (strpos($userImage, 'https://') === 0) {
                return $userImage;
            } else {
                return asset('uploads/images/profile/' . $userImage);
            }
        } else {
            return asset('vendor/adminlte/dist/img/gravtar.jpg');
        }
    }

    /**
     * Gambar profil user
     */
    public function profileImage()
    {
        $userImage = $this->image;

        if (!empty($userImage)) {
            return asset('uploads/images/profile/' . $userImage);
        } else {
            return asset('vendor/adminlte/dist/img/gravtar.jpg');
        }
    }

    /**
     * Gambar employee (bisa sama dengan profile)
     */
    public function employeeImage()
    {
        $userImage = $this->image;

        if (!empty($userImage)) {
            return asset('uploads/images/profile/' . $userImage);
        } else {
            return asset('vendor/adminlte/dist/img/gravtar.jpg');
        }
    }
}
