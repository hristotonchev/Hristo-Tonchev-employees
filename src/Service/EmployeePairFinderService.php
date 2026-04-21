<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\EmployeePairResult;
use App\ValueObject\EmployeeProject;

/**
 * Core algorithm: finds the pair of employees who spent the most
 * cumulative days together across all common projects.
 */
final class EmployeePairFinderService
{
    /**
     * @param  EmployeeProject[] $records
     * @return EmployeePairResult|null  Null when no two employees share overlapping time.
     */
    public function findTopPair(array $records): ?EmployeePairResult
    {
        $byProject    = $this->groupByProject($records);
        $accumulations = $this->accumulateAllPairs($byProject);

        if (empty($accumulations)) {
            return null;
        }

        return $this->selectTopPair($accumulations)->toResult();
    }

    /**
     * @param  array<int, EmployeeProject[]>
     * @return array<string, PairAccumulation>
     */
    private function accumulateAllPairs(array $byProject): array
    {
        $accumulations = [];

        foreach ($byProject as $projectId => $workers) {
            $this->processProjectWorkers((int) $projectId, $workers, $accumulations);
        }

        return $accumulations;
    }

    /**
     * @param EmployeeProject[]
     * @param array<string, PairAccumulation>
     */
    private function processProjectWorkers(int $projectId, array $workers, array &$accumulations): void
    {
        $count = count($workers);

        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                $workerA = $workers[$i];
                $workerB = $workers[$j];

                if ($workerA->employeeId === $workerB->employeeId) {
                    continue;
                }

                $overlapDays = $workerA->dateRange->overlapInDays($workerB->dateRange);

                if ($overlapDays <= 0) {
                    continue;
                }

                $key = PairAccumulation::keyFor($workerA->employeeId, $workerB->employeeId);

                if (!isset($accumulations[$key])) {
                    [$first, $second] = $workerA->employeeId < $workerB->employeeId
                        ? [$workerA->employeeId, $workerB->employeeId]
                        : [$workerB->employeeId, $workerA->employeeId];

                    $accumulations[$key] = new PairAccumulation($first, $second);
                }

                $accumulations[$key]->addProjectOverlap($projectId, $overlapDays);
            }
        }
    }

    /**
     * @param  non-empty-array<string, PairAccumulation> $accumulations
     */
    private function selectTopPair(array $accumulations): PairAccumulation
    {
        return array_reduce(
            $accumulations,
            static fn (?PairAccumulation $champion, PairAccumulation $challenger): PairAccumulation =>
                $champion === null || $challenger->totalDays() > $champion->totalDays()
                    ? $challenger
                    : $champion,
        );
    }

    /**
     * @param  EmployeeProject[]
     * @return array<int, EmployeeProject[]>
     */
    private function groupByProject(array $records): array
    {
        $grouped = [];

        foreach ($records as $record) {
            $grouped[$record->projectId][] = $record;
        }

        return $grouped;
    }
}
