<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_milestones', function (Blueprint $table): void {
            $table->unsignedInteger('days_overdue_cached')->default(0)->after('payment_ready');
        });
    }

    public function down(): void
    {
        Schema::table('payment_milestones', function (Blueprint $table): void {
            $table->dropColumn('days_overdue_cached');
        });
    }
};
