<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('contract_id')->constrained('contracts')->cascadeOnDelete();
            $table->string('invoice_code')->unique();
            $table->decimal('total_amount', 18, 2);
            $table->string('status', 20)->default('Draft');
            $table->date('payment_due_date')->nullable();
            $table->unsignedInteger('days_overdue_cached')->default(0);
            $table->string('misa_transaction_id', 120)->nullable();
            $table->foreignId('replaced_by_invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->timestamps();

            $table->index(['contract_id', 'status']);
            $table->index(['payment_due_date', 'status']);
        });

        Schema::create('financial_ledger_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('contract_id')->nullable()->constrained('contracts')->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->foreignId('partner_id')->nullable()->constrained('partners')->nullOnDelete();
            $table->foreignId('related_ledger_entry_id')->nullable()->constrained('financial_ledger_entries')->nullOnDelete();
            $table->string('type', 30);
            $table->decimal('amount', 18, 2);
            $table->string('memo', 255)->nullable();
            $table->timestamps();

            $table->index(['type', 'created_at']);
            $table->index('invoice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_ledger_entries');
        Schema::dropIfExists('invoices');
    }
};
