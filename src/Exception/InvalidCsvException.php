<?php

declare(strict_types=1);

namespace App\Exception;

use RuntimeException;

/**
 * Thrown when the uploaded CSV is malformed or missing required columns.
 *
 * Extends RuntimeException so it can bubble up through the call stack
 * without being declared in every method signature.
 * The empty body is intentional — the class name itself carries the meaning,
 * and the message passed at throw time explains the specific problem.
 */
final class InvalidCsvException extends RuntimeException {}
