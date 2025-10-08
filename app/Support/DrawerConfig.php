<?php

namespace App\Support;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Drawer class handles form submission configuration for create/edit operations.
 * Implements array conversion for easy data passing to views.
 */
class DrawerConfig implements Arrayable
{
    /**
     * The type of drawer operation (create/edit).
     */
    protected ?string $type = null;

    /**
     * The HTTP method to be used for form submission.
     */
    protected ?string $method = null;

    /**
     * The form submission endpoint URL.
     */
    protected ?string $action = null;

    /**
     * Creates a new drawer instance configured for create operations.
     */
    public static function create(): self
    {
        return (new static)->type('create')->method('post');
    }

    /**
     * Creates a new drawer instance configured for edit operations.
     */
    public static function edit(): self
    {
        return (new static)->type('edit')->method('put');
    }

    /**
     * Sets the drawer operation type.
     */
    public function type(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Sets the HTTP method for form submission.
     */
    public function method(string $method): self
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Sets the form submission endpoint URL.
     */
    public function action(string $action): self
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Converts drawer configuration to array format.
     */
    public function toArray(): array
    {
        return array_filter([
            'type' => $this->type,
            'method' => $this->method,
            'action' => $this->action,
        ]);
    }
}
