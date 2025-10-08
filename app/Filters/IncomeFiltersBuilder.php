<?php

namespace App\Filters;

use App\Contract\DataFilterBuilder;
use App\DTO\Incomes\IncomeFiltersData;
use App\Enums\Currency;
use App\Http\Requests\Incomes\IndexIncomeRequest;
use App\Models\IncomeType;
use App\Repositories\Contracts\IncomeTypeRepository;
use App\Services\TagService;
use App\Support\Filters\Chips\ArrayChip;
use App\Support\Filters\Chips\ChipCollection;
use App\Support\Filters\Chips\CurrencyChip;
use App\Support\Filters\Chips\DateRangeChip;
use App\Support\Filters\Chips\MinorMinMaxRangeChip;
use App\Support\Filters\Chips\ModelChip;
use App\Support\Filters\Chips\StringChip;
use App\Support\Filters\Fields\DateRangeField;
use App\Support\Filters\Fields\FilterFieldsCollection;
use App\Support\Filters\Fields\InputField;
use App\Support\Filters\Fields\MinMaxField;
use App\Support\Filters\Fields\SelectField;
use App\Support\Filters\Fields\TagsField;
use Illuminate\Http\Request;

/**
 * Builds filter field definitions and chip representations for the Incomes index page.
 */
class IncomeFiltersBuilder implements DataFilterBuilder
{
    /**
     * Create a new instance of the builder.
     */
    public function __construct(protected Request $request, protected IncomeFiltersData $filtersDTO) {}

    /**
     * Create a new instance of the builder based on the request.
     */
    public static function buildFrom(IndexIncomeRequest $request): static
    {
        return new static($request, IncomeFiltersData::fromRequest($request));
    }

    /**
     * Build a filter fields collection for the income index page.
     */
    public function buildFilter(): FilterFieldsCollection
    {
        $user = $this->request->user();

        $incomeTypes = app(IncomeTypeRepository::class);
        $tagService = app(TagService::class);

        return FilterFieldsCollection::make()
            // Free-text search
            ->add(InputField::make('query', __('common.search'))->placeholder(__('common.search')))
            // Date range
            ->add(DateRangeField::make('date', __('incomes.table.date'))->keys('date_from', 'date_to'))
            // Amount range (major units, front will convert to minor by request prepared)
            ->add(MinMaxField::make('amount', __('incomes.table.amount'))->keys('amount_min', 'amount_max'))
            // Tags with suggestions
            ->add(
                TagsField::make('tags', __('incomes.table.tags'))
                    ->placeholder(__('common.form.tag_input_placeholder'))
                    ->suggestions($tagService->getSuggestions($user)->pluck('name')->all())
            )
            // Income type select
            ->add(
                SelectField::make('income_type', __('incomes.filters.type'))
                    ->allLabel(__('incomes.filters.all'))
                    ->options($incomeTypes->visibleForUser($user)->pluck('name', 'id'))
            )
            // Currency select
            ->add(
                SelectField::make('currency_code', __('incomes.filters.currency'))
                    ->allLabel(__('incomes.filters.all'))
                    ->options(Currency::displayList())
            );
    }

    /**
     * Get a filter chips collection for current filters.
     */
    public function chips(): ChipCollection
    {
        $data = $this->filtersDTO;

        return ChipCollection::make()
            // Free-text search
            ->addChip(StringChip::make($data->query)->label(__('common.search'))->removeKeys('query'))
            // Date range
            ->addChip(DateRangeChip::make($data->dateFrom, $data->dateTo)->label(__('incomes.table.date')))
            // Income Type (model chip)
            ->addChip(
                ModelChip::make(IncomeType::class, $data->incomeTypeId)
                    ->label(__('incomes.filters.type'))
                    ->removeKeys('income_type')
            )
            // Amount range (minor units will be converted to major)
            ->addChip(
                MinorMinMaxRangeChip::make($data->amountMinorMin, $data->amountMinorMax)
                    ->usingCurrency($data->currency)
                    ->label(__('incomes.table.amount'))
                    ->removeKeys('amount_min', 'amount_max')
            )
            // Currency
            ->addChip(CurrencyChip::make($data->currency)->label(__('incomes.filters.currency')))
            // Tags (array chip)
            ->addChip(ArrayChip::make($data->tags)->label(__('incomes.table.tags'))->removeKeys('tags'));
    }
}
