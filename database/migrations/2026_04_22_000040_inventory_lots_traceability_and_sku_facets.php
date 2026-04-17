<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_lots', function (Blueprint $table): void {
            $table->foreignId('canonical_product_id')
                ->nullable()
                ->after('id')
                ->constrained('canonical_products')
                ->nullOnDelete();
            $table->string('lot_code', 80)->nullable()->after('warehouse_code');
            $table->string('supplier_ref', 120)->nullable()->after('lot_code');
            $table->date('mfg_date')->nullable()->after('supplier_ref');
            $table->date('expiry_date')->nullable()->after('mfg_date');

            $table->index(['canonical_product_id', 'warehouse_code']);
            $table->index(['expiry_date']);
        });
    }

    public function down(): void
    {
        Schema::table('inventory_lots', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('canonical_product_id');
            $table->dropColumn(['lot_code', 'supplier_ref', 'mfg_date', 'expiry_date']);
        });
    }
};
