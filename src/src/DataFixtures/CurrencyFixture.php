<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Currency;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CurrencyFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $currencies = [
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€'],
            ['code' => 'BTC', 'name' => 'Bitcoin', 'symbol' => '₿'],
            ['code' => 'ETH', 'name' => 'Ethereum', 'symbol' => 'Ξ'],
            ['code' => 'LTC', 'name' => 'Litecoin', 'symbol' => 'Ł'],
        ];

        foreach ($currencies as $data) {
            $currency = new Currency();
            $currency->setCode($data['code']);
            $currency->setName($data['name']);
            $currency->setSymbol($data['symbol']);
            $currency->setIsActive(true);
            $currency->setCreatedAt(new DateTimeImmutable());
            $manager->persist($currency);
        }

        $manager->flush();
    }
}
