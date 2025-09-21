<?php

namespace App\Repositories\Eloquent;

use App\Models\IncomeType;
use App\Models\User;
use App\Repositories\Contracts\IncomeTypeRepository;
use Illuminate\Database\Eloquent\Collection;

class EloquentIncomeTypeRepository implements IncomeTypeRepository
{
    /**
     * EloquentIncomeTypeRepository constructor.
     */
    public function __construct(protected IncomeType $model) {}

    /**
     * Get income types visible for the given user (system + user-defined).
     *
     * @return Collection<int, IncomeType>
     */
    public function visibleForUser(User $user): Collection
    {
        return $this->model->query()
            ->where(function ($q) use ($user) {
                $q->whereNull('user_id')->orWhere('user_id', $user->id);
            })
            ->orderBy('user_id')
            ->get();
    }

    /**
     * Get income types created by the given user.
     *
     * @return Collection<int, IncomeType>
     */
    public function createdByUser(User $user): Collection
    {
        return $this->model->query()
            ->where('user_id', $user->id)
            ->orderBy('name')
            ->get();
    }
}
