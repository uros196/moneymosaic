<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class ParsesExchangeSymbolsTraitTest extends TestCase
{
    private function makeTraitUser(): object
    {
        return new class
        {
            use \App\Support\Concerns\ParsesExchangeSymbols;

            public function parse($symbols): array
            {
                return $this->parseCurrenciesToArray($symbols);
            }

            public function getConfigured(): array
            {
                return $this->configuredSymbols();
            }
        };
    }

    public function test_parses_string_to_array(): void
    {
        $u = $this->makeTraitUser();
        $parsed = $u->parse(' usd , eur , , rsd ,GBP ');
        $this->assertSame(['USD', 'EUR', 'RSD', 'GBP'], $parsed);
    }

    public function test_parses_array_to_array(): void
    {
        $u = $this->makeTraitUser();
        $parsed = $u->parse(['usd', 'EUR', '', 'GBP', 'usd']);
        $this->assertSame(['USD', 'EUR', 'GBP'], $parsed);
    }

    public function test_parses_null_to_empty_array(): void
    {
        $u = $this->makeTraitUser();
        $parsed = $u->parse(null);
        $this->assertSame([], $parsed);
    }

    public function test_configured_symbols_reads_string_and_parses(): void
    {
        $u = $this->makeTraitUser();
        Config::set('exchange.symbols', 'usd, eur , RSD');
        $this->assertSame(['USD', 'EUR', 'RSD'], $u->getConfigured());
    }
}
