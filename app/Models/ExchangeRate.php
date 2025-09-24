<?php

namespace App\Models;

use App\Enums\Currency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Stores daily currency exchange rates relative to a base currency.
 *
 * Fields:
 * - date: The effective date of the rate.
 * - base_currency_code / quote_currency_code: ISO 4217 currency codes.
 * - rate_multiplier: Decimal multiplier to convert base -> quote.
 */
class ExchangeRate extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'date',
        'base_currency_code',
        'quote_currency_code',
        'rate_multiplier',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'base_currency_code' => Currency::class,
            'quote_currency_code' => Currency::class,
            'rate_multiplier' => 'decimal:8',
        ];
    }
}
