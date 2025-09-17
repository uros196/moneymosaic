<?php

namespace App\Http\Controllers\Incomes;

use App\Enums\Currency;
use App\Http\Controllers\Controller;
use App\Http\Requests\Incomes\StoreIncomeRequest;
use App\Http\Requests\Incomes\StoreIncomeTypeRequest;
use App\Http\Resources\CurrencyResource;
use App\Http\Resources\IncomeResource;
use App\Http\Resources\IncomeTypeResource;
use App\Http\Resources\TagListResource;
use App\Http\Resources\UserResource;
use App\Models\Income;
use App\Models\IncomeType;
use App\Repositories\Contracts\IncomeRepository;
use App\Repositories\Contracts\IncomeTypeRepository;
use App\Repositories\Contracts\TagRepository;
use App\Services\IncomeService;
use App\Services\TagService;
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
        protected TagRepository $tagRepository,
        protected IncomeTypeRepository $incomeTypes,
        protected IncomeRepository $incomes,
    ) {
        $this->authorizeResource(Income::class, 'income');
    }

    /**
     * Display the income index page.
     */
    public function index(Request $request): Response
    {
        return $this->render($request, [
            // Additional props used by the UI
            'user' => UserResource::make($request->user()),
        ]);
    }

    /**
     * Open the drawer in creation mode via URL.
     */
    public function create(Request $request): Response
    {
        $user = $request->user();

        return $this->render($request, [
            'modal' => [
                'type' => 'create',
                'action' => route('incomes.store'),
                'method' => 'post',
            ],
            'tagSuggestions' => TagListResource::collection($this->tagService->getSuggestions($user)),
        ]);
    }

    /**
     * Persist a newly created income.
     */
    public function store(StoreIncomeRequest $request)
    {
        $this->incomeService->save($request, Income::make());

        return redirect()->route('incomes.index')
            ->with('success', __('incomes.toasts.created'));
    }

    /**
     * Create a new income type for the current user.
     */
    public function storeType(StoreIncomeTypeRequest $request)
    {
        $this->authorize('create', IncomeType::class);

        $user = $request->user();
        $data = $request->validated();

        $type = IncomeType::create([
            'user_id' => $user->id,
            'name' => $data['name'],
        ]);

        // return IncomeTypeResource::make($type)
        //     ->response()
        //     ->setStatusCode(201);

        return back(303);
    }

    /**
     * Open the drawer in edit mode via URL with the given ID.
     */
    public function edit(Request $request, Income $income): Response
    {
        $user = $request->user();
        $income->load('tags');

        return $this->render($request, [
            'modal' => [
                'type' => 'edit',
                'action' => route('incomes.update', $income),
                'method' => 'put',
            ],
            'income' => IncomeResource::make($income),
            'tagSuggestions' => TagListResource::collection($this->tagService->getSuggestions($user)),
        ]);
    }

    /**
     * Persist a newly created income.
     */
    public function update(StoreIncomeRequest $request, Income $income)
    {
        $this->incomeService->save($request, $income);

        return redirect()->route('incomes.index')
            ->with('success', __('incomes.toasts.updated'));
    }

    /**
     * Delete an income that belongs to the current user.
     */
    public function destroy(Income $income)
    {
        $income->delete();

        return back(303)->with('success', __('incomes.toasts.deleted', ['name' => $income->name]));
    }

    /**
     * Render the income index page with the given data.
     *
     * Merges the provided data with default props including:
     * - Pagination configuration
     * - Income list (lazy loaded)
     * - Available currencies
     * - Income types visible to the user
     */
    protected function render(Request $request, array $data = []): Response
    {
        $user = $request->user();

        return Inertia::render('incomes/index', array_merge([

            // Load the table lazily on modal route to keep drawer snappy, but still show the table when landing directly
            'incomes' => Inertia::defer(fn () => IncomeResource::collection($this->incomeService->paginate($user))),
            'currencies' => fn () => CurrencyResource::collection(Currency::cases()),
            'incomeTypes' => fn () => IncomeTypeResource::collection($this->incomeTypes->visibleForUser($user)),

        ], $data));
    }
}
