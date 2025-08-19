<?php

namespace Tests\Feature;

use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    public function test_json_translations_resolve_in_serbian(): void
    {
        app()->setLocale('sr');

        $this->assertSame(
            'Podaci profila su ažurirani.',
            __('Profile information updated.')
        );
    }

    public function test_group_translations_resolve_in_serbian(): void
    {
        app()->setLocale('sr');

        $this->assertSame('Aktivne sesije', trans('sessions.title'));
    }

    public function test_inertia_middleware_shares_locale_and_translations(): void
    {
        $response = $this->get('/');

        $response->assertInertia(fn (Assert $page) => $page
            ->has('locale')
            ->has('translations.common')
            ->has('translations.security')
            ->has('translations.sessions')
            ->has('translations.appearance')
            ->has('translations.password')
            ->has('translations.profile')
            ->has('translations.auth')
            ->has('translations.settings')
            ->has('translations.nav')
        );
    }
}
