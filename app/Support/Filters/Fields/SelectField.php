<?php

namespace App\Support\Filters\Fields;

use Illuminate\Support\Collection;

/**
 * This class represents a select/dropdown field component that allows users to choose
 * from a predefined set of options. It supports both associative arrays and list-based
 * option definitions, with automatic value type normalization.
 *
 * Features:
 * - Accepts options as key-value pairs or array of objects
 * - Supports optional "all" option label
 * - Normalizes numeric values to proper types (int/string)
 * - Provides a consistent option format for frontend rendering
 */
class SelectField extends AbstractField
{
    /**
     * The field type identifier used to distinguish this select field from other field types.
     * This constant is used for type checking and frontend component mapping.
     */
    public const string TYPE = 'select';

    /**
     * Raw options input as provided by the builder/user.
     * Can be either:
     * - associative array of [value => label]
     * - or a list of [ ['value' => v, 'label' => l], ... ] (backward compatible)
     */
    protected array $rawOptions = [];

    /**
     * Label to show for the "all" option (optional).
     */
    protected ?string $allLabel = null;

    /**
     * Accepts options where an array key is the value and array value is the label.
     * Also accepts legacy array-of-objects shape with 'value' and 'label' keys.
     */
    public function options(array|Collection $options): self
    {
        if ($options instanceof Collection) {
            $options = $options->toArray();
        }

        $this->rawOptions = $options;

        return $this;
    }

    /**
     * Set a label for the synthetic "all" option (when present in the UI).
     */
    public function allLabel(?string $label): self
    {
        $this->allLabel = $label;

        return $this;
    }

    /**
     * Normalize raw options to an array of {value, label} pairs for the frontend.
     */
    protected function normalizedOptions(): array
    {
        // Legacy: already in the desired shape
        $isListOfObjects = ! empty($this->rawOptions)
            && array_is_list($this->rawOptions)
            && is_array($this->rawOptions[0] ?? null)
            && array_key_exists('value', $this->rawOptions[0])
            && array_key_exists('label', $this->rawOptions[0]);

        if ($isListOfObjects) {
            // Ensure integer keys are re-indexed
            return array_values(array_map(function ($opt) {
                $value = $opt['value'];
                $label = $opt['label'];

                return [
                    'value' => is_int($value) ? $value : (is_numeric($value) && ctype_digit((string) $value) ? (int) $value : (string) $value),
                    'label' => (string) $label,
                ];
            }, $this->rawOptions));
        }

        // Preferred: associative array [value => label]
        $out = [];
        foreach ($this->rawOptions as $value => $label) {
            $normalizedValue = is_int($value) ? $value : (is_numeric($value) && ctype_digit((string) $value) ? (int) $value : (string) $value);
            $out[] = [
                'value' => $normalizedValue,
                'label' => (string) $label,
            ];
        }

        return $out;
    }

    /**
     * Prepare a specific array containing normalized options and an 'allLabel' value.
     */
    protected function specific(): array
    {
        return [
            'options' => $this->normalizedOptions(),
            'allLabel' => $this->allLabel,
        ];
    }
}
