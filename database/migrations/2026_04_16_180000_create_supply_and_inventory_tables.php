<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_lots', function (Blueprint $table): void {
            $table->id();
            $table->string('item_name');
            $table->decimal('available_qty', 18, 3)->default(0);
            $table->timestamps();

            $table->index('item_name');
        });

        Schema::create('supply_orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('supply_order_code')->unique();
            $table->string('status', 40)->default('Draft');
            $table->timestamps();
        });

        Schema::create('supply_order_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('supply_order_id')->constrained('supply_orders')->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->string('item_name');
            $table->decimal('required_qty', 18, 3)->default(0);
            $table->decimal('available_qty', 18, 3)->default(0);
            $table->decimal('shortage_qty', 18, 3)->default(0);
            $table->timestamps();

            $table->index(['supply_order_id', 'order_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supply_order_lines');
        Schema::dropIfExists('supply_orders');
        Schema::dropIfExists('inventory_lots');
    }
};
