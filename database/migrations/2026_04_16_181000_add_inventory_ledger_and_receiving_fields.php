<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supply_order_lines', function (Blueprint $table): void {
            $table->decimal('received_qty', 18, 3)->default(0)->after('shortage_qty');
            $table->string('status', 40)->default('Pending')->after('received_qty');
        });

        Schema::create('inventory_ledgers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('inventory_lot_id')->nullable()->constrained('inventory_lots')->nullOnDelete();
            $table->foreignId('supply_order_id')->nullable()->constrained('supply_orders')->nullOnDelete();
            $table->foreignId('supply_order_line_id')->nullable()->constrained('supply_order_lines')->nullOnDelete();
            $table->string('item_name');
            $table->string('action', 20);
            $table->decimal('qty_change', 18, 3);
            $table->decimal('balance_after', 18, 3);
            $table->timestamps();

            $table->index(['item_name', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_ledgers');

        Schema::table('supply_order_lines', function (Blueprint $table): void {
            $table->dropColumn(['received_qty', 'status']);
        });
    }
};
