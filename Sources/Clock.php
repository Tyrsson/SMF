<?php

declare(strict_types=1);

namespace SMF;

use DateTimeImmutable;
use DateTimeZone;
use Psr\Clock\ClockInterface;

class Clock implements ClockInterface
{
    public function __construct(
        private readonly DateTimeZone $timeZone,
    ) {
    }

    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable(timezone: $this->timeZone);
    }
}
