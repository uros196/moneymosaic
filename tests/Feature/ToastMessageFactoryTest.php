<?php

namespace Tests\Feature;

use App\Enums\ToastType;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ToastMessageFactoryTest extends TestCase
{
    public function test_factory_flashes_message_into_session(): void
    {
        Route::get('/_test_toast', function () {
            ToastType::Success->flash('Yay');

            return response('ok');
        });

        $response = $this->get('/_test_toast');

        $response->assertSessionHas(ToastType::Success->value, 'Yay');
    }

    public function test_factory_now_sets_message_for_current_request(): void
    {
        Route::get('/_test_toast_now', function () {
            ToastType::Error->now('Oops');

            return response()->json([
                'now' => session()->get(ToastType::Error->value),
            ]);
        });

        $response = $this->get('/_test_toast_now');

        $response->assertJson(['now' => 'Oops']);
    }
}
