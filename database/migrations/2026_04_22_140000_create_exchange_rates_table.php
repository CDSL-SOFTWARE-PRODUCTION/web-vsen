<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exchange_rates', function (Blueprint $table): void {
            $table->id();
            $table->string('quote_currency', 3);
            $table->string('base_currency', 3);
            $table->decimal('rate', 18, 6);
            $table->dateTime('effective_at');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['quote_currency', 'base_currency', 'effective_at'], 'exchange_rates_quote_base_effective_unique');
            $table->index(['quote_currency', 'base_currency', 'effective_at'], 'exchange_rates_lookup_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
