<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_device_declaration_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('medical_device_declaration_id')
                ->constrained('medical_device_declarations')
                ->cascadeOnDelete();
            $table->string('document_type', 100);
            $table->string('status', 20)->default('required');
            $table->string('file_path')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['medical_device_declaration_id', 'document_type'], 'mdd_documents_declaration_type');
            $table->index(['status', 'expiry_date'], 'mdd_documents_status_expiry');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_device_declaration_documents');
    }
};
