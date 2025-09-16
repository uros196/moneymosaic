<?php

namespace App\Repositories\Eloquent;

use App\Models\Income;
use App\Models\User;
use App\Repositories\Contracts\IncomeRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentIncomeRepository implements IncomeRepository
{
    /**
     * EloquentIncomeRepository constructor.
     */
    public function __construct(protected Income $model) {}

    /**
     * Paginate incomes for the given user with default ordering.
     */
    public function paginateForUser(User $user, int $perPage): LengthAwarePaginator
    {
        return $this->model->query()
            ->with('incomeType:id,name,user_id')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(perPage: $perPage)
            ->withQueryString();
    }
}
