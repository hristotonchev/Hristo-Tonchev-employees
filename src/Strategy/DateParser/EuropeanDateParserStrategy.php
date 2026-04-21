<?php

declare(strict_types=1);

namespace App\Strategy\DateParser;

use App\Exception\UnparsableDateException;
use DateTimeImmutable;

/**
 * Handles European date formats:
 *   DD/MM/YYYY  (e.g. 25/12/2023)
 *   DD.MM.YYYY  (e.g. 25.12.2023)
 *   DD-MM-YYYY  (e.g. 25-12-2023)
 */
final class EuropeanDateParserStrategy implements DateParserStrategyInterface
{
    public function supports(string $dateString): bool
    {
        // Matches DD/MM/YYYY, DD.MM.YYYY, or DD-MM-YYYY
        // The day component must come first (1–31) and year is 4 digits at the end.
        return (bool) preg_match('/^(\d{1,2})[\/.\-](\d{1,2})[\/.\-](\d{4})$/', $dateString);
    }

    public function parse(string $dateString): DateTimeImmutable
    {
        $normalised = str_replace(['.', '-'], '/', $dateString);

        $date = DateTimeImmutable::createFromFormat('d/m/Y', $normalised);

        if ($date === false) {
            throw new UnparsableDateException("Cannot parse European date: {$dateString}");
        }

        return $date->setTime(0, 0, 0);
    }

    public function getName(): string
    {
        return 'European (DD/MM/YYYY, DD.MM.YYYY, DD-MM-YYYY)';
    }
}
