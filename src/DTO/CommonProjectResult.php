<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * Represents a single shared project between two employees,
 * along with how many days they overlapped on it.
 *
 * One EmployeePairResult contains an array of these —
 * one entry per project the pair worked on together.
 */
final class CommonProjectResult
{
    // Immutable — created once by PairAccumulation and never modified.
    public function __construct(
        public readonly int $employeeIdOne,
        public readonly int $employeeIdTwo,
        public readonly int $projectId,
        public readonly int $daysWorkedTogether,
    ) {}
}
