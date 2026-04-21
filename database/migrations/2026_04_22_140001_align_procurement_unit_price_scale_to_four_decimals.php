<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('price_list_items', function (Blueprint $table): void {
            $table->decimal('unit_price', 18, 4)->change();
        });

        Schema::table('supply_order_lines', function (Blueprint $table): void {
            $table->decimal('planned_unit_price', 18, 4)->nullable()->change();
            $table->decimal('reference_unit_price', 18, 4)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('price_list_items', function (Blueprint $table): void {
            $table->decimal('unit_price', 18, 2)->change();
        });

        Schema::table('supply_order_lines', function (Blueprint $table): void {
            $table->decimal('planned_unit_price', 18, 2)->nullable()->change();
            $table->decimal('reference_unit_price', 18, 2)->nullable()->change();
        });
    }
};
