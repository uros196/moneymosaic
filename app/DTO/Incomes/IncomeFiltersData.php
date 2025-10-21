<?php

namespace App\DTO\Incomes;

use App\DTO\Sort;
use App\Enums\Currency;
use App\Http\Requests\Incomes\IndexIncomeRequest;
use Illuminate\Support\Carbon;

/**
 * Data Transfer Object carrying filters for listing Incomes.
 */
final readonly class IncomeFiltersData
{
    /**
     * @param  array<int, string>|null  $tags
     */
    public function __construct(
        public ?int $incomeTypeId = null,
        public ?Currency $currency = null,
        public ?string $query = null,
        public ?Carbon $dateFrom = null,
        public ?Carbon $dateTo = null,
        public ?int $amountMinorMin = null,
        public ?int $amountMinorMax = null,
        public ?bool $onlyWithProjectLink = null,
        public ?array $tags = null,
        public ?Sort $sort = null,
    ) {}

    /**
     * Creates a new instance from an IndexIncomeRequest.
     */
    public static function fromRequest(IndexIncomeRequest $request): self
    {
        $data = $request->safe();

        return new self(
            // filter params
            incomeTypeId: $request->integer('income_type') ?: null,
            currency: $data->enum('currency_code', Currency::class),
            query: $data->input('query'),
            dateFrom: $data->date('date_from'),
            dateTo: $data->date('date_to'),
            amountMinorMin: $data->filled('amount_minor_min') ? $data->integer('amount_minor_min') : null,
            amountMinorMax: $data->filled('amount_minor_max') ? $data->integer('amount_minor_max') : null,
            onlyWithProjectLink: $data->filled('only_with_project_link') ? $data->boolean('only_with_project_link') : null,
            tags: $data->filled('tags') ? $data->array('tags') : null,

            // sort param
            sort: $data->filled('sort') ? Sort::fromString($data->input('sort')) : null,
        );
    }
}
