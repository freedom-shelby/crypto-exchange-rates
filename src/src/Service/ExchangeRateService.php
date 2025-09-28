<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\ExchangeRate;
use App\Repository\CurrencyRepository;
use App\Repository\ExchangeRateRepository;
use App\Service\ExchangeRateProvider\ExchangeRateProviderFactory;
use App\Service\ExchangeRateProvider\ExchangeRateProviderInterface;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use RuntimeException;

class ExchangeRateService
{
    private ?ExchangeRateProviderInterface $currentProvider = null;

    public function __construct(
        private readonly ExchangeRateRepository $exchangeRateRepository,
        private readonly CurrencyRepository $currencyRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ExchangeRateProviderFactory $providerFactory,
        private readonly LoggerInterface $logger,
        private readonly string $preferredProvider = 'binance',
    ) {
    }

    /**
     * Update exchange rates by fetching from Binance API
     */
    public function updateExchangeRates(?string $providerName = null): void
    {
        $this->logger->info('Starting exchange rates update', [
            'preferred_provider' => $this->preferredProvider
        ]);

        try {
            $provider = $this->getProvider($providerName);

            $rates = $provider->fetchExchangeRates();
            $savedCount = 0;

            foreach ($rates as $pairString => $rate) {
                $this->saveExchangeRate($pairString, $rate);
                $savedCount++;

                $this->logger->debug('Saved exchange rate', [
                    'provider' => $provider->getProviderName(),
                    'pair'     => $pairString,
                    'rate'     => $rate
                ]);
            }

            $this->entityManager->flush();

            $this->logger->info('Successfully updated exchange rates', [
                'provider'    => $provider->getProviderName(),
                'saved_count' => $savedCount
            ]);
        } catch (Exception $e) {
            $this->logger->error('Failed to update exchange rates', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new RuntimeException(
                'Failed to update exchange rates: '.$e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Save a single exchange rate
     *
     * @param string $pairString (e.g., "EUR/BTC")
     */
    private function saveExchangeRate(string $pairString, string $rate): void
    {
        [$baseCode, $quoteCode] = $this->parsePairString($pairString);

        $baseCurrency = $this->currencyRepository->findByCode($baseCode);
        $quoteCurrency = $this->currencyRepository->findByCode($quoteCode);

        if (!$baseCurrency || !$quoteCurrency) {
            throw new InvalidArgumentException("Currency not found for pair: $pairString");
        }

        $exchangeRate = new ExchangeRate();
        $exchangeRate->setBaseCurrency($baseCurrency)
            ->setQuoteCurrency($quoteCurrency)
            ->setRate($rate);

        $this->exchangeRateRepository->save($exchangeRate);
    }

    /**
     * Get exchange rates for the last 24 hours
     */
    public function getLast24HoursRates(string $pair): array
    {
        $this->validatePair($pair);

        $rates = $this->exchangeRateRepository->findLast24HoursByPair($pair);

        return $this->formatRatesForApi($rates);
    }

    /**
     * Get exchange rates for a specific day
     */
    public function getDayRates(string $pair, DateTimeInterface $date): array
    {
        $this->validatePair($pair);

        $rates = $this->exchangeRateRepository->findByDayAndPair($pair, $date);

        return $this->formatRatesForApi($rates);
    }

    /**
     * Get all supported currency pairs
     */
    public function getSupportedPairs(): array
    {
        return array_keys($this->getProvider()->getSupportedPairs());
    }

    /**
     * Validate if the trading pair is supported
     */
    private function validatePair(string $pair): void
    {
        if (!$this->getProvider()->isPairSupported($pair)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Unsupported trading pair: %s. Supported pairs: %s',
                    $pair,
                    implode(
                        ', ',
                        array_keys($this->getProvider()->getSupportedPairs())
                    )
                )
            );
        }
    }

    /**
     * Parse pair string into base and quote currency codes
     *
     * @return array{0: string, 1: string}
     */
    private function parsePairString(string $pair): array
    {
        $parts = explode('/', $pair);

        if (count($parts) !== 2) {
            throw new InvalidArgumentException("Invalid pair format: $pair. Expected format: BASE/QUOTE");
        }

        return [strtoupper(trim($parts[0])), strtoupper(trim($parts[1]))];
    }

    /**
     * Format exchange rate entities for API response
     *
     * @param ExchangeRate[] $rates
     */
    private function formatRatesForApi(array $rates): array
    {
        return array_map(static function (ExchangeRate $rate) {
            return [
                'id'             => $rate->getId(),
                'pair'           => $rate->getPair(),
                'base_currency'  => [
                    'code'   => $rate->getBaseCurrency()?->getCode(),
                    'name'   => $rate->getBaseCurrency()?->getName(),
                    'symbol' => $rate->getBaseCurrency()?->getSymbol(),
                ],
                'quote_currency' => [
                    'code'   => $rate->getQuoteCurrency()?->getCode(),
                    'name'   => $rate->getQuoteCurrency()?->getName(),
                    'symbol' => $rate->getQuoteCurrency()?->getSymbol(),
                ],
                'rate'           => (float)$rate->getRate(),
                'timestamp'      => $rate->getCreatedAt()->format('Y-m-d H:i:s'),
                'unix_timestamp' => $rate->getCreatedAt()->getTimestamp(),
            ];
        }, $rates);
    }

    /**
     * Get the current provider or create one
     */
    private function getProvider(?string $providerName = null): ExchangeRateProviderInterface
    {
        if ($providerName) {
            return $this->providerFactory->create($providerName);
        }

        if ($this->currentProvider === null) {
            try {
                $this->currentProvider = $this->providerFactory->create($this->preferredProvider);
            } catch (Exception $e) {
                $this->logger->warning('Failed to create preferred provider', [
                    'preferred_provider' => $this->preferredProvider,
                    'error'              => $e->getMessage()
                ]);
            }
        }

        return $this->currentProvider;
    }
}
