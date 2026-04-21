<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('founder_work_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('founder_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('summary')->nullable();
            $table->string('assignee_label')->nullable();
            $table->timestampTz('due_at')->nullable();
            $table->string('status', 32)->default('open');
            $table->string('digest_lane', 32)->default('general');
            $table->json('attachment_urls')->nullable();
            $table->timestamps();

            $table->index(['founder_user_id', 'status']);
            $table->index(['founder_user_id', 'digest_lane', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('founder_work_cards');
    }
};
