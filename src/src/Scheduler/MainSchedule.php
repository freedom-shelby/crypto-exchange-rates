<?php

declare(strict_types=1);

namespace App\Scheduler;

use Symfony\Component\Console\Messenger\RunCommandMessage;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Contracts\Cache\CacheInterface;

#[AsSchedule('default')]
final readonly class MainSchedule implements ScheduleProviderInterface
{
    public function __construct(
        private CacheInterface $cache,
        private string $updateFrequency,
    ) {
    }

    public function getSchedule(): Schedule
    {
        return (new Schedule())
            ->stateful($this->cache)
            ->processOnlyLastMissedRun(true)
            ->add(
                RecurringMessage::every($this->updateFrequency, new RunCommandMessage('app:update-exchange-rates')),
            );
    }
}
