<?php

declare(strict_types=1);

namespace App\ValueObject;

/**
 * Immutable representation of one row from the CSV file.
 */
final class EmployeeProject
{
    public function __construct(
        public readonly int       $employeeId,
        public readonly int       $projectId,
        public readonly DateRange $dateRange,
    ) {}
}
