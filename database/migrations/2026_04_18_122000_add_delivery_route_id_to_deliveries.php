<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deliveries', function (Blueprint $table): void {
            $table->foreignId('delivery_route_id')->nullable()->after('vehicle_id')->constrained('delivery_routes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $table): void {
            $table->dropForeign(['delivery_route_id']);
            $table->dropColumn('delivery_route_id');
        });
    }
};
