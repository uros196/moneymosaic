<?php

namespace App\Repositories\Eloquent;

use App\Models\Tag;
use App\Repositories\Contracts\TagRepository;
use Illuminate\Database\Eloquent\Collection;

class EloquentTagRepository implements TagRepository
{
    /**
     * Create a new repository instance.
     */
    public function __construct(protected Tag $model) {}

    /**
     * Return a list of tags for the given type, in the given locale.
     *
     * @return Collection<int, Tag>
     */
    public function namesByType(string $type, ?string $locale = null): Collection
    {
        // Spatie\Tags\Tag stores translated names in JSON column `name`
        // We will fetch all rows for the given type and map the translation for the locale.
        return $this->model->newQuery()->where('type', $type)->get(['id', 'name']);
    }
}
