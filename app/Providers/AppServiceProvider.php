<?php

namespace App\Providers;

use App\Contracts\Finance\MisaInvoicePort;
use App\Models\Demand\Order;
use App\Models\Ops\Invoice;
use App\Policies\InvoicePolicy;
use App\Policies\OrderPolicy;
use App\Services\Finance\NullMisaInvoiceAdapter;
use Illuminate\Support\Facades\Gate;
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

        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(Invoice::class, InvoicePolicy::class);
    }
}
