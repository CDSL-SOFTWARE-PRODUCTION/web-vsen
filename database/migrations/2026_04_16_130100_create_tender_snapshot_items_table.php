<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tender_snapshot_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tender_snapshot_id')->constrained('tender_snapshots')->cascadeOnDelete();

            $table->unsignedInteger('line_no');
            $table->string('name');
            $table->string('uom', 50)->nullable();
            $table->decimal('quantity_awarded', 18, 3)->default(0);

            $table->string('tender_item_ref')->nullable();
            $table->string('brand')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('origin_country')->nullable();
            $table->unsignedSmallInteger('manufacture_year')->nullable();

            $table->longText('spec_committed_raw')->nullable();
            $table->string('project_site')->nullable();
            $table->text('delivery_earliest_rule')->nullable();
            $table->text('delivery_latest_rule')->nullable();
            $table->text('other_requirements_raw')->nullable();

            $table->timestamps();

            $table->index(['tender_snapshot_id', 'line_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tender_snapshot_items');
    }
};

