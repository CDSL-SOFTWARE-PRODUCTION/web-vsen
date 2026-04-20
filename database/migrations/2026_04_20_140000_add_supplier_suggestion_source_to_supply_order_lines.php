<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supply_order_lines', function (Blueprint $table): void {
            $table->string('supplier_suggestion_source', 40)
                ->nullable()
                ->after('supplier_partner_id');
        });
    }

    public function down(): void
    {
        Schema::table('supply_order_lines', function (Blueprint $table): void {
            $table->dropColumn('supplier_suggestion_source');
        });
    }
};
