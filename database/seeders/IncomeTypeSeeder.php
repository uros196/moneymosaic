<?php

namespace Database\Seeders;

use App\Models\IncomeType;
use Illuminate\Database\Seeder;

class IncomeTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = ['Salary', 'Bonus', 'Other'];

        foreach ($types as $name) {
            IncomeType::query()->firstOrCreate([
                'user_id' => null, // system type
                'name' => $name,
            ]);
        }
    }
}
