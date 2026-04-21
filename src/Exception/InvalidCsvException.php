<?php

declare(strict_types=1);

namespace App\Exception;

use RuntimeException;

/**
 * Thrown when the uploaded CSV is malformed or missing required columns.
 */
final class InvalidCsvException extends RuntimeException {}
