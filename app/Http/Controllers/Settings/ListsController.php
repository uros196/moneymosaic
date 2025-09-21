<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class ListsController extends Controller
{
    /**
     * Lists management index page (selector of available list types).
     */
    public function index(): Response
    {
        return Inertia::render('settings/lists/index', [
            'cards' => [
                [
                    'title' => __('settings.lists.income_types_title'),
                    'description' => __('settings.lists.income_types_desc'),
                    'href' => route('settings.lists.income-types'),
                ],
            ],
        ]);
    }
}
