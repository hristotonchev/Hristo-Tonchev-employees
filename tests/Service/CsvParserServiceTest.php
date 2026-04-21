<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Exception\InvalidCsvException;
use App\Service\CsvParserService;
use App\Service\DateParserService;
use App\Strategy\DateParser\EuropeanDateParserStrategy;
use App\Strategy\DateParser\IsoDateParserStrategy;
use App\Strategy\DateParser\LongFormDateParserStrategy;
use App\Strategy\DateParser\NumericDateParserStrategy;
use App\Strategy\DateParser\UsDateParserStrategy;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for CsvParserService.
 */
final class CsvParserServiceTest extends TestCase
{
    private CsvParserService $parser;

    /** @var string[] */
    private array $tempFiles = [];

    protected function setUp(): void
    {
        $dateParser   = new DateParserService([
            new IsoDateParserStrategy(),
            new NumericDateParserStrategy(),
            new EuropeanDateParserStrategy(),
            new UsDateParserStrategy(),
            new LongFormDateParserStrategy(),
        ]);

        $this->parser = new CsvParserService($dateParser);
    }

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $path) {
            @unlink($path);
        }
    }

    private function writeTempCsv(string $content): string
    {
        $path = tempnam(sys_get_temp_dir(), 'csv_test_');
        file_put_contents($path, $content);
        $this->tempFiles[] = $path;

        return $path;
    }

    public function test_parses_simple_data_row(): void
    {
        $path    = $this->writeTempCsv("143, 10, 2013-11-01, 2014-01-05\n");
        $records = $this->parser->parse($path);

        $this->assertCount(1, $records);
        $this->assertSame(143, $records[0]->employeeId);
        $this->assertSame(10, $records[0]->projectId);
        $this->assertSame('2013-11-01', $records[0]->dateRange->from->format('Y-m-d'));
        $this->assertSame('2014-01-05', $records[0]->dateRange->to->format('Y-m-d'));
    }

    public function test_skips_header_row_when_first_cell_is_non_numeric(): void
    {
        $csv  = "EmpID, ProjectID, DateFrom, DateTo\n143, 10, 2013-11-01, 2014-01-05\n";
        $path = $this->writeTempCsv($csv);

        $records = $this->parser->parse($path);

        $this->assertCount(1, $records);
        $this->assertSame(143, $records[0]->employeeId);
    }

    public function test_null_date_to_is_treated_as_today(): void
    {
        $today = (new \DateTimeImmutable())->format('Y-m-d');
        $path  = $this->writeTempCsv("1, 10, 2020-01-01, NULL\n");

        $records = $this->parser->parse($path);

        $this->assertCount(1, $records);
        $this->assertSame($today, $records[0]->dateRange->to->format('Y-m-d'));
    }

    public function test_skips_empty_lines(): void
    {
        $csv  = "1, 10, 2020-01-01, 2020-12-31\n\n2, 10, 2020-01-01, 2020-12-31\n";
        $path = $this->writeTempCsv($csv);

        $records = $this->parser->parse($path);

        $this->assertCount(2, $records);
    }

    public function test_parses_multiple_rows(): void
    {
        $csv = implode("\n", [
            '143, 12, 2013-11-01, 2014-01-05',
            '218, 10, 2012-05-16, NULL',
            '143, 10, 2009-01-01, 2011-04-27',
        ]);
        $path = $this->writeTempCsv($csv);

        $records = $this->parser->parse($path);

        $this->assertCount(3, $records);
    }

    public function test_throws_on_non_readable_path(): void
    {
        $this->expectException(InvalidCsvException::class);
        $this->parser->parse('/this/path/does/not/exist.csv');
    }

    public function test_throws_on_row_with_too_few_columns(): void
    {
        $this->expectException(InvalidCsvException::class);
        $path = $this->writeTempCsv("1, 10, 2020-01-01\n"); // only 3 columns
        $this->parser->parse($path);
    }

    public function test_throws_when_emp_id_is_not_numeric(): void
    {
        $this->expectException(InvalidCsvException::class);
        $path = $this->writeTempCsv("John, 10, 2020-01-01, 2020-12-31\n");
        $this->parser->parse($path);
    }

    public function test_throws_when_date_from_is_after_date_to(): void
    {
        $this->expectException(InvalidCsvException::class);
        $path = $this->writeTempCsv("1, 10, 2021-01-01, 2020-01-01\n");
        $this->parser->parse($path);
    }
}
