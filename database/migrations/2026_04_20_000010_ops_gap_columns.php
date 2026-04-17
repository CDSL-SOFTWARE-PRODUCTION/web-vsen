<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Runs after core Ops tables (orders, partners, deliveries, canonical_products).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('orders') && ! Schema::hasColumn('orders', 'fulfillment_priority')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->string('fulfillment_priority', 24)->default('contract')->after('state');
            });
        }

        if (Schema::hasTable('partners') && ! Schema::hasColumn('partners', 'reserve_ttl_days')) {
            Schema::table('partners', function (Blueprint $table): void {
                if (Schema::hasColumn('partners', 'max_overdue_days_cached')) {
                    $table->unsignedSmallInteger('reserve_ttl_days')->nullable()->after('max_overdue_days_cached');
                } else {
                    $table->unsignedSmallInteger('reserve_ttl_days')->nullable();
                }
            });
        }

        if (Schema::hasTable('deliveries') && ! Schema::hasColumn('deliveries', 'expected_gps_coordinates')) {
            Schema::table('deliveries', function (Blueprint $table): void {
                if (Schema::hasColumn('deliveries', 'gps_coordinates_actual')) {
                    $table->string('expected_gps_coordinates', 64)->nullable()->after('gps_coordinates_actual');
                } else {
                    $table->string('expected_gps_coordinates', 64)->nullable();
                }
            });
        }

        if (Schema::hasTable('canonical_products') && ! Schema::hasColumn('canonical_products', 'abc_class')) {
            Schema::table('canonical_products', function (Blueprint $table): void {
                if (Schema::hasColumn('canonical_products', 'raw_name')) {
                    $table->string('abc_class', 8)->nullable()->after('raw_name');
                } else {
                    $table->string('abc_class', 8)->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'fulfillment_priority')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->dropColumn('fulfillment_priority');
            });
        }

        if (Schema::hasTable('partners') && Schema::hasColumn('partners', 'reserve_ttl_days')) {
            Schema::table('partners', function (Blueprint $table): void {
                $table->dropColumn('reserve_ttl_days');
            });
        }

        if (Schema::hasTable('deliveries') && Schema::hasColumn('deliveries', 'expected_gps_coordinates')) {
            Schema::table('deliveries', function (Blueprint $table): void {
                $table->dropColumn('expected_gps_coordinates');
            });
        }

        if (Schema::hasTable('canonical_products') && Schema::hasColumn('canonical_products', 'abc_class')) {
            Schema::table('canonical_products', function (Blueprint $table): void {
                $table->dropColumn('abc_class');
            });
        }
    }
};
