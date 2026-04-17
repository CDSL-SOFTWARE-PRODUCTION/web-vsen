<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requirements', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 64)->unique();
            $table->string('type', 40);
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('type');
        });

        Schema::create('product_aliases', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('canonical_product_id')->constrained('canonical_products')->cascadeOnDelete();
            $table->string('alias_name', 512);
            $table->timestamps();

            $table->unique(['canonical_product_id', 'alias_name']);
        });

        Schema::create('canonical_product_requirement', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('canonical_product_id')->constrained('canonical_products')->cascadeOnDelete();
            $table->foreignId('requirement_id')->constrained('requirements')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['canonical_product_id', 'requirement_id']);
        });

        Schema::create('tender_snapshot_item_requirement', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tender_snapshot_item_id')->constrained('tender_snapshot_items')->cascadeOnDelete();
            $table->foreignId('requirement_id')->constrained('requirements')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['tender_snapshot_item_id', 'requirement_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tender_snapshot_item_requirement');
        Schema::dropIfExists('canonical_product_requirement');
        Schema::dropIfExists('product_aliases');
        Schema::dropIfExists('requirements');
    }
};
