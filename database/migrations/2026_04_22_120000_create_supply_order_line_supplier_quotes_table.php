<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supply_order_line_supplier_quotes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('supply_order_line_id')->constrained('supply_order_lines')->cascadeOnDelete();
            $table->foreignId('partner_id')->constrained('partners')->cascadeOnDelete();
            $table->decimal('unit_price', 18, 4);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['supply_order_line_id', 'partner_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supply_order_line_supplier_quotes');
    }
};
