<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Currency;
use App\Entity\ExchangeRate;
use App\Util\DateTimeHelper;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExchangeRate>
 */
class ExchangeRateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExchangeRate::class);
    }

    public function save(ExchangeRate $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ExchangeRate $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Find exchange rates for the last 24 hours for a specific pair
     *
     * @return ExchangeRate[]
     */
    public function findLast24Hours(Currency $baseCurrency, Currency $quoteCurrency): array
    {
        $twentyFourHoursAgo = new DateTimeImmutable('-24 hours');

        return $this->createQueryBuilder('e')
            ->andWhere('e.baseCurrency = :baseCurrency')
            ->andWhere('e.quoteCurrency = :quoteCurrency')
            ->andWhere('e.createdAt >= :twentyFourHoursAgo')
            ->setParameter('baseCurrency', $baseCurrency)
            ->setParameter('quoteCurrency', $quoteCurrency)
            ->setParameter('twentyFourHoursAgo', $twentyFourHoursAgo)
            ->orderBy('e.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find exchange rates for the last 24 hours by pair string
     *
     * @param string $pair (e.g., "EUR/BTC")
     *
     * @return ExchangeRate[]
     */
    public function findLast24HoursByPair(string $pair): array
    {
        [$baseCode, $quoteCode] = $this->parsePairString($pair);

        $twentyFourHoursAgo = new DateTimeImmutable('-24 hours');

        return $this->createQueryBuilder('e')
            ->join('e.baseCurrency', 'bc')
            ->join('e.quoteCurrency', 'qc')
            ->andWhere('bc.code = :baseCode')
            ->andWhere('qc.code = :quoteCode')
            ->andWhere('e.createdAt >= :twentyFourHoursAgo')
            ->setParameter('baseCode', $baseCode)
            ->setParameter('quoteCode', $quoteCode)
            ->setParameter('twentyFourHoursAgo', $twentyFourHoursAgo)
            ->orderBy('e.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find exchange rates for a specific day by pair string
     *
     * @param string $pair (e.g., "EUR/BTC")
     *
     * @return ExchangeRate[]
     */
    public function findByDayAndPair(string $pair, DateTimeInterface $date): array
    {
        [$baseCode, $quoteCode] = $this->parsePairString($pair);

        $startOfDay = DateTimeHelper::getStartOfDay($date);
        $endOfDay = DateTimeHelper::getEndOfDay($date);

        return $this->createQueryBuilder('e')
            ->join('e.baseCurrency', 'bc')
            ->join('e.quoteCurrency', 'qc')
            ->andWhere('bc.code = :baseCode')
            ->andWhere('qc.code = :quoteCode')
            ->andWhere('e.createdAt >= :startOfDay')
            ->andWhere('e.createdAt <= :endOfDay')
            ->setParameter('baseCode', $baseCode)
            ->setParameter('quoteCode', $quoteCode)
            ->setParameter('startOfDay', $startOfDay)
            ->setParameter('endOfDay', $endOfDay)
            ->orderBy('e.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
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
            throw new \InvalidArgumentException("Invalid pair format: {$pair}. Expected format: BASE/QUOTE");
        }

        return [strtoupper(trim($parts[0])), strtoupper(trim($parts[1]))];
    }
}
