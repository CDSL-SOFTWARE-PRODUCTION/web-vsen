<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tender_snapshots', function (Blueprint $table): void {
            $table->unsignedInteger('snapshot_version')->default(1)->after('snapshot_hash');
        });

        Schema::table('contracts', function (Blueprint $table): void {
            $table->foreignId('tender_snapshot_id')
                ->nullable()
                ->after('tender_snapshot_ref')
                ->constrained('tender_snapshots')
                ->nullOnDelete();
        });

        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->string('order_code')->unique();
            $table->string('name');
            $table->string('state', 40)->default('SubmitTender');
            $table->foreignId('tender_snapshot_id')->nullable()->constrained('tender_snapshots')->nullOnDelete();
            $table->timestamp('awarded_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('execution_started_at')->nullable();
            $table->timestamps();

            $table->index(['state', 'created_at']);
        });

        Schema::create('order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->unsignedInteger('line_no');
            $table->string('name');
            $table->string('uom', 50)->nullable();
            $table->decimal('quantity', 18, 3)->default(0);
            $table->string('status', 40)->default('planned');
            $table->timestamps();

            $table->unique(['order_id', 'line_no']);
        });

        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('entity_type', 80);
            $table->unsignedBigInteger('entity_id');
            $table->string('action', 120);
            $table->json('context')->nullable();
            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
            $table->index(['action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');

        Schema::table('contracts', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('tender_snapshot_id');
        });

        Schema::table('tender_snapshots', function (Blueprint $table): void {
            $table->dropColumn('snapshot_version');
        });
    }
};

