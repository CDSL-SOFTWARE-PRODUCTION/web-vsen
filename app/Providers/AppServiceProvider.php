<?php

namespace App\Providers;

use App\Contracts\Finance\MisaInvoicePort;
use App\Services\Finance\NullMisaInvoiceAdapter;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(MisaInvoicePort::class, NullMisaInvoiceAdapter::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
