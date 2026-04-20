<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('award_outcomes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('bid_opening_session_id')->nullable()->constrained('bid_opening_sessions')->nullOnDelete();
            $table->foreignId('tender_snapshot_id')->nullable()->constrained('tender_snapshots')->nullOnDelete();
            $table->string('source_system', 50)->default('muasamcong');
            $table->string('source_notify_no')->index();
            $table->string('lot_code');
            $table->string('winning_bidder_identifier')->nullable()->index();
            $table->string('winning_bidder_name');
            $table->decimal('winning_price', 18, 2);
            $table->char('currency', 3)->default('VND');
            $table->timestamp('awarded_at')->nullable()->index();
            $table->string('status', 30)->default('awarded');
            $table->timestamps();

            $table->unique(['source_system', 'source_notify_no', 'lot_code'], 'award_outcomes_unique_lot');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('award_outcomes');
    }
};
