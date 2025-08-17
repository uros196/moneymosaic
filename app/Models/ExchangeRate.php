<?php

namespace App\Models;

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

    protected $table = 'exchange_rates';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'date',
        'base_currency_code',
        'quote_currency_code',
        'rate_multiplier',
    ];

    /**
     * Attribute casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'rate_multiplier' => 'decimal:8',
        ];
    }
}
