<?php

namespace App\Providers;

use App\Database\Query\Grammars\AccentInsensitivePostgresGrammar;
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
use App\Support\Search\VietnameseTextSearch;
use Illuminate\Database\PostgresConnection;
use Illuminate\Database\SQLiteConnection;
use Throwable;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
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
        $this->registerPostgresAccentInsensitiveLike();
        $this->registerSqliteAccentInsensitiveLike();

        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(Invoice::class, InvoicePolicy::class);
        Gate::policy(SupplyOrder::class, SupplyOrderPolicy::class);
        Gate::policy(Vehicle::class, VehiclePolicy::class);
        Gate::policy(DeliveryRoute::class, DeliveryRoutePolicy::class);
    }

    private function registerSqliteAccentInsensitiveLike(): void
    {
        $connection = DB::connection();
        if (! $connection instanceof SQLiteConnection) {
            return;
        }

        $pdo = $connection->getPdo();
        $pdo->sqliteCreateFunction('like', function (?string $pattern, ?string $value): int {
            return VietnameseTextSearch::likeMatch($value, $pattern) ? 1 : 0;
        }, 2);
        $pdo->sqliteCreateFunction('like', function (?string $pattern, ?string $value, ?string $escape): int {
            $effectiveEscape = $escape !== null && $escape !== '' ? $escape : '\\';

            return VietnameseTextSearch::likeMatch($value, $pattern, $effectiveEscape) ? 1 : 0;
        }, 3);
    }

    private function registerPostgresAccentInsensitiveLike(): void
    {
        $connection = DB::connection();
        if (! $connection instanceof PostgresConnection) {
            return;
        }

        try {
            DB::statement('CREATE EXTENSION IF NOT EXISTS unaccent');
            $connection->setQueryGrammar(new AccentInsensitivePostgresGrammar());
        } catch (Throwable) {
            // Keep app boot resilient when PostgreSQL is temporarily unavailable.
        }
    }
}
