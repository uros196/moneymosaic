<?php

namespace App\Support;

use App\Enums\ToastType;

/**
 * Factory for flashing toast messages into the session.
 *
 * Initialized from a ToastType enum and intended for scenarios where
 * we cannot (or do not want to) use RedirectResponse::with().
 */
readonly class ToastMessageFactory
{
    public function __construct(protected ToastType $type) {}

    /**
     * Flash a message for the next request cycle.
     */
    public function flash(string $message): void
    {
        session()->flash($this->type->value, $message);
    }

    /**
     * Make the message available for the current request only.
     */
    public function now(string $message): void
    {
        session()->now($this->type->value, $message);
    }
}
