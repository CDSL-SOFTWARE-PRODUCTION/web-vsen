<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table): void {
            $table->string('lot_code', 64)->nullable()->after('line_no');
            $table->string('project_location')->nullable()->after('quantity');
            $table->string('required_delivery_timeline', 255)->nullable()->after('project_location');
            $table->string('proposed_delivery_timeline', 255)->nullable()->after('required_delivery_timeline');

            $table->index(['order_id', 'lot_code']);
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table): void {
            $table->dropIndex(['order_id', 'lot_code']);
            $table->dropColumn([
                'lot_code',
                'project_location',
                'required_delivery_timeline',
                'proposed_delivery_timeline',
            ]);
        });
    }
};
