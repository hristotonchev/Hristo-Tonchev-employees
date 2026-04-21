<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\EmployeePairFinderService;
use App\Service\PairAccumulation;
use App\ValueObject\DateRange;
use App\ValueObject\EmployeeProject;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the core pair-finding algorithm.
 */
final class EmployeePairFinderServiceTest extends TestCase
{
    private EmployeePairFinderService $service;

    protected function setUp(): void
    {
        $this->service = new EmployeePairFinderService();
    }

    private function makeRecord(int $empId, int $projectId, string $from, string $to): EmployeeProject
    {
        return new EmployeeProject(
            employeeId: $empId,
            projectId:  $projectId,
            dateRange:  new DateRange(
                new DateTimeImmutable($from),
                new DateTimeImmutable($to),
            ),
        );
    }

    public function test_returns_null_when_no_records(): void
    {
        $this->assertNull($this->service->findTopPair([]));
    }

    public function test_returns_null_when_only_one_employee(): void
    {
        $records = [
            $this->makeRecord(1, 10, '2023-01-01', '2023-12-31'),
        ];

        $this->assertNull($this->service->findTopPair($records));
    }

    public function test_returns_null_when_no_overlapping_periods(): void
    {
        $records = [
            $this->makeRecord(1, 10, '2023-01-01', '2023-06-30'),
            $this->makeRecord(2, 10, '2023-07-01', '2023-12-31'),
        ];

        $this->assertNull($this->service->findTopPair($records));
    }

    public function test_finds_pair_with_full_overlap(): void
    {
        $records = [
            $this->makeRecord(143, 10, '2023-01-01', '2023-12-31'),
            $this->makeRecord(218, 10, '2023-01-01', '2023-12-31'),
        ];

        $result = $this->service->findTopPair($records);

        $this->assertNotNull($result);
        $this->assertSame(143, $result->employeeIdOne);
        $this->assertSame(218, $result->employeeIdTwo);
        $this->assertSame(365, $result->totalDaysTogether);
    }

    public function test_finds_pair_with_partial_overlap(): void
    {
        $records = [
            $this->makeRecord(1, 5, '2023-01-01', '2023-12-31'), // 365 days
            $this->makeRecord(2, 5, '2023-07-01', '2023-12-31'), // overlap = 184 days
        ];

        $result = $this->service->findTopPair($records);

        $this->assertNotNull($result);
        $this->assertSame(184, $result->totalDaysTogether);
    }

    public function test_accumulates_days_across_multiple_projects(): void
    {
        $records = [
            $this->makeRecord(1, 1, '2023-01-01', '2023-12-31'),
            $this->makeRecord(2, 1, '2023-01-01', '2023-04-11'),
            $this->makeRecord(3, 1, '2023-01-01', '2023-05-01'),

            $this->makeRecord(1, 2, '2023-01-01', '2023-06-30'),
            $this->makeRecord(2, 2, '2023-05-11', '2023-06-30'),
        ];

        $result = $this->service->findTopPair($records);

        $this->assertNotNull($result);
        $this->assertSame(
            PairAccumulation::keyFor(1, 2),
            PairAccumulation::keyFor($result->employeeIdOne, $result->employeeIdTwo)
        );
    }

    public function test_result_contains_per_project_breakdown(): void
    {
        $records = [
            $this->makeRecord(1, 10, '2023-01-01', '2023-06-30'),
            $this->makeRecord(2, 10, '2023-01-01', '2023-06-30'),
            $this->makeRecord(1, 20, '2023-01-01', '2023-03-31'),
            $this->makeRecord(2, 20, '2023-01-01', '2023-03-31'),
        ];

        $result = $this->service->findTopPair($records);

        $this->assertNotNull($result);
        $this->assertCount(2, $result->projects);

        $projectIds = array_map(fn($p) => $p->projectId, $result->projects);
        $this->assertContains(10, $projectIds);
        $this->assertContains(20, $projectIds);
    }

    public function test_pair_key_is_order_independent(): void
    {
        $this->assertSame(
            PairAccumulation::keyFor(5, 10),
            PairAccumulation::keyFor(10, 5)
        );
    }

    public function test_sample_data_from_task_description(): void
    {
        $records = [
            $this->makeRecord(143, 12, '2013-11-01', '2014-01-05'),
            $this->makeRecord(218, 10, '2012-05-16', '2024-01-01'),
            $this->makeRecord(143, 10, '2009-01-01', '2011-04-27'),
        ];

        $result = $this->service->findTopPair($records);
        $this->assertNull($result);
    }

    public function test_ignores_duplicate_employee_on_same_project(): void
    {
        $records = [
            $this->makeRecord(1, 10, '2023-01-01', '2023-12-31'),
            $this->makeRecord(1, 10, '2023-01-01', '2023-12-31'),
        ];

        $this->assertNull($this->service->findTopPair($records));
    }
}
