<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tender_snapshot_attachments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tender_snapshot_id')->constrained('tender_snapshots')->cascadeOnDelete();

            $table->string('label')->nullable();
            $table->string('file_path')->nullable();
            $table->string('external_url')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size_bytes')->nullable();

            $table->foreignId('uploaded_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['tender_snapshot_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tender_snapshot_attachments');
    }
};

