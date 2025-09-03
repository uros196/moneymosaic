<?php

namespace App\Services;

use App\Models\Income;
use App\Models\Tag;
use App\Models\User;
use App\Repositories\Contracts\TagRepository;
use Illuminate\Database\Eloquent\Collection;

class TagService
{
    /**
     * TagService constructor.
     */
    public function __construct(protected TagRepository $tagRepository) {}

    /**
     * Gather user tag suggestions using the Spatie\Tags grouping by type.
     *
     * @return Collection<int, Tag>
     */
    public function getSuggestions(User $user, ?string $locale = null): Collection
    {
        $type = Income::tagTypeForUser($user);

        return $this->tagRepository->namesByType($type, $locale);
    }
}
