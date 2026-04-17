<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('canonical_products') && ! Schema::hasColumn('canonical_products', 'image_url')) {
            Schema::table('canonical_products', function (Blueprint $table): void {
                $table->string('image_url', 2048)->nullable();
            });
        }

        if (Schema::hasTable('price_list_items') && ! Schema::hasColumn('price_list_items', 'canonical_product_id')) {
            Schema::table('price_list_items', function (Blueprint $table): void {
                $table->foreignId('canonical_product_id')
                    ->nullable()
                    ->after('price_list_id')
                    ->constrained('canonical_products')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('price_list_items') && Schema::hasColumn('price_list_items', 'canonical_product_id')) {
            Schema::table('price_list_items', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('canonical_product_id');
            });
        }

        if (Schema::hasTable('canonical_products') && Schema::hasColumn('canonical_products', 'image_url')) {
            Schema::table('canonical_products', function (Blueprint $table): void {
                $table->dropColumn('image_url');
            });
        }
    }
};
