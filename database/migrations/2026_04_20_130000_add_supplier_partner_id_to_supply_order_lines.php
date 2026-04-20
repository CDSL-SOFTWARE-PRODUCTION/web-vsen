<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supply_order_lines', function (Blueprint $table): void {
            $table->foreignId('supplier_partner_id')
                ->nullable()
                ->after('canonical_product_id')
                ->constrained('partners')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('supply_order_lines', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('supplier_partner_id');
        });
    }
};
