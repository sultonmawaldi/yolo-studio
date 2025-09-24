<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Midtrans\Config;
use App\Models\Transaction;
use App\Observers\TransactionObserver;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Daftarkan PaymentMethodService sebagai singleton
        
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Super admin (id=1) otomatis punya semua permission
        Gate::before(function ($user, $ability) {
            return $user->id === 1 ? true : null;
        });

        // Konfigurasi Midtrans jika tersedia
        if (config('midtrans.server_key') && config('midtrans.client_key')) {
            Config::$serverKey    = config('midtrans.server_key');
            Config::$clientKey    = config('midtrans.client_key');
            Config::$isProduction = (bool) config('midtrans.is_production', false);
            Config::$isSanitized  = config('midtrans.is_sanitized', true);
            Config::$is3ds        = config('midtrans.is_3ds', true);
        }

        // Daftarkan TransactionObserver
        Transaction::observe(TransactionObserver::class);
    }
}
