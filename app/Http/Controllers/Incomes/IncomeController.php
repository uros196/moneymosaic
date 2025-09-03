<?php

namespace App\Http\Controllers\Incomes;

use App\Enums\Currency;
use App\Http\Requests\Incomes\StoreIncomeRequest;
use App\Http\Requests\Incomes\StoreIncomeTypeRequest;
use App\Http\Resources\CurrencyResource;
use App\Http\Resources\IncomeResource;
use App\Http\Resources\IncomeTypeResource;
use App\Http\Resources\TagListResource;
use App\Http\Resources\UserResource;
use App\Models\Income;
use App\Models\IncomeType;
use App\Repositories\Contracts\IncomeTypeRepository;
use App\Repositories\Contracts\TagRepository;
use App\Services\IncomeService;
use App\Services\TagService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Inertia\Inertia;

class IncomeController extends BaseController
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected TagService $tagService,
        protected IncomeService $incomeService,
        protected TagRepository $tagRepository,
        protected IncomeTypeRepository $incomeTypes,
    ) {}

    /**
     * Display the income index page.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        return Inertia::render('incomes/index', [
            // Optional props that the frontend may use in the future
            'currencies' => CurrencyResource::collection(Currency::cases()),
            'user' => UserResource::make($user),
            'incomeTypes' => IncomeTypeResource::collection($this->incomeTypes->visibleForUser($user)),
        ]);
    }

    /**
     * Open the drawer in creation mode via URL.
     */
    public function create(Request $request)
    {
        $user = $request->user();

        return Inertia::render('incomes/index', [
            'modal' => [
                'type' => 'create',
                'action' => route('incomes.store'),
                'method' => 'post',
            ],
            'currencies' => CurrencyResource::collection(Currency::cases()),
            'incomeTypes' => IncomeTypeResource::collection($this->incomeTypes->visibleForUser($user)),
            'tagSuggestions' => TagListResource::collection($this->tagService->getSuggestions($user)),
        ]);
    }

    /**
     * Persist a newly created income.
     */
    public function store(StoreIncomeRequest $request)
    {
        $this->incomeService->save($request, Income::make());

        return redirect()->route('incomes.index');
    }

    /**
     * Create a new income type for the current user.
     */
    public function storeType(StoreIncomeTypeRequest $request)
    {
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
    public function edit(Request $request, Income $income)
    {
        $user = $request->user();
        $income->load('tags');

        return Inertia::render('incomes/index', [
            'modal' => [
                'type' => 'edit',
                'action' => route('incomes.update', $income),
                'method' => 'put',
            ],
            'income' => IncomeResource::make($income),
            'currencies' => CurrencyResource::collection(Currency::cases()),
            'incomeTypes' => IncomeTypeResource::collection($this->incomeTypes->visibleForUser($user)),
            'tagSuggestions' => TagListResource::collection($this->tagService->getSuggestions($user)),
        ]);
    }

    /**
     * Persist a newly created income.
     */
    public function update(StoreIncomeRequest $request, Income $income)
    {
        $this->incomeService->save($request, $income);

        return redirect()->route('incomes.index');
    }
}
