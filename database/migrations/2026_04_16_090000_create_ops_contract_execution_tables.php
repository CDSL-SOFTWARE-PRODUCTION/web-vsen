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
        Schema::create('partners', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('type', 50)->default('Supplier');
            $table->string('segment', 50)->nullable();
            $table->unsignedInteger('lead_time_days')->default(7);
            $table->text('reliability_note')->nullable();
            $table->timestamps();

            $table->index(['type', 'name']);
        });

        Schema::create('contracts', function (Blueprint $table): void {
            $table->id();
            $table->string('contract_code')->unique();
            $table->string('name');
            $table->string('customer_name');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('allocated_budget', 18, 2)->default(0);
            $table->date('next_delivery_due_date')->nullable();
            $table->string('risk_level', 20)->default('Green');
            $table->unsignedInteger('open_items_count')->default(0);
            $table->unsignedInteger('open_issues_count')->default(0);
            $table->unsignedInteger('missing_docs_count')->default(0);
            $table->decimal('cash_needed_14d', 18, 2)->default(0);
            $table->timestamps();

            $table->index(['risk_level', 'next_delivery_due_date']);
        });

        Schema::create('contract_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->foreignId('partner_id')->nullable()->constrained('partners')->nullOnDelete();
            $table->string('item_code');
            $table->string('name');
            $table->text('spec')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->date('delivery_deadline');
            $table->unsignedInteger('lead_time_days')->default(7);
            $table->string('status', 50)->default('not_ordered');
            $table->string('docs_status', 20)->default('missing');
            $table->string('cash_status', 20)->default('not_needed');
            $table->boolean('is_critical')->default(false);
            $table->string('line_risk_level', 20)->default('Green');
            $table->timestamps();

            $table->index(['contract_id', 'delivery_deadline']);
            $table->index(['status', 'line_risk_level']);
        });

        Schema::create('execution_issues', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('contract_id')->constrained('contracts')->cascadeOnDelete();
            $table->foreignId('contract_item_id')->nullable()->constrained('contract_items')->nullOnDelete();
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('issue_type', 50);
            $table->string('severity', 20)->default('Medium');
            $table->json('impact_flags')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->string('status', 30)->default('Open');
            $table->text('description')->nullable();
            $table->text('resolution_note')->nullable();
            $table->timestamps();

            $table->index(['status', 'due_at']);
            $table->index(['contract_id', 'issue_type']);
        });

        Schema::create('documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('contract_id')->constrained('contracts')->cascadeOnDelete();
            $table->foreignId('contract_item_id')->nullable()->constrained('contract_items')->nullOnDelete();
            $table->foreignId('payment_milestone_id')->nullable()->nullOnDelete();
            $table->string('document_group', 50);
            $table->string('document_type', 100);
            $table->string('status', 20)->default('missing');
            $table->string('file_path')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'expiry_date']);
            $table->index(['contract_id', 'document_group']);
        });

        Schema::create('payment_milestones', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('contract_id')->constrained('contracts')->cascadeOnDelete();
            $table->string('name');
            $table->date('due_date');
            $table->decimal('amount_planned', 18, 2);
            $table->string('checklist_status', 20)->default('pending');
            $table->boolean('payment_ready')->default(false);
            $table->timestamps();

            $table->index(['due_date', 'checklist_status']);
        });

        Schema::create('cash_plan_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('contract_id')->nullable()->constrained('contracts')->nullOnDelete();
            $table->foreignId('partner_id')->nullable()->constrained('partners')->nullOnDelete();
            $table->date('scheduled_date');
            $table->decimal('amount', 18, 2);
            $table->string('purpose', 50)->default('Other');
            $table->timestamps();

            $table->index(['scheduled_date', 'amount']);
        });

        Schema::table('documents', function (Blueprint $table): void {
            $table->foreign('payment_milestone_id')->references('id')->on('payment_milestones')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table): void {
            $table->dropForeign(['payment_milestone_id']);
        });

        Schema::dropIfExists('cash_plan_events');
        Schema::dropIfExists('payment_milestones');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('execution_issues');
        Schema::dropIfExists('contract_items');
        Schema::dropIfExists('contracts');
        Schema::dropIfExists('partners');
    }
};
