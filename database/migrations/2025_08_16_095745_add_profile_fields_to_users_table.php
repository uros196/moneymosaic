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
        Schema::table('users', function (Blueprint $table) {
            $table->string('surname')->nullable()->after('name');
            $table->enum('preferred_language', ['en', 'sr'])->default('en')->after('email');
            $table->enum('default_currency_code', ['USD', 'EUR', 'RSD'])->default('EUR')->after('preferred_language');
            $table->string('timezone')->default('Europe/Belgrade')->after('default_currency_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['surname', 'preferred_language', 'default_currency_code', 'timezone']);
        });
    }
};
