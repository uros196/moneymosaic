<?php

namespace Tests\Unit\Rules;

use App\Rules\Pagination\Page;
use App\Rules\Pagination\PerPage;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class PaginationRulesTest extends TestCase
{
    public function test_page_allows_null_and_min_one(): void
    {
        $rule = new Page;

        $v = Validator::make(['page' => null], ['page' => [$rule]]);
        $this->assertTrue($v->passes());

        $v = Validator::make(['page' => 1], ['page' => [$rule]]);
        $this->assertTrue($v->passes());

        $v = Validator::make(['page' => '2'], ['page' => [$rule]]);
        $this->assertTrue($v->passes());

        $v = Validator::make(['page' => 0], ['page' => [$rule]]);
        $this->assertTrue($v->fails());
    }

    public function test_per_page_uses_configured_options(): void
    {
        // Ensure test knows the options
        Config::set('tables.incomes.per_page.options', [10, 25, 50]);
        $rule = new PerPage('incomes');

        $v = Validator::make(['perPage' => null], ['perPage' => [$rule]]);
        $this->assertTrue($v->passes());

        $v = Validator::make(['perPage' => 25], ['perPage' => [$rule]]);
        $this->assertTrue($v->passes());

        $v = Validator::make(['perPage' => '50'], ['perPage' => [$rule]]);
        $this->assertTrue($v->passes());

        $v = Validator::make(['perPage' => 7], ['perPage' => [$rule]]);
        $this->assertTrue($v->fails());
    }
}
