<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('product_families')) {
            Schema::create('product_families', function (Blueprint $table): void {
                $table->id();
                $table->string('name', 255);
                $table->foreignId('medical_device_declaration_id')
                    ->nullable()
                    ->constrained('medical_device_declarations')
                    ->nullOnDelete();
                $table->text('description')->nullable();
                $table->timestamps();

                $table->index(['medical_device_declaration_id', 'name']);
            });
        }

        if (Schema::hasTable('canonical_products') && ! Schema::hasColumn('canonical_products', 'product_family_id')) {
            Schema::table('canonical_products', function (Blueprint $table): void {
                $table->foreignId('product_family_id')
                    ->nullable()
                    ->after('medical_device_declaration_id')
                    ->constrained('product_families')
                    ->nullOnDelete();
            });
        }

        $this->backfillFamilies();
    }

    public function down(): void
    {
        if (Schema::hasTable('canonical_products') && Schema::hasColumn('canonical_products', 'product_family_id')) {
            Schema::table('canonical_products', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('product_family_id');
            });
        }

        Schema::dropIfExists('product_families');
    }

    private function backfillFamilies(): void
    {
        if (! Schema::hasTable('canonical_products') || ! Schema::hasTable('product_families')) {
            return;
        }

        $groups = DB::table('canonical_products')
            ->select('medical_device_declaration_id', 'raw_name')
            ->whereNull('product_family_id')
            ->groupBy('medical_device_declaration_id', 'raw_name')
            ->get();

        foreach ($groups as $group) {
            $name = trim((string) $group->raw_name);
            if ($name === '') {
                continue;
            }

            $existing = DB::table('product_families')
                ->where('name', $name)
                ->where('medical_device_declaration_id', $group->medical_device_declaration_id)
                ->value('id');

            $familyId = $existing;
            if ($familyId === null) {
                $familyId = DB::table('product_families')->insertGetId([
                    'name' => $name,
                    'medical_device_declaration_id' => $group->medical_device_declaration_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('canonical_products')
                ->where('raw_name', $name)
                ->where('medical_device_declaration_id', $group->medical_device_declaration_id)
                ->whereNull('product_family_id')
                ->update([
                    'product_family_id' => $familyId,
                    'updated_at' => now(),
                ]);
        }
    }
};
