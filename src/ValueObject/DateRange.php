<?php

declare(strict_types=1);

namespace App\ValueObject;

use DateTimeImmutable;

/**
 * Immutable value object representing a closed date range [from, to].
 *
 * Using a value object (rather than a plain array or loose parameters)
 * keeps date-range logic encapsulated and easy to test in isolation.
 */
final class DateRange
{
    public function __construct(
        public readonly DateTimeImmutable $from,
        public readonly DateTimeImmutable $to,
    ) {}

    public function overlapInDays(DateRange $other): int
    {
        $overlapStart = max($this->from, $other->from);
        $overlapEnd   = min($this->to, $other->to);

        if ($overlapStart > $overlapEnd) {
            return 0;
        }

        return (int) $overlapStart->diff($overlapEnd)->days + 1;
    }

    public function durationInDays(): int
    {
        return (int) $this->from->diff($this->to)->days + 1;
    }
}
