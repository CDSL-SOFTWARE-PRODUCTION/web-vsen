<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('price_lists', function (Blueprint $table): void {
            $table->string('list_code', 64)->nullable()->after('name');
            $table->string('status', 20)->default('active')->after('channel');
            $table->text('description')->nullable()->after('status');
            $table->char('default_currency', 3)->nullable()->after('description');

            $table->unique('list_code');
        });

        Schema::table('price_list_items', function (Blueprint $table): void {
            $table->string('uom', 32)->nullable()->after('product_name');
            $table->string('supplier_sku', 128)->nullable()->after('uom');
            $table->text('notes')->nullable()->after('currency');
            $table->unsignedSmallInteger('lead_time_days')->nullable()->after('notes');
            $table->string('inco_term', 10)->nullable()->after('lead_time_days');
        });
    }

    public function down(): void
    {
        Schema::table('price_list_items', function (Blueprint $table): void {
            $table->dropColumn([
                'uom',
                'supplier_sku',
                'notes',
                'lead_time_days',
                'inco_term',
            ]);
        });

        Schema::table('price_lists', function (Blueprint $table): void {
            $table->dropUnique(['list_code']);
            $table->dropColumn([
                'list_code',
                'status',
                'description',
                'default_currency',
            ]);
        });
    }
};
