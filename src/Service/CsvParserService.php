<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\InvalidCsvException;
use App\ValueObject\DateRange;
use App\ValueObject\EmployeeProject;
use SplFileInfo;

/**
 * Reads a CSV file and returns an array of EmployeeProject value objects.
 */
final class CsvParserService
{
    /**
     * Number of columns we require in every data row.
     * Header row is optional (auto-detected) and skipped if present.
     */
    private const REQUIRED_COLUMNS = 4;

    public function __construct(
        private readonly DateParserService $dateParser,
    ) {}

    /**
     * @param SplFileInfo|string $file  Path to the CSV or an SplFileInfo object.
     * @return EmployeeProject[]
     *
     * @throws InvalidCsvException
     */
    public function parse(SplFileInfo|string $file): array
    {
        $path = $file instanceof SplFileInfo ? $file->getPathname() : $file;

        if (!is_readable($path)) {
            throw new InvalidCsvException("File is not readable: {$path}");
        }

        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new InvalidCsvException("Could not open file: {$path}");
        }

        $records    = [];
        $lineNumber = 0;

        try {
            while (($row = fgetcsv($handle, separator: ',', escape: '')) !== false) {
                $lineNumber++;

                if ($row === [null]) {
                    continue;
                }

                $row = array_map('trim', $row);

                if ($lineNumber === 1 && !is_numeric($row[0])) {
                    continue;
                }

                if (count($row) < self::REQUIRED_COLUMNS) {
                    throw new InvalidCsvException(
                        "Line {$lineNumber} has fewer than " . self::REQUIRED_COLUMNS . " columns."
                    );
                }

                [$empId, $projectId] = $row;

                [$dateFrom, $dateTo] = $this->extractDateFields(
                    array_slice($row, 2),
                    $lineNumber,
                );

                if (!is_numeric($empId) || !is_numeric($projectId)) {
                    throw new InvalidCsvException(
                        "Line {$lineNumber}: EmpID and ProjectID must be numeric. Got: '{$empId}', '{$projectId}'."
                    );
                }

                $from = $this->dateParser->parse($dateFrom);
                $to   = $this->dateParser->parse($dateTo);

                if ($from > $to) {
                    throw new InvalidCsvException(
                        "Line {$lineNumber}: DateFrom ({$dateFrom}) is after DateTo ({$dateTo})."
                    );
                }

                $records[] = new EmployeeProject(
                    employeeId: (int) $empId,
                    projectId:  (int) $projectId,
                    dateRange:  new DateRange($from, $to),
                );
            }
        } finally {
            fclose($handle);
        }

        if (empty($records)) {
            throw new InvalidCsvException("The CSV file contains no valid data rows.");
        }

        return $records;
    }

    /**
     * Given the tokens after EmpID and ProjectID, find which split produces
     *
     * @param string[] $tokens
     * @return array{0: string, 1: string}
     * @throws InvalidCsvException
     */
    private function extractDateFields(array $tokens, int $lineNumber): array
    {
        $count = count($tokens);

        if ($count === 2) {
            return [$tokens[0], $tokens[1]];
        }

        for ($i = 0; $i < $count - 1; $i++) {
            $dateFrom = implode(', ', array_slice($tokens, 0, $i + 1));
            $dateTo   = implode(', ', array_slice($tokens, $i + 1));

            try {
                $this->dateParser->parse($dateFrom);
                $this->dateParser->parse($dateTo);

                return [$dateFrom, $dateTo];
            } catch (\Throwable) {
            }
        }

        throw new InvalidCsvException(
            "Line {$lineNumber}: could not determine DateFrom/DateTo from: " . implode(', ', $tokens)
        );
    }
}
