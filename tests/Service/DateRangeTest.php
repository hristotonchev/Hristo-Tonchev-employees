<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\ValueObject\DateRange;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class DateRangeTest extends TestCase
{
    private function range(string $from, string $to): DateRange
    {
        return new DateRange(new DateTimeImmutable($from), new DateTimeImmutable($to));
    }

    public function test_overlap_full(): void
    {
        $a = $this->range('2023-01-01', '2023-12-31');
        $b = $this->range('2023-01-01', '2023-12-31');

        $this->assertSame(365, $a->overlapInDays($b));
    }

    public function test_overlap_partial(): void
    {
        $a = $this->range('2023-01-01', '2023-12-31');
        $b = $this->range('2023-07-01', '2023-12-31');

        $overlap = $a->overlapInDays($b);
        $this->assertSame(184, $overlap);
    }

    public function test_overlap_zero_when_adjacent(): void
    {
        $a = $this->range('2023-01-01', '2023-06-30');
        $b = $this->range('2023-07-01', '2023-12-31');

        $this->assertSame(0, $a->overlapInDays($b));
    }

    public function test_overlap_zero_when_completely_separate(): void
    {
        $a = $this->range('2023-01-01', '2023-03-31');
        $b = $this->range('2023-07-01', '2023-12-31');

        $this->assertSame(0, $a->overlapInDays($b));
    }

    public function test_overlap_is_symmetric(): void
    {
        $a = $this->range('2023-01-01', '2023-09-30');
        $b = $this->range('2023-06-01', '2023-12-31');

        $this->assertSame($a->overlapInDays($b), $b->overlapInDays($a));
    }

    public function test_duration_in_days(): void
    {
        $range = $this->range('2023-01-01', '2023-12-31');
        $this->assertSame(365, $range->durationInDays());
    }
}
