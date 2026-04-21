<?php

declare(strict_types=1);

namespace App\Strategy\DateParser;

use App\Exception\UnparsableDateException;
use DateTimeImmutable;

/**
 * Handles the standard ISO 8601 date format: YYYY-MM-DD
 */
final class IsoDateParserStrategy implements DateParserStrategyInterface
{
    public function supports(string $dateString): bool
    {
        return (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString);
    }

    public function parse(string $dateString): DateTimeImmutable
    {
        $date = DateTimeImmutable::createFromFormat('Y-m-d', $dateString);

        if ($date === false) {
            throw new UnparsableDateException("Cannot parse ISO date: {$dateString}");
        }

        return $date->setTime(0, 0, 0);
    }

    public function getName(): string
    {
        return 'ISO 8601 (YYYY-MM-DD)';
    }
}
