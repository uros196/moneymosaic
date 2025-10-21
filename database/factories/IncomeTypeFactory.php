<?php

namespace Database\Factories;

use App\Models\IncomeType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IncomeType>
 */
class IncomeTypeFactory extends Factory
{
    protected $model = IncomeType::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->randomElement(['Salary', 'Bonus', 'Other', ucfirst(fake()->unique()->word())]);

        return [
            // null means system type; tests can override to create user-owned types
            'user_id' => null,
            'name' => $name,
        ];
    }
}
