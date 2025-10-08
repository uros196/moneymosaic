<?php

namespace App\Http\Controllers\Settings;

use App\Enums\ToastType;
use App\Http\Controllers\Controller;
use App\Http\Requests\IncomeTypes\DeleteIncomeTypeRequest;
use App\Http\Requests\IncomeTypes\StoreIncomeTypeRequest;
use App\Http\Requests\IncomeTypes\UpdateIncomeTypeRequest;
use App\Http\Resources\IncomeTypeResource;
use App\Models\IncomeType;
use App\Repositories\Contracts\IncomeTypeRepository;
use App\Services\IncomeTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IncomeTypeController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(protected IncomeTypeRepository $incomeTypes)
    {
        $this->authorizeResource(IncomeType::class, 'incomeType');
    }

    /**
     * Show the Income Types management page.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $incomeTypes = $this->incomeTypes->visibleForUser($user)
            ->loadCount('incomes');

        return Inertia::render('settings/lists/income-types', [
            'incomeTypes' => fn () => IncomeTypeResource::collection($incomeTypes),
        ]);
    }

    /**
     * Create a new income type from Settings.
     */
    public function store(StoreIncomeTypeRequest $request, IncomeTypeService $service): JsonResponse|RedirectResponse
    {
        $type = $service->create($request->user(), $request->validated('name'));

        if ($request->expectsJson()) {
            return IncomeTypeResource::make($type)->response()->setStatusCode(201);
        }

        return back(303)->with(ToastType::Success->message(__('incomes.types.created')));
    }

    /**
     * Update an existing income type's name (all locales).
     */
    public function update(UpdateIncomeTypeRequest $request, IncomeType $incomeType, IncomeTypeService $service): JsonResponse|RedirectResponse
    {
        $service->updateName($incomeType, $request->validated('name'));

        if ($request->expectsJson()) {
            return IncomeTypeResource::make($incomeType)->response();
        }

        return back(303)->with(ToastType::Success->message(__('incomes.types.updated')));
    }

    /**
     * Delete an income type when it is not linked to any incomes.
     */
    public function destroy(DeleteIncomeTypeRequest $request, IncomeType $incomeType): RedirectResponse
    {
        $incomeType->delete();

        return back(303)->with(ToastType::Success->message(__('incomes.types.deleted')));
    }
}
