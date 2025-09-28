<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Currency;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Currency>
 */
class CurrencyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Currency::class);
    }

    public function save(Currency $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Currency $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Find currency by code
     */
    public function findByCode(string $code): ?Currency
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.code = :code')
            ->setParameter('code', strtoupper($code))
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find all active currencies
     *
     * @return Currency[]
     */
    public function findActive(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('c.code', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find or create currency by code
     */
    public function findOrCreateByCode(string $code, ?string $name = null, ?string $symbol = null): Currency
    {
        $currency = $this->findByCode($code);

        if (!$currency) {
            $currency = new Currency();
            $currency->setCode($code)
                ->setName($name ?? $code)
                ->setSymbol($symbol);

            $this->save($currency, true);
        }

        return $currency;
    }
}
