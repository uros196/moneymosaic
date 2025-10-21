<?php

namespace App\Support\Filters\Fields;

/**
 * Tag input field with server-provided suggestions.
 *
 * This class represents a specialized field type for handling tag inputs with server-side suggestions.
 * It extends the AbstractField class and provides functionality for managing tag suggestions
 * and placeholder text in the UI.
 */
class TagsField extends AbstractField
{
    /**
     * The field type identifier used to recognize this as a tags input field.
     */
    public const string TYPE = 'tags';

    /**
     * Array of server-side suggestions that will be shown to users
     * when they interact with the tag input field.
     */
    protected array $suggestions = [];

    /**
     * Optional placeholder text shown in the tag input when it's empty,
     * providing a hint about the expected input format.
     */
    protected ?string $placeholder = null;

    /**
     * Provide server-side suggestion values shown in the tag input.
     *
     * This method sets the list of suggestions that will be displayed to the user
     * when they interact with the tag input field. The suggestions are converted
     * to a zero-based indexed array.
     */
    public function suggestions(array $suggestions): self
    {
        $this->suggestions = array_values($suggestions);

        return $this;
    }

    /**
     * Set a UI placeholder for the tag input.
     *
     * This method sets the placeholder text that will be displayed in the tag input field
     * when it is empty. The placeholder provides a hint to the user about the expected input.
     */
    public function placeholder(?string $placeholder): self
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    /**
     * Returns the field-specific configuration array.
     *
     * This method provides the specific configuration for the tags field,
     * including the list of suggestions and placeholder text. It is used
     * internally to generate the complete field configuration.
     */
    protected function specific(): array
    {
        return [
            'suggestions' => $this->suggestions,
            'placeholder' => $this->placeholder,
        ];
    }
}
