<?php

declare(strict_types=1);

namespace App\Strategy\DateParser;

use DateTimeImmutable;

/**
 * Strategy interface for parsing dates from various string formats.
 */
interface DateParserStrategyInterface
{
    /**
     * Returns true if this strategy can handle the given date string.
     */
    public function supports(string $dateString): bool;

    /**
     * Parse the date string into a DateTimeImmutable.
     *
     * @throws \App\Exception\UnparsableDateException
     */
    public function parse(string $dateString): DateTimeImmutable;

    /**
     * Human-readable name for this format (useful for debug / logging).
     */
    public function getName(): string;
}
