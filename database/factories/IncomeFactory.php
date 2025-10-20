<?php

namespace Database\Factories;

use App\Enums\Currency;
use App\Models\Income;
use App\Models\IncomeType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Income>
 */
class IncomeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Income::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $currency = fake()->randomElement(Currency::cases());
        $incomeType = IncomeType::factory()->create();

        // Slightly different typical ranges by type
        $amount = match ($incomeType->name) {
            'salary' => fake()->numberBetween(8000, 80000), // 800.00 - 8,000.00
            'bonus' => fake()->numberBetween(1000, 30000),  // 100.00 - 3,000.00
            default => fake()->numberBetween(100, 20000),   // 10.00 - 2,000.00
        };

        return [
            'user_id' => User::factory(),
            'amount_minor' => $amount,
            'currency_code' => $currency,
            'income_type_id' => $incomeType,
            'description' => fake()->optional(0.6)->sentence(),
            'occurred_on' => fake()->dateTimeBetween('-2 years', 'now'),
        ];
    }
}
