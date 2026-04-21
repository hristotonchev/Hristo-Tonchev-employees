<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\CommonProjectResult;
use App\DTO\EmployeePairResult;

/**
 * Mutable accumulator for a single employee pair.
 */
final class PairAccumulation
{
    private int $totalDays = 0;

    /** @var CommonProjectResult[] */
    private array $projects = [];

    public function __construct(
        public readonly int $employeeIdOne,
        public readonly int $employeeIdTwo,
    ) {}

    public function addProjectOverlap(int $projectId, int $days): void
    {
        $this->totalDays += $days;
        $this->projects[] = new CommonProjectResult(
            employeeIdOne:      $this->employeeIdOne,
            employeeIdTwo:      $this->employeeIdTwo,
            projectId:          $projectId,
            daysWorkedTogether: $days,
        );
    }

    public function totalDays(): int
    {
        return $this->totalDays;
    }

    public function toResult(): EmployeePairResult
    {
        return new EmployeePairResult(
            employeeIdOne:     $this->employeeIdOne,
            employeeIdTwo:     $this->employeeIdTwo,
            totalDaysTogether: $this->totalDays,
            projects:          $this->projects,
        );
    }

    public static function keyFor(int $a, int $b): string
    {
        return $a < $b ? "{$a}-{$b}" : "{$b}-{$a}";
    }
}
