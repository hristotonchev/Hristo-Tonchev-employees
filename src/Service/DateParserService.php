<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\UnparsableDateException;
use App\Strategy\DateParser\DateParserStrategyInterface;
use DateTimeImmutable;

/**
 * Iterates the registered date-parser strategies in priority order
 * and delegates to the first one that claims it can handle the input.
 */
final class DateParserService
{
    /** @param iterable<DateParserStrategyInterface> $strategies */
    public function __construct(
        private readonly iterable $strategies,
    ) {}

    /**
     * Returns today's date for NULL / empty values (as per task spec).
     */
    public function parse(?string $dateString): DateTimeImmutable
    {
        $trimmed = trim($dateString ?? '');

        if ($trimmed === '' || strtoupper($trimmed) === 'NULL') {
            return (new DateTimeImmutable())->setTime(0, 0, 0);
        }

        foreach ($this->strategies as $strategy) {
            if ($strategy->supports($trimmed)) {
                return $strategy->parse($trimmed);
            }
        }

        throw new UnparsableDateException(
            "No registered strategy could parse the date string: \"{$trimmed}\". "
            . "Supported formats include ISO (YYYY-MM-DD), European (DD/MM/YYYY), "
            . "US (MM/DD/YYYY), compact (YYYYMMDD), Unix timestamps, and long-form (January 5, 2014)."
        );
    }
}
