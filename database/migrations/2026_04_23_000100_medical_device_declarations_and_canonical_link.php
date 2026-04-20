<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('medical_device_declarations')) {
            Schema::create('medical_device_declarations', function (Blueprint $table): void {
                $table->id();
                $table->string('declaration_number', 128)->unique();
                $table->date('declared_on')->nullable();
                $table->string('issuer', 255)->nullable();
                $table->string('device_risk_class', 1)->nullable();
                $table->string('device_name_official', 512)->nullable();
                $table->text('declaring_organization')->nullable();
                $table->text('declaring_address')->nullable();
                $table->string('internal_reference_code', 128)->nullable();
                $table->date('internal_reference_date')->nullable();
                $table->text('quality_standard')->nullable();
                $table->text('legal_owner_name')->nullable();
                $table->text('legal_owner_address')->nullable();
                $table->json('warranty')->nullable();
                $table->text('notes')->nullable();
                $table->json('extra')->nullable();
                $table->timestamps();

                $table->index(['declared_on', 'device_risk_class']);
            });
        }

        if (Schema::hasTable('canonical_products') && ! Schema::hasColumn('canonical_products', 'medical_device_declaration_id')) {
            Schema::table('canonical_products', function (Blueprint $table): void {
                $table->foreignId('medical_device_declaration_id')
                    ->nullable()
                    ->constrained('medical_device_declarations')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('canonical_products') && Schema::hasColumn('canonical_products', 'medical_device_declaration_id')) {
            Schema::table('canonical_products', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('medical_device_declaration_id');
            });
        }

        Schema::dropIfExists('medical_device_declarations');
    }
};
