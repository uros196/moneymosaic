<?php

namespace App\Enums;

use App\Support\ToastMessageFactory;

/**
 * Toast message types used for flashing notifications to the session/UI.
 */
enum ToastType: string
{
    case Success = 'success';
    case Error = 'error';
    case Warning = 'warning';
    case Info = 'info';

    /**
     * Get the session flash key for this toast type.
     */
    public function flashKey(): string
    {
        return $this->value;
    }

    /**
     * Create a toast message factory for this toast type.
     */
    public function factory(): ToastMessageFactory
    {
        return new ToastMessageFactory($this);
    }

    /**
     * Flash a message for the next request cycle using a factory.
     */
    public function flash(string $message): void
    {
        $this->factory()->flash($message);
    }

    /**
     * Make the message available for the current request only.
     */
    public function now(string $message): void
    {
        $this->factory()->now($message);
    }

    /**
     * Create a message array with the toast type as a key.
     * This is useful for passing to the Inertia response.
     */
    public function message(string $message): array
    {
        return [$this->value => $message];
    }
}
