<?php

namespace App\Services;

use App\Models\IncomeType;
use App\Models\User;

class IncomeTypeService
{
    /**
     * Create a new income type for the given user and set translations for all available locales.
     */
    public function create(User $user, string $name): IncomeType
    {
        $name = trim($name);

        $translations = [];
        foreach (config('app.available_locales') as $locale) {
            $translations[$locale] = $name;
        }

        return IncomeType::create([
            'user_id' => $user->id,
            'name' => $translations,
        ]);
    }
}
