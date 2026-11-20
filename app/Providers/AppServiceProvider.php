<?php

namespace App\Providers;

use App\Services\NotificationService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Throwable;

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
        Paginator::defaultView('vendor.pagination.sipala');
        Paginator::defaultSimpleView('vendor.pagination.sipala-simple');

        // Pengecekan otomatis di latar belakang (setelah response dikirim) untuk pengingat rating mingguan (7 hari)
        if (!$this->app->runningInConsole()) {
            $this->app->terminating(function () {
                try {
                    if (Cache::add('sipala_auto_rating_reminder_check', true, now()->addHours(6))) {
                        app(NotificationService::class)->sendDueRatingReminders(7, false);
                    }
                } catch (Throwable $e) {
                    // Abaikan agar tidak mengganggu request pengguna
                }
            });
        }
    }
}
