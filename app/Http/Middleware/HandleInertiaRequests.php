<?php

namespace App\Http\Middleware;

use App\Enums\ToastType;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $request->user(),
            ],
            'ziggy' => fn (): array => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'flash' => function () use ($request) {
                $success = $request->session()->get(ToastType::Success->value);
                $error = $request->session()->get(ToastType::Error->value);
                $warning = $request->session()->get(ToastType::Warning->value);
                $info = $request->session()->get(ToastType::Info->value);
                $status = $request->session()->get('status');

                $hasFlash = $success !== null || $error !== null || $warning !== null || $info !== null || $status !== null;

                return [
                    ToastType::Success->value => $success,
                    ToastType::Error->value => $error,
                    ToastType::Warning->value => $warning,
                    ToastType::Info->value => $info,
                    'status' => $status,
                    // Unique key so the frontend effect runs even when text is identical across submits
                    'key' => $hasFlash ? (string) microtime(true) : null,
                ];
            },
            'locale' => app()->getLocale(),
            'availableLocales' => config('app.available_locales'),
            'translations' => function () use ($request) {
                return array_merge([

                    // base translations that are always available
                    'common' => trans('common'),
                    'nav' => trans('nav'),

                ], $request->attributes->get('translations.extra', []));
            },
        ];
    }
}
