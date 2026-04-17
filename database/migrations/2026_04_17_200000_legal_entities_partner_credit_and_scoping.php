<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_entities', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('tax_code', 32)->nullable();
            $table->timestamps();
        });

        $defaultEntityId = DB::table('legal_entities')->insertGetId([
            'name' => 'Default Legal Entity',
            'tax_code' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('legal_entity_id')
                ->nullable()
                ->after('role')
                ->constrained('legal_entities')
                ->nullOnDelete();
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->foreignId('legal_entity_id')
                ->nullable()
                ->after('id')
                ->constrained('legal_entities')
                ->nullOnDelete();
        });

        DB::table('orders')->update(['legal_entity_id' => $defaultEntityId]);

        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table): void {
                $table->foreignId('legal_entity_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('legal_entities')
                    ->nullOnDelete();
            });

            DB::table('invoices')->orderBy('id')->chunkById(100, function ($rows) use ($defaultEntityId): void {
                foreach ($rows as $row) {
                    $le = DB::table('orders')->where('id', $row->order_id)->value('legal_entity_id')
                        ?? $defaultEntityId;
                    DB::table('invoices')->where('id', $row->id)->update(['legal_entity_id' => $le]);
                }
            });
        }

        Schema::table('partners', function (Blueprint $table): void {
            $table->decimal('credit_limit', 18, 2)->nullable()->after('reliability_note');
            $table->decimal('outstanding_balance_cached', 18, 2)->default(0)->after('credit_limit');
            $table->unsignedInteger('max_overdue_days_cached')->default(0)->after('outstanding_balance_cached');
        });

        Schema::table('contracts', function (Blueprint $table): void {
            $table->foreignId('customer_partner_id')
                ->nullable()
                ->after('customer_name')
                ->constrained('partners')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('customer_partner_id');
        });

        if (Schema::hasTable('invoices') && Schema::hasColumn('invoices', 'legal_entity_id')) {
            Schema::table('invoices', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('legal_entity_id');
            });
        }

        Schema::table('partners', function (Blueprint $table): void {
            $table->dropColumn(['credit_limit', 'outstanding_balance_cached', 'max_overdue_days_cached']);
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('legal_entity_id');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('legal_entity_id');
        });

        Schema::dropIfExists('legal_entities');
    }
};
