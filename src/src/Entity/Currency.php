<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CurrencyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CurrencyRepository::class)]
#[ORM\Table(name: 'currencies')]
#[ORM\UniqueConstraint(name: 'unique_currency_code', columns: ['code'])]
class Currency
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10, unique: true)]
    private string $code;

    #[ORM\Column(length: 100)]
    private string $name;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $symbol = null;

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, ExchangeRate>
     */
    #[ORM\OneToMany(targetEntity: ExchangeRate::class, mappedBy: 'baseCurrency')]
    private Collection $baseRates;

    /**
     * @var Collection<int, ExchangeRate>
     */
    #[ORM\OneToMany(targetEntity: ExchangeRate::class, mappedBy: 'quoteCurrency')]
    private Collection $quoteRates;

    public function __construct()
    {
        $this->baseRates = new ArrayCollection();
        $this->quoteRates = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = strtoupper($code);
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    public function setSymbol(?string $symbol): static
    {
        $this->symbol = $symbol;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, ExchangeRate>
     */
    public function getBaseRates(): Collection
    {
        return $this->baseRates;
    }

    public function addBaseRate(ExchangeRate $baseRate): static
    {
        if (!$this->baseRates->contains($baseRate)) {
            $this->baseRates->add($baseRate);
            $baseRate->setBaseCurrency($this);
        }

        return $this;
    }

    public function removeBaseRate(ExchangeRate $baseRate): static
    {
        if ($this->baseRates->removeElement($baseRate)) {
            // set the owning side to null (unless already changed)
            if ($baseRate->getBaseCurrency() === $this) {
                $baseRate->setBaseCurrency(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ExchangeRate>
     */
    public function getQuoteRates(): Collection
    {
        return $this->quoteRates;
    }

    public function addQuoteRate(ExchangeRate $quoteRate): static
    {
        if (!$this->quoteRates->contains($quoteRate)) {
            $this->quoteRates->add($quoteRate);
            $quoteRate->setQuoteCurrency($this);
        }

        return $this;
    }

    public function removeQuoteRate(ExchangeRate $quoteRate): static
    {
        if ($this->quoteRates->removeElement($quoteRate)) {
            // set the owning side to null (unless already changed)
            if ($quoteRate->getQuoteCurrency() === $this) {
                $quoteRate->setQuoteCurrency(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->code;
    }

    /**
     * Generate pair string (e.g., "EUR/BTC")
     */
    public function getPairWith(Currency $quoteCurrency): string
    {
        return $this->code.'/'.$quoteCurrency->getCode();
    }
}
