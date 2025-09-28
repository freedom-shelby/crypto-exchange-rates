<?php

declare(strict_types=1);

namespace App\Service\ExchangeRateProvider;

use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ExchangeRateProviderFactory
{
    public const PROVIDER_BINANCE = 'binance';

    private const AVAILABLE_PROVIDERS = [
        self::PROVIDER_BINANCE => BinanceProvider::class,
    ];

    private array $providerInstances = [];

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
        private readonly string $defaultProvider = self::PROVIDER_BINANCE,
    ) {
    }

    /**
     * Create a provider instance by name
     */
    public function create(?string $providerName = null): ExchangeRateProviderInterface
    {
        $providerName = $providerName ?? $this->defaultProvider;
        $providerName = strtolower($providerName);

        if (!$this->isProviderSupported($providerName)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Unsupported provider: %s. Available providers: %s',
                    $providerName,
                    implode(', ', array_keys(self::AVAILABLE_PROVIDERS))
                )
            );
        }

        if (!isset($this->providerInstances[$providerName])) {
            $this->providerInstances[$providerName] = $this->createProviderInstance($providerName);
        }

        return $this->providerInstances[$providerName];
    }

    /**
     * Check if the provider is supported
     */
    public function isProviderSupported(string $providerName): bool
    {
        return array_key_exists(strtolower($providerName), self::AVAILABLE_PROVIDERS);
    }

    /**
     * Get default provider name
     */
    public function getDefaultProvider(): string
    {
        return $this->defaultProvider;
    }

    /**
     * Create actual provider instance
     */
    private function createProviderInstance(string $providerName): ExchangeRateProviderInterface
    {
        $className = self::AVAILABLE_PROVIDERS[$providerName];

        return new $className($this->httpClient, $this->logger);
    }

    /**
     * Clear provider instances cache
     */
    public function clearCache(): void
    {
        $this->providerInstances = [];
    }
}