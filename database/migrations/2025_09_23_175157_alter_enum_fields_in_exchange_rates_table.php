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
        Schema::table('exchange_rates', function (Blueprint $table) {
            $table->string('base_currency_code', 3)->default('EUR')->change();
            $table->string('quote_currency_code', 3)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exchange_rates', function (Blueprint $table) {
            $table->enum('base_currency_code', ['USD', 'EUR', 'RSD'])->default('EUR')->change();
            $table->enum('quote_currency_code', ['USD', 'EUR', 'RSD'])->change();
        });
    }
};
