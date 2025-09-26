<?php

namespace Tests\Unit\Rules;

use App\Enums\Currency;
use App\Rules\CurrencyFilter;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CurrencyFilterTest extends TestCase
{
    public function test_accepts_null_all_and_valid_codes(): void
    {
        foreach (Currency::values() as $code) {
            $validator = Validator::make(['currency' => $code], ['currency' => [new CurrencyFilter]]);
            $this->assertTrue($validator->passes(), "Should accept $code");
        }
    }

    public function test_rejects_invalid_value(): void
    {
        // Test illegal values
        foreach ([null, 'all', 'currency'] as $code) {
            $validator = Validator::make(['currency' => $code], ['currency' => [new CurrencyFilter]]);
            $this->assertTrue($validator->fails());
            $this->assertArrayHasKey('currency', $validator->errors()->toArray());
        }
    }
}
