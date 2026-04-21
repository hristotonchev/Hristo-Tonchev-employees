<?php

declare(strict_types=1);

namespace App\Strategy\DateParser;

use App\Exception\UnparsableDateException;
use DateTimeImmutable;

/**
 * Handles US-style date formats:
 *   MM/DD/YYYY  (e.g. 12/25/2023)
 *   MM-DD-YYYY  (e.g. 12-25-2023)
 */
final class UsDateParserStrategy implements DateParserStrategyInterface
{
    public function supports(string $dateString): bool
    {
        if (!preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $dateString, $matches)) {
            return false;
        }

        $firstSegment = (int) $matches[1];

        return $firstSegment <= 12;
    }

    public function parse(string $dateString): DateTimeImmutable
    {
        $normalised = str_replace('-', '/', $dateString);
        $date = DateTimeImmutable::createFromFormat('m/d/Y', $normalised);

        if ($date === false) {
            throw new UnparsableDateException("Cannot parse US date: {$dateString}");
        }

        return $date->setTime(0, 0, 0);
    }

    public function getName(): string
    {
        return 'US (MM/DD/YYYY)';
    }
}
