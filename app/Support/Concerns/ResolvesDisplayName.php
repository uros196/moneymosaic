<?php

namespace App\Support\Concerns;

use Closure;

/**
 * Provides a reusable way to resolve a human-friendly display name for an object.
 *
 * Resolution order:
 * 1) Explicit resolver callback (if provided)
 * 2) toChipLabel()
 * 3) chipLabel()
 * 5) name/title public attributes (if set)
 * 7) __toString() (if available)
 * 8) Fallback to the class name
 */
trait ResolvesDisplayName
{
    /**
     * Resolve a displayable string for the given subject.
     */
    protected function resolveName(object $subject, ?Closure $nameResolver = null): string
    {
        if ($nameResolver instanceof Closure) {
            return (string) $nameResolver($subject);
        }

        if (method_exists($subject, 'toChipLabel')) {
            return (string) $subject->toChipLabel();
        }

        if (method_exists($subject, 'chipLabel')) {
            return (string) $subject->chipLabel();
        }

        if (method_exists($subject, '__toString')) {
            try {
                return (string) $subject;
            } catch (\Throwable) {
                // ignore and fallback below
            }
        }

        return class_basename($subject);
    }
}
