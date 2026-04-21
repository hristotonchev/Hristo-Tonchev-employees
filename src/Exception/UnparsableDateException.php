<?php

declare(strict_types=1);

namespace App\Exception;

use RuntimeException;

/**
 * Thrown when none of the registered date-parser strategies
 * can make sense of a given date string.
 */
final class UnparsableDateException extends RuntimeException {}
