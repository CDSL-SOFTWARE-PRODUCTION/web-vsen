<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bid_opening_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tender_snapshot_id')->nullable()->constrained('tender_snapshots')->nullOnDelete();
            $table->string('source_system', 50)->default('muasamcong');
            $table->string('source_notify_no')->index();
            $table->string('source_plan_no')->nullable()->index();
            $table->unsignedInteger('session_version')->default(1);
            $table->timestamp('opened_at')->nullable()->index();
            $table->unsignedInteger('total_bidders')->default(0);
            $table->string('source_url')->nullable();
            $table->string('raw_payload_hash', 64)->nullable()->index();
            $table->timestamps();

            $table->unique(['source_system', 'source_notify_no', 'session_version'], 'bid_opening_sessions_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bid_opening_sessions');
    }
};
