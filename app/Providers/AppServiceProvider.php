<?php

namespace App\Providers;

use App\Contracts\Finance\MisaInvoicePort;
use App\Models\Demand\Order;
use App\Models\Ops\DeliveryRoute;
use App\Models\Ops\Invoice;
use App\Models\Ops\Vehicle;
use App\Models\Supply\SupplyOrder;
use App\Policies\DeliveryRoutePolicy;
use App\Policies\InvoicePolicy;
use App\Policies\OrderPolicy;
use App\Policies\SupplyOrderPolicy;
use App\Policies\VehiclePolicy;
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
        Gate::policy(SupplyOrder::class, SupplyOrderPolicy::class);
        Gate::policy(Vehicle::class, VehiclePolicy::class);
        Gate::policy(DeliveryRoute::class, DeliveryRoutePolicy::class);
    }
}
