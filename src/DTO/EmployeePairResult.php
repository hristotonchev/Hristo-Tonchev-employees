<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * The top-level result: the winning pair and every project they shared.
 *
 * This is what EmployeePairFinderService returns to the controller.
 * Separating "which pair won" from "per-project breakdown" keeps the
 * service return type clean and the template logic straightforward.
 */
final class EmployeePairResult
{
    /**
     * @param CommonProjectResult[] $projects  Per-project breakdown for the template table.
     */
    public function __construct(
        public readonly int   $employeeIdOne,
        public readonly int   $employeeIdTwo,
        // Sum of all overlapping days across every shared project.
        public readonly int   $totalDaysTogether,
        // Each entry shows one project and how many days they overlapped on it.
        public readonly array $projects,
    ) {}
}
