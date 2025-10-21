<?php

namespace App\Models;

use Spatie\Tags\Tag as ParentTag;

class Tag extends ParentTag
{
    /**
     * Always use application fallback locale.
     */
    public static function getLocale(): string
    {
        return config('app.fallback_locale');
    }
}
