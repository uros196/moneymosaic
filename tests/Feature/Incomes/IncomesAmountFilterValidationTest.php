<?php

namespace Tests\Feature;

use App\Enums\Currency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncomesAmountFilterValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_amount_max_is_allowed(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('incomes.index', [
            // Provide only max in major units; request will convert it to minor before validation
            'amount_max' => '10.50',
            'currency_code' => Currency::EUR->value,
        ]));

        $response->assertOk();
    }

    public function test_amount_max_must_be_greater_than_min_when_min_is_present_equal_fails(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('incomes.index', [
            'amount_min' => '100',
            'amount_max' => '100',
            'currency_code' => Currency::EUR->value,
        ]));

        $response->assertSessionHasErrors('amount_minor_max');
    }

    public function test_amount_max_must_be_greater_than_min_when_min_is_present_lower_fails(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('incomes.index', [
            'amount_min' => '100',
            'amount_max' => '50',
            'currency_code' => Currency::EUR->value,
        ]));

        $response->assertSessionHasErrors('amount_minor_max');
    }
}
