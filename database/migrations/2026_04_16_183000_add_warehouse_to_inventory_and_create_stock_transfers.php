<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_lots', function (Blueprint $table): void {
            $table->string('warehouse_code', 50)->default('DC')->after('item_name');
        });

        Schema::create('stock_transfers', function (Blueprint $table): void {
            $table->id();
            $table->string('transfer_code')->unique();
            $table->string('item_name');
            $table->string('source_warehouse_code', 50);
            $table->string('dest_warehouse_code', 50);
            $table->decimal('quantity', 18, 3);
            $table->string('status', 40)->default('Pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transfers');

        Schema::table('inventory_lots', function (Blueprint $table): void {
            $table->dropColumn('warehouse_code');
        });
    }
};
