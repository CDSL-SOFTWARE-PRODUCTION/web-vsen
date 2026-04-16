<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('return_orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('return_code')->unique();
            $table->string('status', 40)->default('Draft');
            $table->timestamps();
        });

        Schema::create('return_line_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('return_order_id')->constrained('return_orders')->cascadeOnDelete();
            $table->string('item_name');
            $table->string('warehouse_code', 50)->default('DC');
            $table->decimal('quantity', 18, 3);
            $table->string('condition', 20)->default('Good');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_line_items');
        Schema::dropIfExists('return_orders');
    }
};
