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
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->date('date')->index();
            $table->enum('base_currency_code', ['USD', 'EUR', 'RSD'])->default('EUR');
            $table->enum('quote_currency_code', ['USD', 'EUR', 'RSD']);
            $table->decimal('rate_multiplier', 20, 8);
            $table->timestamps();

            $table->unique(['date', 'base_currency_code', 'quote_currency_code'], 'exchange_rates_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
