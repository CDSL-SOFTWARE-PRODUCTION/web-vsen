<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('canonical_products')) {
            return;
        }

        if (! Schema::hasColumn('canonical_products', 'image_urls')) {
            Schema::table('canonical_products', function (Blueprint $table): void {
                $table->json('image_urls')->nullable()->after('spec_json');
            });
        }

        if (Schema::hasColumn('canonical_products', 'image_url')) {
            DB::table('canonical_products')
                ->whereNotNull('image_url')
                ->where('image_url', '!=', '')
                ->orderBy('id')
                ->chunkById(200, function ($rows): void {
                    foreach ($rows as $row) {
                        DB::table('canonical_products')->where('id', $row->id)->update([
                            'image_urls' => json_encode([$row->image_url]),
                        ]);
                    }
                });

            Schema::table('canonical_products', function (Blueprint $table): void {
                $table->dropColumn('image_url');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('canonical_products')) {
            return;
        }

        if (! Schema::hasColumn('canonical_products', 'image_url')) {
            Schema::table('canonical_products', function (Blueprint $table): void {
                $table->string('image_url', 2048)->nullable()->after('spec_json');
            });
        }

        if (Schema::hasColumn('canonical_products', 'image_urls')) {
            DB::table('canonical_products')
                ->whereNotNull('image_urls')
                ->orderBy('id')
                ->chunkById(200, function ($rows): void {
                    foreach ($rows as $row) {
                        $urls = json_decode($row->image_urls, true);
                        $first = is_array($urls) && isset($urls[0]) && is_string($urls[0]) ? $urls[0] : null;
                        DB::table('canonical_products')->where('id', $row->id)->update([
                            'image_url' => $first,
                        ]);
                    }
                });

            Schema::table('canonical_products', function (Blueprint $table): void {
                $table->dropColumn('image_urls');
            });
        }
    }
};
