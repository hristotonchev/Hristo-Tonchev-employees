<?php

declare(strict_types=1);

namespace App\Strategy\DateParser;

use App\Exception\UnparsableDateException;
use DateTimeImmutable;

/**
 * Handles two edge-case numeric formats often found in exports:
 *   YYYYMMDD  — compact ISO (e.g. 20131101)
 *   Unix timestamp — seconds since epoch (e.g. 1383264000)
 */
final class NumericDateParserStrategy implements DateParserStrategyInterface
{
    public function supports(string $dateString): bool
    {
        return (bool) preg_match('/^\d{8}$/', $dateString)
            || (bool) preg_match('/^\d{10}$/', $dateString);
    }

    public function parse(string $dateString): DateTimeImmutable
    {
        if (preg_match('/^\d{8}$/', $dateString)) {
            $date = DateTimeImmutable::createFromFormat('Ymd', $dateString);
            if ($date !== false) {
                return $date->setTime(0, 0, 0);
            }
        }

        if (preg_match('/^\d{10}$/', $dateString)) {
            $date = (new DateTimeImmutable())->setTimestamp((int) $dateString);
            return $date->setTime(0, 0, 0);
        }

        throw new UnparsableDateException("Cannot parse numeric date: {$dateString}");
    }

    public function getName(): string
    {
        return 'Numeric (YYYYMMDD / Unix timestamp)';
    }
}
