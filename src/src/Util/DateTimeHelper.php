<?php

declare(strict_types=1);

namespace App\Util;

use DateTimeImmutable;
use DateTimeInterface;

final class DateTimeHelper
{
    public static function getStartOfDay(DateTimeInterface $date): DateTimeImmutable
    {
        return DateTimeImmutable::createFromInterface($date)->setTime(0, 0, 0);
    }

    public static function getEndOfDay(DateTimeInterface $date): DateTimeImmutable
    {
        return DateTimeImmutable::createFromInterface($date)->setTime(23, 59, 59);
    }
}
