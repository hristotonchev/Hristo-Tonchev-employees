<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * Represents a single shared project between two employees,
 * along with how many days they overlapped on it.
 */
final class CommonProjectResult
{
    public function __construct(
        public readonly int $employeeIdOne,
        public readonly int $employeeIdTwo,
        public readonly int $projectId,
        public readonly int $daysWorkedTogether,
    ) {}
}
