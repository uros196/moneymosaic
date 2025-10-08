<?php

namespace App\DTO\Incomes;

use App\DTO\Sort;
use App\Enums\Currency;
use App\Http\Requests\Incomes\IndexIncomeRequest;
use App\Models\IncomeType;
use App\Support\FilterChips\ArrayChip;
use App\Support\FilterChips\ChipCollection;
use App\Support\FilterChips\CurrencyChip;
use App\Support\FilterChips\DateRangeChip;
use App\Support\FilterChips\MinorMinMaxRangeChip;
use App\Support\FilterChips\ModelChip;
use App\Support\FilterChips\StringChip;
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

    /**
     * Get a filter chips collection for current filters.
     */
    public function chips(): ChipCollection
    {
        return ChipCollection::make()
            // Free-text search
            ->addChip(StringChip::make($this->query)->label(__('common.search'))->removeKeys('query'))
            // Date range
            ->addChip(DateRangeChip::make($this->dateFrom, $this->dateTo)->label(__('incomes.table.date')))
            // Income Type (model chip)
            ->addChip(
                ModelChip::make(IncomeType::class, $this->incomeTypeId)
                    ->label(__('incomes.filters.type'))
                    ->removeKeys('income_type')
            )
            // Amount range (minor units will be converted to major)
            ->addChip(
                MinorMinMaxRangeChip::make($this->amountMinorMin, $this->amountMinorMax)
                    ->usingCurrency($this->currency)
                    ->label(__('incomes.table.amount'))
                    ->removeKeys('amount_min', 'amount_max')
            )
            // Currency
            ->addChip(CurrencyChip::make($this->currency)->label(__('incomes.filters.currency')))
            // Tags (array chip)
            ->addChip(ArrayChip::make($this->tags)->label(__('incomes.table.tags'))->removeKeys('tags'));
    }
}
