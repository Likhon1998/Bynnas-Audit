<?php

namespace Tests\Unit;

use App\Support\ExcelTsvParser;
use PHPUnit\Framework\TestCase;

class ExcelTsvParserTest extends TestCase
{
    public function test_parses_tsv_into_assoc_rows(): void
    {
        $tsv = "100\t20\t5\t25%\n200\t40\t8\t20%";

        $rows = ExcelTsvParser::toAssocRows($tsv, [
            'total_population',
            'sample_size',
            'instances_found',
            'percentage',
        ]);

        $this->assertCount(2, $rows);
        $this->assertSame('100', $rows[0]['total_population']);
        $this->assertSame('25%', $rows[0]['percentage']);
        $this->assertSame('200', $rows[1]['total_population']);
    }

    public function test_skips_header_like_first_row(): void
    {
        $tsv = "Total Population\tSample\tInstances\tPercent\n10\t2\t1\t50";

        $rows = ExcelTsvParser::toAssocRows($tsv, [
            'total_population',
            'sample_size',
            'instances_found',
            'percentage',
        ]);

        $this->assertCount(1, $rows);
        $this->assertSame('10', $rows[0]['total_population']);
    }

    public function test_parses_csv_fallback(): void
    {
        $csv = "A,B,C\n1,2,3";

        $rows = ExcelTsvParser::toAssocRows($csv, ['a', 'b', 'c']);

        // First row looks like header (no hint match for 'a') — still kept as data unless hint matches
        $this->assertNotEmpty($rows);
    }
}
