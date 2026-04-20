<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bid_opening_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('bid_opening_session_id')->constrained('bid_opening_sessions')->cascadeOnDelete();
            $table->unsignedInteger('source_row_no')->nullable();
            $table->string('lot_code')->index();
            $table->string('item_name')->nullable();
            $table->string('bidder_identifier')->nullable()->index();
            $table->string('bidder_name');
            $table->unsignedSmallInteger('bid_valid_days')->nullable();
            $table->decimal('bid_security_value', 18, 2)->nullable();
            $table->unsignedSmallInteger('bid_security_days')->nullable();
            $table->decimal('bid_price', 18, 2);
            $table->decimal('discount_rate', 8, 4)->default(0);
            $table->decimal('bid_price_after_discount', 18, 2)->nullable();
            $table->text('delivery_commitment')->nullable();
            $table->char('currency', 3)->default('VND');
            $table->string('row_fingerprint', 64);
            $table->timestamps();

            $table->index(['bid_opening_session_id', 'lot_code'], 'bid_opening_lines_session_lot');
            $table->unique(['bid_opening_session_id', 'row_fingerprint'], 'bid_opening_lines_unique_row');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bid_opening_lines');
    }
};
