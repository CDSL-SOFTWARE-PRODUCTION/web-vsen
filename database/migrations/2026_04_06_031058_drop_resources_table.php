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
        Schema::dropIfExists('resources');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('resource_type'); // brochure, manual, driver, software...
            $table->string('file_path')->nullable(); // the uploaded file location if hosted locally
            $table->string('file_url')->nullable(); // external url if hosted elsewhere
            $table->string('thumbnail')->nullable();
            $table->string('specialty')->nullable();
            $table->year('publish_year')->nullable();
            $table->integer('download_count')->default(0);
            $table->boolean('is_public')->default(true);
            $table->timestamps();
        });
    }
};
