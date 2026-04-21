<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Exception\UnparsableDateException;
use App\Service\DateParserService;
use App\Strategy\DateParser\EuropeanDateParserStrategy;
use App\Strategy\DateParser\IsoDateParserStrategy;
use App\Strategy\DateParser\LongFormDateParserStrategy;
use App\Strategy\DateParser\NumericDateParserStrategy;
use App\Strategy\DateParser\UsDateParserStrategy;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/**
 * Covers every date-format strategy plus the NULL / empty-value handling.
 */
final class DateParserServiceTest extends TestCase
{
    private DateParserService $service;

    protected function setUp(): void
    {
        $this->service = new DateParserService([
            new IsoDateParserStrategy(),
            new NumericDateParserStrategy(),
            new EuropeanDateParserStrategy(),
            new UsDateParserStrategy(),
            new LongFormDateParserStrategy(),
        ]);
    }

    public function test_parses_iso_date(): void
    {
        $date = $this->service->parse('2013-11-01');
        $this->assertSame('2013-11-01', $date->format('Y-m-d'));
    }

    public function test_null_string_returns_today(): void
    {
        $today = (new DateTimeImmutable())->format('Y-m-d');
        $this->assertSame($today, $this->service->parse('NULL')->format('Y-m-d'));
    }

    public function test_empty_string_returns_today(): void
    {
        $today = (new DateTimeImmutable())->format('Y-m-d');
        $this->assertSame($today, $this->service->parse('')->format('Y-m-d'));
    }

    public function test_null_value_returns_today(): void
    {
        $today = (new DateTimeImmutable())->format('Y-m-d');
        $this->assertSame($today, $this->service->parse(null)->format('Y-m-d'));
    }

    public function test_parses_european_slash_date(): void
    {
        $date = $this->service->parse('25/12/2023');
        $this->assertSame('2023-12-25', $date->format('Y-m-d'));
    }

    public function test_parses_european_dot_date(): void
    {
        $date = $this->service->parse('25.12.2023');
        $this->assertSame('2023-12-25', $date->format('Y-m-d'));
    }

    public function test_parses_european_dash_date(): void
    {
        $date = $this->service->parse('25-12-2023');
        $this->assertSame('2023-12-25', $date->format('Y-m-d'));
    }

    public function test_parses_us_slash_date(): void
    {
        $strategy = new UsDateParserStrategy();
        $date = $strategy->parse('12/25/2023');
        $this->assertSame('2023-12-25', $date->format('Y-m-d'));
    }

    public function test_parses_long_form_full_month(): void
    {
        $date = $this->service->parse('January 5, 2014');
        $this->assertSame('2014-01-05', $date->format('Y-m-d'));
    }

    public function test_parses_long_form_day_first(): void
    {
        $date = $this->service->parse('5 January 2014');
        $this->assertSame('2014-01-05', $date->format('Y-m-d'));
    }

    public function test_parses_long_form_abbreviated_month(): void
    {
        $date = $this->service->parse('Jan 5, 2014');
        $this->assertSame('2014-01-05', $date->format('Y-m-d'));
    }

    public function test_parses_compact_yyyymmdd(): void
    {
        $date = $this->service->parse('20131101');
        $this->assertSame('2013-11-01', $date->format('Y-m-d'));
    }

    public function test_parses_unix_timestamp(): void
    {
        $date = $this->service->parse('1383264000');
        $this->assertSame('2013-11-01', $date->format('Y-m-d'));
    }

    public function test_throws_for_completely_unknown_format(): void
    {
        $this->expectException(UnparsableDateException::class);
        $this->service->parse('not-a-date-at-all!!');
    }
}
