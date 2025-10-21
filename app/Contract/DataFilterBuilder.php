<?php

namespace App\Contract;

use App\Support\Filters\Chips\ChipCollection;
use App\Support\Filters\Fields\FilterFieldsCollection;

/**
 * Interface DataFilterBuilder
 *
 * Defines contract for building data filters and managing filter chips
 */
interface DataFilterBuilder
{
    /**
     * Build and return a collection of filter fields.
     */
    public function buildFilter(): FilterFieldsCollection;

    /**
     * Get a collection of filter chips.
     */
    public function chips(): ChipCollection;
}
