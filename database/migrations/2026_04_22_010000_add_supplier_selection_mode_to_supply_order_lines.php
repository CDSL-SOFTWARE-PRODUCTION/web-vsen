<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supply_order_lines', function (Blueprint $table): void {
            $table->string('supplier_selection_mode', 40)
                ->nullable()
                ->after('supplier_suggestion_source');
        });

        DB::table('supply_order_lines')
            ->whereNotNull('supplier_suggestion_source')
            ->update(['supplier_selection_mode' => 'auto_suggested']);
    }

    public function down(): void
    {
        Schema::table('supply_order_lines', function (Blueprint $table): void {
            $table->dropColumn('supplier_selection_mode');
        });
    }
};
