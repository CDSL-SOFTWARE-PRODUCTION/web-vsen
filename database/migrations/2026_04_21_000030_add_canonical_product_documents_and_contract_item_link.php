<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('canonical_product_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('canonical_product_id')->constrained('canonical_products')->cascadeOnDelete();
            $table->string('document_type', 100);
            $table->string('document_group', 50)->nullable();
            $table->string('status', 20)->default('required');
            $table->string('file_path')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['canonical_product_id', 'document_type']);
            $table->index(['status', 'expiry_date']);
        });

        Schema::table('contract_items', function (Blueprint $table): void {
            $table->foreignId('canonical_product_id')
                ->nullable()
                ->after('partner_id')
                ->constrained('canonical_products')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('contract_items', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('canonical_product_id');
        });

        Schema::dropIfExists('canonical_product_documents');
    }
};
