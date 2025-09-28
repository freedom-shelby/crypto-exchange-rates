<?php

declare(strict_types=1);

namespace App\Service\ExchangeRateProvider;

/**
 * Interface for exchange rate providers
 */
interface ExchangeRateProviderInterface
{
    /**
     * Fetch exchange rates for all supported pairs
     *
     * @return array<string, string> Array of pair => rate
     */
    public function fetchExchangeRates(): array;

    /**
     * Get all supported trading pairs
     *
     * @return array<string, string> Array of pair => symbol mapping
     */
    public function getSupportedPairs(): array;

    /**
     * Check if a pair is supported
     */
    public function isPairSupported(string $pair): bool;

    /**
     * Get the provider name
     */
    public function getProviderName(): string;

    /**
     * Get provider-specific configuration or metadata
     */
    public function getProviderInfo(): array;
}