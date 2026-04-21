<?php

declare(strict_types=1);

namespace App\Strategy\DateParser;

use App\Exception\UnparsableDateException;
use DateTimeImmutable;

/**
 * Handles long-form natural language dates:
 *   "January 5, 2014"   → F j, Y
 *   "5 January 2014"    → j F Y
 *   "Jan 5, 2014"       → M j, Y
 *   "5 Jan 2014"        → j M Y
 *
 */
final class LongFormDateParserStrategy implements DateParserStrategyInterface
{
    private const FORMATS = [
        'F j, Y',   // January 5, 2014
        'j F Y',    // 5 January 2014
        'M j, Y',   // Jan 5, 2014
        'j M Y',    // 5 Jan 2014
        'F d, Y',   // January 05, 2014
        'd F Y',    // 05 January 2014
    ];

    public function supports(string $dateString): bool
    {
        return (bool) preg_match('/[a-zA-Z]/', $dateString)
            && (bool) preg_match('/\d{4}/', $dateString);
    }

    public function parse(string $dateString): DateTimeImmutable
    {
        foreach (self::FORMATS as $format) {
            $date = DateTimeImmutable::createFromFormat($format, $dateString);
            if ($date !== false) {
                return $date->setTime(0, 0, 0);
            }
        }

        throw new UnparsableDateException("Cannot parse long-form date: {$dateString}");
    }

    public function getName(): string
    {
        return 'Long-form (January 5, 2014 / 5 Jan 2014)';
    }
}
