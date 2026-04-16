<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_lists', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('channel', 50);
            $table->foreignId('partner_id')->nullable()->constrained('partners')->nullOnDelete();
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->timestamps();
        });

        Schema::create('price_list_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('price_list_id')->constrained('price_lists')->cascadeOnDelete();
            $table->string('product_name')->nullable();
            $table->decimal('unit_price', 18, 2);
            $table->unsignedInteger('min_qty')->default(1);
            $table->string('currency', 10)->default('VND');
            $table->timestamps();
        });

        Schema::create('sales_touchpoints', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('partner_id')->nullable()->constrained('partners')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('activity_type', 40)->default('Other');
            $table->timestamp('occurred_at')->nullable();
            $table->text('summary');
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('order_items', function (Blueprint $table): void {
            $table->foreignId('price_list_item_id')
                ->nullable()
                ->after('status')
                ->constrained('price_list_items')
                ->nullOnDelete();
            $table->decimal('unit_price', 18, 2)->nullable()->after('price_list_item_id');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('price_list_item_id');
            $table->dropColumn('unit_price');
        });

        Schema::dropIfExists('sales_touchpoints');
        Schema::dropIfExists('price_list_items');
        Schema::dropIfExists('price_lists');
    }
};
