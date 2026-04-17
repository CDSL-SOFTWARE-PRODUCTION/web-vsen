<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 64)->unique();
            $table->string('plate_number', 32)->nullable();
            $table->string('description', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('delivery_routes', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 128);
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->string('route_type', 32)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_routes');
        Schema::dropIfExists('vehicles');
    }
};
