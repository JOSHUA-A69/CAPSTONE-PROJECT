<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

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
        // Ensure DB session timezone aligns with app timezone for MySQL
        try {
            $driver = Schema::getConnection()->getDriverName();
            if ($driver === 'mysql') {
                // Use +08:00 for Philippine Standard Time
                DB::statement("SET time_zone = '+08:00'");
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to set DB session time_zone: '.$e->getMessage());
        }
    }
}
