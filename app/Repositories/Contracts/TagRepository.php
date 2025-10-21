<?php

namespace App\Repositories\Contracts;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;

interface TagRepository
{
    /**
     * Return a list of tags for the given type, in the given locale.
     *
     * @return Collection<int, Tag>
     */
    public function namesByType(string $type, ?string $locale = null): Collection;
}
