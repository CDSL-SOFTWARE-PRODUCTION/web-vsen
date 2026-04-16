<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table): void {
            $table->unsignedBigInteger('order_id')->nullable()->after('id');
            $table->string('tender_snapshot_ref')->nullable()->after('order_id');

            $table->index(['order_id']);
            $table->index(['tender_snapshot_ref']);
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table): void {
            $table->dropIndex(['order_id']);
            $table->dropIndex(['tender_snapshot_ref']);
            $table->dropColumn(['order_id', 'tender_snapshot_ref']);
        });
    }
};

