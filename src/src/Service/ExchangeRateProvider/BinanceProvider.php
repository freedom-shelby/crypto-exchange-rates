<?php

declare(strict_types=1);

namespace App\Service\ExchangeRateProvider;

use Exception;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class BinanceProvider implements ExchangeRateProviderInterface
{
    private const BASE_URL = 'https://api.binance.com/api/v3';
    private const TIMEOUT = 10;
    private const PROVIDER_NAME = 'Binance';

    private array $pairs = [
        'EUR/BTC' => 'BTCEUR',
        'EUR/ETH' => 'ETHEUR',
        'EUR/LTC' => 'LTCEUR',
    ];

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function fetchExchangeRates(): array
    {
        $rates = [];

        foreach ($this->pairs as $pair => $symbol) {
            try {
                $rate = $this->fetchSingleRate($symbol);
                $rates[$pair] = $rate;

                $this->logger->info('Successfully fetched rate from Binance', [
                    'provider' => self::PROVIDER_NAME,
                    'pair'     => $pair,
                    'symbol'   => $symbol,
                    'rate'     => $rate
                ]);
            } catch (Exception $e) {
                $this->logger->error('Failed to fetch rate from Binance', [
                    'provider' => self::PROVIDER_NAME,
                    'pair'     => $pair,
                    'symbol'   => $symbol,
                    'error'    => $e->getMessage()
                ]);

                // Continue fetching other pairs even if one fails
                continue;
            }
        }

        if (empty($rates)) {
            throw new RuntimeException('Failed to fetch any exchange rates from Binance API');
        }

        return $rates;
    }

    /**
     * Fetch a single exchange rate from Binance API by symbol
     */
    private function fetchSingleRate(string $symbol): string
    {
        try {
            $response = $this->httpClient->request('GET', self::BASE_URL.'/ticker/price', [
                'query'   => ['symbol' => $symbol],
                'timeout' => self::TIMEOUT,
                'headers' => [
                    'User-Agent' => 'CryptoExchangeApp/1.0',
                    'Accept'     => 'application/json',
                ],
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode !== 200) {
                throw new RuntimeException("HTTP {$statusCode} response from Binance API");
            }

            $data = $response->toArray();

            if (!isset($data['price']) || !is_numeric($data['price'])) {
                throw new RuntimeException('Invalid response format from Binance API');
            }

            return $data['price'];
        } catch (ExceptionInterface $e) {
            throw new RuntimeException(
                sprintf('HTTP client error for symbol %s: %s', $symbol, $e->getMessage()),
                0,
                $e
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getSupportedPairs(): array
    {
        return $this->pairs;
    }

    /**
     * {@inheritDoc}
     */
    public function isPairSupported(string $pair): bool
    {
        return array_key_exists($pair, $this->pairs);
    }

    /**
     * {@inheritDoc}
     */
    public function getProviderName(): string
    {
        return self::PROVIDER_NAME;
    }

    /**
     * {@inheritDoc}
     */
    public function getProviderInfo(): array
    {
        return [
            'name'                  => self::PROVIDER_NAME,
            'base_url'              => self::BASE_URL,
            'timeout'               => self::TIMEOUT,
            'supported_pairs_count' => count($this->pairs),
            'rate_limit'            => '1200 requests per minute',
            'documentation'         => 'https://developers.binance.com/docs/binance-spot-api-docs',
        ];
    }
}
