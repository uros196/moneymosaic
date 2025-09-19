<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Validator;
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

        $this->assertSame('Aktivne sesije', trans('settings.sessions.title'));
    }

    public function test_inertia_middleware_shares_locale_and_translations(): void
    {
        $response = $this->get('/');

        $response->assertInertia(fn (Assert $page) => $page
            ->has('locale')
            ->has('translations.common')
            ->has('translations.nav')
        );
    }

    public function test_validation_group_translations_resolve_in_english(): void
    {
        app()->setLocale('en');

        $this->assertSame('The :attribute field is required.', trans('validation.required'));
        $this->assertSame('The :attribute field must be :size characters.', trans('validation.size.string'));
    }

    public function test_validation_group_translations_resolve_in_serbian(): void
    {
        app()->setLocale('sr');

        $this->assertSame('Polje :attribute je obavezno.', trans('validation.required'));
        $this->assertSame('Polje :attribute mora imati :size karaktera.', trans('validation.size.string'));
    }

    public function test_validation_attribute_names_translate_in_serbian(): void
    {
        app()->setLocale('sr');

        $this->assertSame('Naziv', trans('validation.attributes.name'));
    }

    public function test_validation_attribute_names_translate_in_english(): void
    {
        app()->setLocale('en');

        $this->assertSame('Name', trans('validation.attributes.name'));
    }

    public function test_validator_uses_translated_attribute_names_in_messages_sr(): void
    {
        app()->setLocale('sr');

        $validator = Validator::make(['name' => null], ['name' => 'required']);
        $this->assertTrue($validator->fails());
        $this->assertSame(['Polje Naziv je obavezno.'], $validator->errors()->get('name'));
    }

    public function test_validator_uses_translated_attribute_names_in_messages_en(): void
    {
        app()->setLocale('en');

        $validator = Validator::make(['name' => null], ['name' => 'required']);
        $this->assertTrue($validator->fails());
        $this->assertSame(['The Name field is required.'], $validator->errors()->get('name'));
    }

    public function test_validation_attribute_email_translates_in_both_locales(): void
    {
        app()->setLocale('sr');
        $this->assertSame('Email adresa', trans('validation.attributes.email'));

        app()->setLocale('en');
        $this->assertSame('Email', trans('validation.attributes.email'));
    }

    public function test_validator_uses_translated_current_password_in_messages_sr(): void
    {
        app()->setLocale('sr');

        $validator = Validator::make(['current_password' => null], ['current_password' => 'required']);
        $this->assertTrue($validator->fails());
        $this->assertSame(['Polje Trenutna lozinka je obavezno.'], $validator->errors()->get('current_password'));
    }

    public function test_validator_uses_translated_current_password_in_messages_en(): void
    {
        app()->setLocale('en');

        $validator = Validator::make(['current_password' => null], ['current_password' => 'required']);
        $this->assertTrue($validator->fails());
        $this->assertSame(['The Current password field is required.'], $validator->errors()->get('current_password'));
    }
}
