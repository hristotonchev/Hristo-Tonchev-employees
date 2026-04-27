<?php

declare(strict_types=1);

namespace App\ValueObject;

/**
 * Immutable representation of one row from the CSV file.
 *
 * Acts as a typed wrapper around raw CSV data — instead of passing
 * untyped arrays around, the rest of the application works with this object.
 */
final class EmployeeProject
{
    // All properties are readonly — once created from a CSV row, this object never changes.
    public function __construct(
        public readonly int       $employeeId,
        public readonly int       $projectId,
        // DateRange is itself a value object that also carries overlap logic.
        public readonly DateRange $dateRange,
    ) {}
}
