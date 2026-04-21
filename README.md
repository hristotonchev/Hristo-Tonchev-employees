# Hristo-Tonchev-employees

Symfony 7 app that finds the pair of employees who worked together on common projects the longest. Upload a CSV, get the result.

## Setup

Requires PHP 8.2+ and Composer.

```bash
git clone https://github.com/hristotonchev/Hristo-Tonchev-employees.git
cd Hristo-Tonchev-employees
composer install
php -S localhost:8000 -t public/
```

Open [http://localhost:8000](http://localhost:8000).

## Tests

```bash
vendor/bin/phpunit --testdox
```

## CSV format

```
EmpID, ProjectID, DateFrom, DateTo
143, 12, 2013-11-01, 2014-01-05
218, 10, 2012-05-16, NULL
143, 10, 2009-01-01, 2011-04-27
```

Header row is optional. `DateTo = NULL` means today. A sample file is included at `sample_data.csv`.

Supported date formats: ISO 8601, European (DD/MM/YYYY, DD.MM.YYYY), US (MM/DD/YYYY), compact (YYYYMMDD), Unix timestamp, long-form (January 5, 2014 / 5 Jan 2014).

## How it works

Date parsing uses the **Strategy pattern** — each format is its own class. `DateParserService` tries them in priority order via Symfony's tagged iterator. Adding a new format means adding one class, nothing else changes.

The controller is intentionally thin. All logic lives in two services: `CsvParserService` (CSV → objects) and `EmployeePairFinderService` (algorithm). Both are tested independently without booting the framework.
