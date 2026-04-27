<?php

declare(strict_types=1);

namespace App\Exception;

use RuntimeException;

/**
 * Thrown when none of the registered date-parser strategies
 * can make sense of a given date string.
 *
 * Having a separate exception from InvalidCsvException lets the controller
 * give a more specific error message — the user knows it's a date format
 * problem, not a structural CSV problem.
 */
final class UnparsableDateException extends RuntimeException {}
