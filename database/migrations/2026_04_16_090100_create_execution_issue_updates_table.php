<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('execution_issue_updates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('execution_issue_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status_from', 30)->nullable();
            $table->string('status_to', 30)->nullable();
            $table->text('note')->nullable();
            $table->string('attachment_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('execution_issue_updates');
    }
};
