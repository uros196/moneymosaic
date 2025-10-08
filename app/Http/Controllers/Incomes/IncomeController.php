<?php

namespace App\Http\Controllers\Incomes;

use App\DTO\Incomes\IncomeData;
use App\Enums\Currency;
use App\Enums\ToastType;
use App\Filters\IncomeFiltersBuilder;
use App\Http\Controllers\Controller;
use App\Http\Requests\Incomes\IndexIncomeRequest;
use App\Http\Requests\Incomes\StoreIncomeRequest;
use App\Http\Resources\CurrencyResource;
use App\Http\Resources\IncomeResource;
use App\Http\Resources\IncomeTypeResource;
use App\Http\Resources\TagListResource;
use App\Http\Resources\UserResource;
use App\Models\Income;
use App\Repositories\Contracts\IncomeTypeRepository;
use App\Services\IncomeService;
use App\Services\TagService;
use App\Support\DrawerConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IncomeController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected TagService $tagService,
        protected IncomeService $incomeService,
        protected IncomeTypeRepository $incomeTypes,
    ) {
        $this->authorizeResource(Income::class, 'income');
    }

    /**
     * Display the income index page.
     */
    public function index(IndexIncomeRequest $request): Response
    {
        $filterBuilder = IncomeFiltersBuilder::buildFrom($request);

        return $this->render($request, [
            'sortables' => $request->sortables(),
            // Server-driven filters config
            'filters' => fn () => $filterBuilder->buildFilter(),
            // Always include chips in the response so we don't have to re-fetch them on every page load
            'filterChips' => Inertia::always(fn () => $filterBuilder->chips()),
        ]);
    }

    /**
     * Open the drawer in creation mode via URL.
     */
    public function create(Request $request): Response
    {
        return $this->render($request, [
            'modal' => Inertia::always(function () {
                return DrawerConfig::create()->action(route('incomes.store'));
            }),
        ]);
    }

    /**
     * Persist a newly created income.
     */
    public function store(StoreIncomeRequest $request): RedirectResponse
    {
        $this->incomeService->save(IncomeData::fromRequest($request), Income::make());

        return redirect()->route('incomes.index')
            ->with(ToastType::Success->message(__('incomes.toasts.created')));
    }

    /**
     * Display the income details page.
     */
    public function show(Request $request, Income $income): Response
    {
        $user = $request->user();
        $income->load(['tags', 'incomeType']);

        return Inertia::render('incomes/show', [
            'user' => UserResource::make($user),
            'income' => IncomeResource::make($income),
        ]);
    }

    /**
     * Open the drawer in edit mode via URL with the given ID.
     */
    public function edit(Request $request, Income $income): Response
    {
        $income->load('tags');

        return $this->render($request, [
            'modal' => Inertia::always(function () use ($income) {
                return DrawerConfig::edit()->action(route('incomes.update', $income));
            }),
            'income' => IncomeResource::make($income),
        ]);
    }

    /**
     * Persist a newly created income.
     */
    public function update(StoreIncomeRequest $request, Income $income): RedirectResponse
    {
        $this->incomeService->save(IncomeData::fromRequest($request), $income);

        return redirect()->route('incomes.index')
            ->with(ToastType::Success->message(__('incomes.toasts.updated')));
    }

    /**
     * Delete an income that belongs to the current user.
     */
    public function destroy(Income $income): RedirectResponse
    {
        $income->delete();

        return back(303)
            ->with(ToastType::Success->message(__('incomes.toasts.deleted', ['name' => $income->name])));
    }

    /**
     * Render the 'income index' page with required data and UI props.
     */
    protected function render(Request $request, array $data = []): Response
    {
        $user = $request->user();

        return Inertia::render('incomes/index', array_merge([

            // Additional props used by the UI
            'user' => UserResource::make($request->user()),
            'currencies' => CurrencyResource::collection(Currency::cases()),

            // Always set the modal default to null so React can rely on it
            'modal' => Inertia::always(fn () => null),

            // Load the table lazily on modal route to keep drawer snappy, but still show the table when landing directly
            'incomes' => Inertia::defer(fn () => IncomeResource::collection($this->incomeService->paginate($request, $user))),

            // Always include in a standard visit but only evaluated when needed
            'incomeTypes' => fn () => IncomeTypeResource::collection($this->incomeTypes->visibleForUser($user)),
            'tagSuggestions' => fn () => TagListResource::collection($this->tagService->getSuggestions($user)),

        ], $data));
    }
}
