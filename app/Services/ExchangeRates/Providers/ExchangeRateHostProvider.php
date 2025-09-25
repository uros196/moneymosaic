<?php

namespace App\Services\ExchangeRates\Providers;

use App\DTO\ExchangeRates\DailyRates;
use App\DTO\ExchangeRates\RateQuote;
use App\Services\ExchangeRates\ExchangeRateProviderInterface;
use App\Support\Concerns\ParsesExchangeSymbols;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * ExchangeRateHostProvider
 *
 * Strategy for fetching rates from https://api.exchangerate.host
 *
 * Implements:
 * - Historical: GET /{date}
 * - Timeframe: GET /timeframe?start_date=YYYY-MM-DD&end_date=YYYY-MM-DD
 */
class ExchangeRateHostProvider implements ExchangeRateProviderInterface
{
    use ParsesExchangeSymbols;

    /**
     * API key for the provider.
     */
    protected string $apiKey;

    /**
     * Create a new instance.
     */
    public function __construct(public string $base_url = '', public int $timeout = 10)
    {
        $this->apiKey = (string) config('exchange.providers.exchangerate_host.api_key');
    }

    /**
     * Fetch rates for the given date (historical daily rates).
     *
     * @throws ConnectionException
     */
    public function getRatesForDate(CarbonInterface $date, string $baseCurrency, array $currencies): DailyRates
    {
        $response = $this->client()
            ->get($this->method('historical'), $this->query([
                'date' => $date->toDateString(),
                'source' => $baseCurrency,
                'currencies' => $this->parseCurrenciesToString($currencies),
            ]));

        $json = $this->parseJson($response->json());

        if (! is_array($pairQuotes = data_get($json, 'quotes'))) {
            throw new RuntimeException('Rates/quotes key missing in provider response.');
        }

        return new DailyRates(
            date: CarbonImmutable::parse($date->toDateString()),
            base: strtoupper($baseCurrency),
            quotes: $this->buildQuotes($pairQuotes, $baseCurrency),
        );
    }

    /**
     * Fetch rates for a timeframe using the /timeframe endpoint.
     *
     * @return array<int, DailyRates>
     *
     * @throws ConnectionException
     */
    public function getRatesForRange(CarbonInterface $startDate, CarbonInterface $endDate, string $baseCurrency, array $currencies): array
    {
        $response = $this->client()
            ->get($this->method('timeframe'), $this->query([
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'source' => $baseCurrency,
                'currencies' => $this->parseCurrenciesToString($currencies),
            ]));

        $json = $this->parseJson($response->json());

        $daily = [];

        // Get 'quotes' shape per-provider example
        if (! is_array($byDate = data_get($json, 'quotes'))) {
            throw new RuntimeException('Rates/quotes key missing in timeframe response.');
        }

        // Sort dates ascending for deterministic ordering
        ksort($byDate);

        foreach ($byDate as $dateStr => $rates) {
            if (! is_array($rates)) {
                // Skip malformed entries
                continue;
            }

            $daily[] = new DailyRates(
                date: CarbonImmutable::parse((string) $dateStr),
                base: strtoupper($baseCurrency),
                quotes: $this->buildQuotes($rates, $baseCurrency),
            );
        }

        return $daily;
    }

    /**
     * Build a common HTTP client for requests.
     */
    private function client(): PendingRequest
    {
        return Http::timeout($this->timeout)
            ->acceptJson();
    }

    /**
     * Builds a full API endpoint URL by appending the method name to the base URL.
     */
    private function method(string $method): string
    {
        return rtrim($this->base_url, '/')."/$method";
    }

    /**
     * Build query parameters for requests.
     */
    private function query(array $params): array
    {
        return array_merge([
            'access_key' => $this->apiKey,
        ], $params);
    }

    /**
     * Normalize a quote key from the provider response to an ISO 4217 quote code.
     *
     * Accepts either plain quote codes (e.g. "USD") or pair keys (e.g. "EURUSD").
     */
    private function normalizeQuote(string $key, string $baseCurrency): string
    {
        $key = strtoupper($key);
        $base = strtoupper($baseCurrency);

        // If it's already a 3-letter code
        if (strlen($key) === 3) {
            return $key;
        }

        // Pair prefixed with base, e.g. EURUSD, EURRSD
        if (str_starts_with($key, $base) && strlen($key) > strlen($base)) {
            return substr($key, strlen($base));
        }

        // Fallback: take the last 3 characters (most ISO codes)
        if (strlen($key) >= 3) {
            return substr($key, -3);
        }

        return $key; // As-is fallback
    }

    /**
     * Build RateQuote list from a key=>rate map using base currency.
     *
     * @param  array<int|string, float|int|string>  $rates
     * @return array<int, RateQuote>
     */
    private function buildQuotes(array $rates, string $baseCurrency): array
    {
        $quotes = [];
        foreach ($rates as $key => $rate) {
            $quote = $this->normalizeQuote((string) $key, $baseCurrency);
            $quotes[] = new RateQuote($baseCurrency, $quote, (float) $rate);
        }

        return $quotes;
    }

    /**
     * Validate and normalize provider JSON response.
     *
     * @return array<string, mixed>
     */
    private function parseJson(mixed $json): array
    {
        if (! is_array($json)) {
            throw new RuntimeException('Invalid response from exchange rates provider.');
        }

        if (array_key_exists('success', $json) && $json['success'] === false) {
            $message = (string) (data_get($json, 'error.type') ?? data_get($json, 'error') ?? 'Exchange provider returned an error.');
            throw new RuntimeException($message);
        }

        return $json;
    }
}
