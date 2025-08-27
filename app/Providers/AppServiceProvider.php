<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\App;
use Carbon\Carbon; 

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useTailwind();

        // [3] TAMBAHKAN BLOK INI UNTUK MENGATUR BAHASA
        try {
            // Mengatur lokal Carbon ke Bahasa Indonesia (id)
            Carbon::setLocale(config('app.locale'));
        } catch (\Exception $e) {
            // Lakukan sesuatu jika terjadi error, misal log
        }
    }
}