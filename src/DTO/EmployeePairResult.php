<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * The top-level result: the winning pair and every project they shared.
 *
 * Separating "which pair won" from "per-project breakdown" keeps the
 * service return type clean and the template logic straightforward.
 */
final class EmployeePairResult
{
    /** @param CommonProjectResult[] $projects */
    public function __construct(
        public readonly int   $employeeIdOne,
        public readonly int   $employeeIdTwo,
        public readonly int   $totalDaysTogether,
        public readonly array $projects,
    ) {}
}
