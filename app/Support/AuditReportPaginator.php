<?php

namespace App\Support;

/**
 * Logical document sections. Glance + staff + the full TOC stay in one flow
 * so adding or removing সূচিপত্র rows never leaves a blank A4 sheet.
 */
class AuditReportPaginator
{
    /**
     * @param  list<array<string, mixed>>  $tocRows
     * @return list<array<string, mixed>>
     */
    public static function buildSheets(array $tocRows, bool $hasFinancial = true): array
    {
        $number = 1;
        $coverNo = $number++;
        $overviewNo = $number++;
        $classificationNo = $number++;
        $financialNo = $hasFinancial ? $number++ : null;

        $sheets = [
            ['type' => 'cover', 'number' => $coverNo],
            [
                'type' => 'overview',
                'number' => $overviewNo,
                'rows' => self::stampRows($tocRows, $financialNo, $classificationNo),
            ],
            ['type' => 'signatures_classification', 'number' => $classificationNo],
        ];

        if ($hasFinancial) {
            $sheets[] = ['type' => 'financial', 'number' => $financialNo];
        }

        return $sheets;
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    public static function stampRows(array $rows, ?int $financialPage, ?int $classificationPage = null): array
    {
        foreach ($rows as &$row) {
            if (($row['type'] ?? 'item') === 'section') {
                $row['page_no'] = '';

                continue;
            }

            if (($row['page_no'] ?? '') !== '') {
                continue;
            }

            $serial = (string) ($row['serial'] ?? '');
            $row['page_no'] = self::toBnPage(self::pageForSerial($serial, $financialPage, $classificationPage));
        }
        unset($row);

        return $rows;
    }

    protected static function pageForSerial(string $serial, ?int $financialPage, ?int $classificationPage): ?int
    {
        if (preg_match('/^১[\.٫]/u', $serial)) {
            return $financialPage;
        }

        if (preg_match('/^[২-৭]/u', $serial)) {
            return $classificationPage ?? $financialPage;
        }

        return $financialPage;
    }

    /**
     * @param  list<array<string, mixed>>  $sheets
     */
    public static function sheetNumber(array $sheets, string $type): ?int
    {
        foreach ($sheets as $sheet) {
            if (($sheet['type'] ?? '') === $type) {
                return (int) $sheet['number'];
            }
        }

        return null;
    }

    public static function toBnPage(?int $n): string
    {
        if ($n === null) {
            return '';
        }

        return strtr((string) $n, BanglaNumerals::LATIN_TO_BANGLA);
    }
}
