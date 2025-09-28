<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ExchangeRateRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExchangeRateRepository::class)]
#[ORM\Table(name: 'exchange_rates')]
#[ORM\Index(name: 'idx_currencies_created_at', columns: ['base_currency_id', 'quote_currency_id', 'created_at'])]
#[ORM\Index(name: 'idx_created_at', columns: ['created_at'])]
#[ORM\Index(name: 'idx_base_currency', columns: ['base_currency_id'])]
#[ORM\Index(name: 'idx_quote_currency', columns: ['quote_currency_id'])]
class ExchangeRate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Currency::class, inversedBy: 'baseRates')]
    #[ORM\JoinColumn(name: 'base_currency_id', referencedColumnName: 'id', nullable: false)]
    private ?Currency $baseCurrency = null;

    #[ORM\ManyToOne(targetEntity: Currency::class, inversedBy: 'quoteRates')]
    #[ORM\JoinColumn(name: 'quote_currency_id', referencedColumnName: 'id', nullable: false)]
    private ?Currency $quoteCurrency = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 18, scale: 8)]
    private string $rate;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBaseCurrency(): ?Currency
    {
        return $this->baseCurrency;
    }

    public function setBaseCurrency(?Currency $baseCurrency): static
    {
        $this->baseCurrency = $baseCurrency;

        return $this;
    }

    public function getQuoteCurrency(): ?Currency
    {
        return $this->quoteCurrency;
    }

    public function setQuoteCurrency(?Currency $quoteCurrency): static
    {
        $this->quoteCurrency = $quoteCurrency;

        return $this;
    }

    public function getRate(): string
    {
        return $this->rate;
    }

    public function setRate(string $rate): static
    {
        $this->rate = $rate;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get the currency pair as a string (e.g., "EUR/BTC")
     */
    public function getPair(): string
    {
        if (!$this->baseCurrency || !$this->quoteCurrency) {
            return '';
        }

        return $this->baseCurrency->getCode().'/'.$this->quoteCurrency->getCode();
    }

    /**
     * Set currencies from pair string (e.g., "EUR/BTC")
     */
    public function setPairFromString(string $pair, Currency $baseCurrency, Currency $quoteCurrency): static
    {
        $this->baseCurrency = $baseCurrency;
        $this->quoteCurrency = $quoteCurrency;

        return $this;
    }
}
