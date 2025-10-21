<?php

namespace App\Support\Filters\Fields;

/**
 * Simple text input filter field.
 *
 * This class represents a basic text input filter field that can be used in forms
 * or filtering interfaces. It extends the AbstractField class and provides
 * functionality for handling text input with optional placeholder support.
 */
class InputField extends AbstractField
{
    /**
     * The type identifier for this field.
     *
     * Used to identify this field type in the filtering system.
     */
    public const string TYPE = 'input';

    /**
     * The placeholder text to be displayed in the input field when empty.
     */
    protected ?string $placeholder = null;

    /**
     * Set a UI placeholder for the input.
     */
    public function placeholder(?string $placeholder): self
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    /**
     * Retrieve an array containing specific configuration or placeholders.
     */
    protected function specific(): array
    {
        return [
            'placeholder' => $this->placeholder,
        ];
    }
}
