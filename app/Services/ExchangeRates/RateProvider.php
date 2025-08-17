<?php

namespace App\Services\ExchangeRates;

use Carbon\CarbonInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class RateProvider
{
    public function __construct(
        public string $baseUrl = '',
        public string $baseCurrency = '',
        /** @var list<string> */
        public array $symbols = [],
        public int $timeoutSeconds = 10,
    ) {
        $this->baseUrl = $this->baseUrl !== '' ? $this->baseUrl : (string) config('exchange.provider.base_url');
        $this->baseCurrency = $this->baseCurrency !== '' ? $this->baseCurrency : (string) config('exchange.base_currency');
        $this->symbols = ! empty($this->symbols) ? $this->symbols : array_values(array_filter(array_map('trim', (array) config('exchange.provider.symbols'))));
        $this->timeoutSeconds = $this->timeoutSeconds > 0 ? $this->timeoutSeconds : (int) config('exchange.provider.timeout');
    }

    /**
     * Fetch rates for the given date.
     *
     * @return array<string,float> Map of quote currency code => rate multiplier relative to base.
     */
    public function getRatesForDate(CarbonInterface $date): array
    {
        $symbolsParam = implode(',', $this->symbols);
        $url = rtrim($this->baseUrl, '/').'/'.$date->toDateString();

        $response = Http::timeout($this->timeoutSeconds)
            ->acceptJson()
            ->get($url, [
                'base' => $this->baseCurrency,
                'symbols' => $symbolsParam,
            ]);

        if (! $response->ok()) {
            throw new RuntimeException('Failed to fetch exchange rates: HTTP '.$response->status());
        }

        $json = $response->json();
        if (! is_array($json)) {
            throw new RuntimeException('Invalid response from exchange rates provider.');
        }

        $rates = Arr::get($json, 'rates');
        if (! is_array($rates)) {
            throw new RuntimeException('Rates key missing in provider response.');
        }

        // Normalize keys and cast to float
        $normalized = [];
        foreach ($rates as $quote => $rate) {
            $q = strtoupper((string) $quote);
            // ensure float cast
            $normalized[$q] = (float) $rate;
        }

        // Ensure base->base = 1.0 present
        $normalized[strtoupper($this->baseCurrency)] = 1.0;

        return $normalized;
    }
}
