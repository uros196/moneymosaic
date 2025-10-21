<?php

namespace Database\Seeders;

use App\DTO\Incomes\IncomeData;
use App\Enums\Currency;
use App\Models\Income;
use App\Models\IncomeType;
use App\Models\User;
use App\Repositories\Contracts\IncomeTypeRepository;
use App\Services\IncomeService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class RealisticDemoSeeder extends Seeder
{
    /**
     * Seed a realistic dataset for local development and demos.
     */
    public function run(): void
    {
        $incomeTypes = app(IncomeTypeRepository::class);
        $incomeService = app(IncomeService::class);

        // 1) Ensure base/reference seeders are executed first
        $this->call([
            IncomeTypeSeeder::class,
            ExchangeRatesSeeder::class,
        ]);

        // 2) Create a few users with diverse locales and default currencies
        $users = collect([
            [
                'name' => 'Nikola Petrović',
                'email' => 'nikola@example.com',
                'locale' => 'sr',
                'default_currency_code' => Currency::RSD,
            ],
            [
                'name' => 'Mark Johnson',
                'email' => 'mark@example.com',
                'locale' => 'en',
                'default_currency_code' => Currency::EUR,
            ],
            [
                'name' => 'Sofia Müller',
                'email' => 'sofia@example.com',
                'locale' => 'en',
                'default_currency_code' => Currency::CHF,
            ],
        ])->map(function (array $attrs) {
            return User::query()->firstOrCreate(
                ['email' => $attrs['email']],
                array_merge($attrs, [
                    'email_verified_at' => now(),
                    'password' => 'password',
                ]),
            );
        });

        // 3) System types (from IncomeTypeSeeder) + a few user-specific types
        $customTypes = [
            ['en' => 'Freelance', 'sr' => 'Freelans'],
            ['en' => 'Rental', 'sr' => 'Izdavanje'],
            ['en' => 'Gift', 'sr' => 'Poklon'],
            ['en' => 'Dividends', 'sr' => 'Dividende'],
            ['en' => 'Consulting', 'sr' => 'Konsalting'],
        ];

        $users->each(function (User $user) use ($customTypes) {
            foreach (Arr::random($customTypes, 2) as $name) {
                IncomeType::query()->firstOrCreate([
                    'user_id' => $user->getKey(),
                    'name' => $name,
                ]);
            }
        });

        // 4) Generate realistic incomes for the last ~18 months
        $start = Carbon::today()->subMonths(18)->startOfMonth();
        $end = Carbon::today();

        $users->each(function (User $user) use ($start, $end, $incomeTypes, $incomeService) {

            // Visible types (system + user-defined)
            $visibleTypes = $incomeTypes->visibleForUser($user);
            // User-created types
            $userTypes = $incomeTypes->createdByUser($user);
            // Fallback to system types only if the user has none
            if ($userTypes->isEmpty()) {
                $userTypes = $visibleTypes->filter(fn ($t) => $t->user_id === null)->values();
            }

            // Monthly salary-like income on the 1st (or next weekday)
            $baseCurrency = $user->default_currency_code ?? Currency::default();
            $current = $start->clone();
            while ($current->lessThanOrEqualTo($end)) {
                $date = self::firstBusinessDayOfMonth($current->clone());
                $salaryMinor = self::approximateAmountMinor(min: 120_000, max: 280_000, jitter: 7_500); // 1,200.00 - 2,800.00
                $salaryType = $visibleTypes->firstWhere('name->en', 'Salary') ?? $visibleTypes->first();

                $incomeService->save(
                    new IncomeData([
                        'user_id' => $user->getKey(),
                        'name' => __('Salary for :month', ['month' => $date->isoFormat('MMMM Y')], $user->locale ?? app()->getLocale()),
                        'amount_minor' => $salaryMinor,
                        'currency_code' => $baseCurrency,
                        'income_type_id' => $salaryType?->getKey(),
                        'description' => fake()->optional(0.4)->sentence(),
                        'occurred_on' => $date->toDateString(),
                    ], self::tagsForType('salary', true)),
                    Income::make()
                );

                $current->addMonth();
            }

            // Random one-off incomes: bonus, freelance, gifts, etc.
            $additionalCount = fake()->numberBetween(20, 50);
            for ($i = 0; $i < $additionalCount; $i++) {
                $occurred = Carbon::instance(fake()->dateTimeBetween($start, $end));

                // choose a type: 60% user custom, 40% from visible/system pool
                $type = fake()->boolean(60)
                    ? $userTypes->random()
                    : $visibleTypes->random();

                // Choose currency: 70% user's default, 30% random of supported
                $currency = fake()->boolean(70)
                    ? ($user->default_currency_code ?? Currency::default())
                    : fake()->randomElement(Currency::cases());

                // Amount ranges depend on a rough category heuristic
                $amountMinor = match (Str::lower($type->name)) {
                    'bonus' => self::approximateAmountMinor(20_000, 80_000, 10_000),
                    'freelance', 'consulting' => self::approximateAmountMinor(10_000, 180_000, 20_000),
                    'rental' => self::approximateAmountMinor(50_000, 180_000, 5_000),
                    'dividends' => self::approximateAmountMinor(5_000, 60_000, 5_000),
                    'gift' => self::approximateAmountMinor(5_000, 40_000, 5_000),
                    default => self::approximateAmountMinor(5_000, 120_000, 5_000),
                };

                $incomeService->save(
                    new IncomeData([
                        'user_id' => $user->getKey(),
                        'name' => self::randomIncomeTitle($type->name, $occurred, $user->locale ?? app()->getLocale()),
                        'amount_minor' => $amountMinor,
                        'currency_code' => $currency,
                        'income_type_id' => $type->getKey(),
                        'description' => fake()->optional(0.5)->sentence(),
                        'occurred_on' => $occurred->toDateString(),
                    ], self::tagsForType($type->name)),
                    Income::make()
                );
            }
        });
    }

    /**
     * Return approximate minor units amount between a range with some jitter.
     */
    private static function approximateAmountMinor(int $min, int $max, int $jitter): int
    {
        $base = fake()->numberBetween($min, $max);

        return max($min, min($max, $base + fake()->numberBetween(-$jitter, $jitter)));
    }

    /**
     * Get the first business day of the month (Mon-Fri). If the 1st is weekend, shift forward.
     */
    private static function firstBusinessDayOfMonth(Carbon $date): Carbon
    {
        $date->day(1);
        while ($date->isWeekend()) {
            $date->addDay();
        }

        return $date;
    }

    /**
     * Generate a readable income title given type and date.
     */
    private static function randomIncomeTitle(string $typeEn, Carbon $date, ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $month = $date->isoFormat('MMMM Y');

        return match (Str::lower($typeEn)) {
            'bonus' => __('Bonus (:month)', ['month' => $month], $locale),
            'freelance' => __('Freelance project (:month)', ['month' => $month], $locale),
            'consulting' => __('Consulting fee (:month)', ['month' => $month], $locale),
            'rental' => __('Rental income (:month)', ['month' => $month], $locale),
            'dividends' => __('Dividends (:month)', ['month' => $month], $locale),
            'gift' => __('Gift', [], $locale),
            default => __('Other income', [], $locale),
        };
    }

    /**
     * Generate demo tags for a given income type.
     *
     * @param  string  $typeName  Income type name (in any locale or lowercase). Used only heuristically.
     * @param  bool  $recurring  Whether this income is recurring (e.g. salary).
     * @return array<int,string>
     */
    private static function tagsForType(string $typeName, bool $recurring = false): array
    {
        // Normalize name to lowercase string
        $key = Str::lower($typeName);

        $base = match ($key) {
            'salary' => ['salary'],
            'bonus' => ['bonus'],
            'freelance' => ['freelance', 'side-hustle'],
            'consulting' => ['consulting', 'client-work'],
            'rental' => ['rental', 'passive'],
            'dividends' => ['dividends', 'investments', 'passive'],
            'gift' => ['gift'],
            default => ['income'],
        };

        if ($recurring) {
            $base[] = 'recurring';
            $base[] = 'monthly';
        } else {
            $base[] = 'one-off';
        }

        // Occasionally add one extra contextual tag to increase realism
        $extrasPool = ['q1', 'q2', 'q3', 'q4', 'family', 'education', 'health', 'invoice', 'late', 'company'];
        if (fake()->boolean(35)) {
            $base[] = fake()->randomElement($extrasPool);
        }

        return array_values(array_unique($base));
    }
}
