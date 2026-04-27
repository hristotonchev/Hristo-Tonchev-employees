<?php

declare(strict_types=1);

namespace App\Strategy\DateParser;

use DateTimeImmutable;

/**
 * Contract that every date-parser strategy must fulfil.
 *
 * The two-method pattern (supports + parse) is the core of the Strategy Pattern:
 * the service asks "can you handle this?" before asking "handle it".
 * This keeps each strategy focused and the service free of format-specific logic.
 */
interface DateParserStrategyInterface
{
    /**
     * Returns true if this strategy can handle the given date string.
     * Should be cheap — typically just a regex check, no actual parsing.
     */
    public function supports(string $dateString): bool;

    /**
     * Parse the date string into a DateTimeImmutable.
     * Only called after supports() returned true.
     *
     * @throws \App\Exception\UnparsableDateException
     */
    public function parse(string $dateString): DateTimeImmutable;

    /**
     * Human-readable name for this format.
     * Useful for error messages and debugging — not used in the main flow.
     */
    public function getName(): string;
}
