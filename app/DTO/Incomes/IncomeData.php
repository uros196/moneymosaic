<?php

namespace App\DTO\Incomes;

use App\Http\Requests\Incomes\StoreIncomeRequest;

/**
 * Data Transfer Object carrying attributes for creating/updating an Income.
 *
 * Holds model fillable attributes separately from the optional tags payload.
 */
final class IncomeData
{
    /**
     * @param  array<string, mixed>  $attributes  Attributes to be mass-assigned to Income (exclude 'tags').
     * @param  array<int, string>|null  $tags  Optional list of tags to sync. When null, tags should not be modified.
     */
    public function __construct(
        public array $attributes,
        public ?array $tags = null,
    ) {}

    /**
     * Build DTO from the validated store/update request.
     */
    public static function fromRequest(StoreIncomeRequest $request): self
    {
        $validated = $request->safe();

        return new self(
            attributes: $validated->except('tags'),
            // Preserve previous behavior: only sync when 'tags' is present and not empty.
            tags: $validated->input('tags', [])
        );
    }

    /**
     * Attributes suitable for Income::fill().
     */
    public function toModelAttributes(): array
    {
        return $this->attributes;
    }
}
