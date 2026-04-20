<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partners', function (Blueprint $table): void {
            $table->string('bidder_identifier', 64)->nullable()->after('name');
            $table->index(['type', 'bidder_identifier']);
        });

        Schema::table('bid_opening_lines', function (Blueprint $table): void {
            $table->foreignId('canonical_product_id')
                ->nullable()
                ->after('item_name')
                ->constrained('canonical_products')
                ->nullOnDelete();
            $table->string('mapping_status', 20)->default('unmapped')->after('canonical_product_id');
            $table->text('mapping_note')->nullable()->after('mapping_status');
            $table->timestamp('mapped_at')->nullable()->after('mapping_note');
            $table->index(['bid_opening_session_id', 'mapping_status'], 'bid_opening_lines_mapping_status_idx');
        });

        Schema::table('order_items', function (Blueprint $table): void {
            $table->foreignId('canonical_product_id')
                ->nullable()
                ->after('price_list_item_id')
                ->constrained('canonical_products')
                ->nullOnDelete();
            $table->string('procurement_status', 30)->default('pending')->after('status');
            $table->index(['order_id', 'procurement_status']);
        });

        Schema::table('supply_orders', function (Blueprint $table): void {
            $table->foreignId('supplier_partner_id')
                ->nullable()
                ->after('order_id')
                ->constrained('partners')
                ->nullOnDelete();
            $table->timestamp('approval_requested_at')->nullable()->after('status');
            $table->timestamp('approved_at')->nullable()->after('approval_requested_at');
            $table->foreignId('approved_by_user_id')
                ->nullable()
                ->after('approved_at')
                ->constrained('users')
                ->nullOnDelete();
            $table->text('blocked_reason')->nullable()->after('approved_by_user_id');
            $table->index(['status', 'approval_requested_at'], 'supply_orders_status_approval_idx');
        });

        Schema::table('supply_order_lines', function (Blueprint $table): void {
            $table->foreignId('canonical_product_id')
                ->nullable()
                ->after('order_item_id')
                ->constrained('canonical_products')
                ->nullOnDelete();
            $table->decimal('planned_unit_price', 18, 2)->nullable()->after('status');
            $table->decimal('reference_unit_price', 18, 2)->nullable()->after('planned_unit_price');
            $table->decimal('price_deviation_pct', 8, 4)->nullable()->after('reference_unit_price');
            $table->boolean('price_deviation_flag')->default(false)->after('price_deviation_pct');
        });
    }

    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table): void {
            $table->dropColumn('bidder_identifier');
        });

        Schema::table('supply_order_lines', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('canonical_product_id');
            $table->dropColumn([
                'planned_unit_price',
                'reference_unit_price',
                'price_deviation_pct',
                'price_deviation_flag',
            ]);
        });

        Schema::table('supply_orders', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('supplier_partner_id');
            $table->dropConstrainedForeignId('approved_by_user_id');
            $table->dropColumn([
                'approval_requested_at',
                'approved_at',
                'blocked_reason',
            ]);
        });

        Schema::table('order_items', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('canonical_product_id');
            $table->dropColumn('procurement_status');
        });

        Schema::table('bid_opening_lines', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('canonical_product_id');
            $table->dropColumn([
                'mapping_status',
                'mapping_note',
                'mapped_at',
            ]);
        });
    }
};
