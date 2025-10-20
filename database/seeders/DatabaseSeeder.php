<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed system income types and sample exchange rates for development/testing
        $this->call([
            // turn it off for now, it's causing duplicated entries
            // IncomeTypeSeeder::class,
            ExchangeRatesSeeder::class,
        ]);

        // Create a default test user (kept for backward compatibility)
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Seed a larger realistic dataset for local/testing environments
        if (app()->environment(['local', 'testing'])) {
            $this->call(RealisticDemoSeeder::class);
        }
    }
}
