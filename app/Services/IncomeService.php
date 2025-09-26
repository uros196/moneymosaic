<?php

namespace App\Services;

use App\DTO\Incomes\IncomeData;
use App\Models\Income;
use App\Models\User;
use App\Repositories\Contracts\IncomeRepository;
use App\Support\TableConfig;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

/**
 * Service for managing income-related operations including pagination and CRUD operations.
 */
class IncomeService
{
    /**
     * IncomeService constructor.
     */
    public function __construct(protected Request $request) {}

    /**
     * Paginate incomes for a specific user.
     */
    public function paginate(User $user): LengthAwarePaginator
    {
        $repository = app(IncomeRepository::class);

        // Resolve per-page configuration
        $perPage = TableConfig::resolvePerPage($this->request, 'incomes');

        return $repository->paginateForUser($user, $perPage);
    }

    /**
     * Save or update an income record with its associated tags.
     */
    public function save(IncomeData $data, Income $income): Income
    {
        // save the income
        $income->fill($data->toModelAttributes())->save();

        // update the list of the tags (preserve previous behavior: only when provided and not empty)
        if ($data->tags !== null) {
            $income->syncUserTags($data->tags);
        }

        return $income;
    }
}
