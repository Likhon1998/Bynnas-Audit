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
    public static function buildSheets(
        array $tocRows,
        bool $hasFinancial = true,
        bool $hasFinancialDetail = false,
        bool $hasFinancialPage6 = false,
        bool $hasFinancialPage7 = false,
        bool $hasFinancialPage8 = false,
        bool $hasFinancialPage9 = false,
        bool $hasFinancialPage10 = false,
        bool $hasFinancialPage11 = false,
        bool $hasFinancialPage12 = false,
        bool $hasFinancialPage13 = false,
        bool $hasFinancialPage14 = false,
        bool $hasFinancialPage15 = false,
        bool $hasFinancialPage16 = false,
        bool $hasFinancialPage17 = false,
        bool $hasFinancialPage18 = false,
        bool $hasFinancialPage19 = false,
        bool $hasFinancialPage20 = false,
        bool $hasFinancialPage21 = false,
    ): array {
        $number = 1;
        $coverNo = $number++;
        $overviewNo = $number++;
        $classificationNo = $number++;
        $financialNo = $hasFinancial ? $number++ : null;
        $financialDetailNo = ($hasFinancial && $hasFinancialDetail) ? $number++ : null;
        $financialPage6No = ($hasFinancial && $hasFinancialPage6) ? $number++ : null;
        $financialPage7No = ($hasFinancial && $hasFinancialPage7) ? $number++ : null;
        $financialPage8No = ($hasFinancial && $hasFinancialPage8) ? $number++ : null;
        $financialPage9No = ($hasFinancial && $hasFinancialPage9) ? $number++ : null;
        $financialPage10No = ($hasFinancial && $hasFinancialPage10) ? $number++ : null;
        $financialPage11No = ($hasFinancial && $hasFinancialPage11) ? $number++ : null;
        $financialPage12No = ($hasFinancial && $hasFinancialPage12) ? $number++ : null;
        $financialPage13No = ($hasFinancial && $hasFinancialPage13) ? $number++ : null;
        $financialPage14No = ($hasFinancial && $hasFinancialPage14) ? $number++ : null;
        $financialPage15No = ($hasFinancial && $hasFinancialPage15) ? $number++ : null;
        $financialPage16No = ($hasFinancial && $hasFinancialPage16) ? $number++ : null;
        $financialPage17No = ($hasFinancial && $hasFinancialPage17) ? $number++ : null;
        $financialPage18No = ($hasFinancial && $hasFinancialPage18) ? $number++ : null;
        $financialPage19No = ($hasFinancial && $hasFinancialPage19) ? $number++ : null;
        $financialPage20No = ($hasFinancial && $hasFinancialPage20) ? $number++ : null;
        $financialPage21No = ($hasFinancial && $hasFinancialPage21) ? $number++ : null;

        $sheets = [
            ['type' => 'cover', 'number' => $coverNo],
            [
                'type' => 'overview',
                'number' => $overviewNo,
                'rows' => self::stampRows(
                    $tocRows,
                    $financialNo,
                    $classificationNo,
                    $financialDetailNo,
                    $financialPage6No,
                    $financialPage7No,
                    $financialPage8No,
                    $financialPage9No,
                    $financialPage10No,
                    $financialPage11No,
                    $financialPage12No,
                    $financialPage13No,
                    $financialPage14No,
                    $financialPage15No,
                    $financialPage16No,
                    $financialPage17No,
                    $financialPage18No,
                    $financialPage19No,
                    $financialPage20No,
                    $financialPage21No
                ),
            ],
            ['type' => 'signatures_classification', 'number' => $classificationNo],
        ];

        if ($hasFinancial) {
            $sheets[] = ['type' => 'financial', 'number' => $financialNo];
        }

        if ($hasFinancial && $hasFinancialDetail) {
            $sheets[] = ['type' => 'financial_detail', 'number' => $financialDetailNo];
        }

        if ($hasFinancial && $hasFinancialPage6) {
            $sheets[] = ['type' => 'financial_page6', 'number' => $financialPage6No];
        }

        if ($hasFinancial && $hasFinancialPage7) {
            $sheets[] = ['type' => 'financial_page7', 'number' => $financialPage7No];
        }

        if ($hasFinancial && $hasFinancialPage8) {
            $sheets[] = ['type' => 'financial_page8', 'number' => $financialPage8No];
        }

        if ($hasFinancial && $hasFinancialPage9) {
            $sheets[] = ['type' => 'financial_page9', 'number' => $financialPage9No];
        }

        if ($hasFinancial && $hasFinancialPage10) {
            $sheets[] = ['type' => 'financial_page10', 'number' => $financialPage10No];
        }

        if ($hasFinancial && $hasFinancialPage11) {
            $sheets[] = ['type' => 'financial_page11', 'number' => $financialPage11No];
        }

        if ($hasFinancial && $hasFinancialPage12) {
            $sheets[] = ['type' => 'financial_page12', 'number' => $financialPage12No];
        }

        if ($hasFinancial && $hasFinancialPage13) {
            $sheets[] = ['type' => 'financial_page13', 'number' => $financialPage13No];
        }

        if ($hasFinancial && $hasFinancialPage14) {
            $sheets[] = ['type' => 'financial_page14', 'number' => $financialPage14No];
        }

        if ($hasFinancial && $hasFinancialPage15) {
            $sheets[] = ['type' => 'financial_page15', 'number' => $financialPage15No];
        }

        if ($hasFinancial && $hasFinancialPage16) {
            $sheets[] = ['type' => 'financial_page16', 'number' => $financialPage16No];
        }

        if ($hasFinancial && $hasFinancialPage17) {
            $sheets[] = ['type' => 'financial_page17', 'number' => $financialPage17No];
        }

        if ($hasFinancial && $hasFinancialPage18) {
            $sheets[] = ['type' => 'financial_page18', 'number' => $financialPage18No];
        }

        if ($hasFinancial && $hasFinancialPage19) {
            $sheets[] = ['type' => 'financial_page19', 'number' => $financialPage19No];
        }

        if ($hasFinancial && $hasFinancialPage20) {
            $sheets[] = ['type' => 'financial_page20', 'number' => $financialPage20No];
        }

        if ($hasFinancial && $hasFinancialPage21) {
            $sheets[] = ['type' => 'financial_page21', 'number' => $financialPage21No];
        }

        return $sheets;
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    public static function stampRows(
        array $rows,
        ?int $financialPage,
        ?int $classificationPage = null,
        ?int $financialDetailPage = null,
        ?int $financialPage6 = null,
        ?int $financialPage7 = null,
        ?int $financialPage8 = null,
        ?int $financialPage9 = null,
        ?int $financialPage10 = null,
        ?int $financialPage11 = null,
        ?int $financialPage12 = null,
        ?int $financialPage13 = null,
        ?int $financialPage14 = null,
        ?int $financialPage15 = null,
        ?int $financialPage16 = null,
        ?int $financialPage17 = null,
        ?int $financialPage18 = null,
        ?int $financialPage19 = null,
        ?int $financialPage20 = null,
        ?int $financialPage21 = null,
    ): array {
        foreach ($rows as &$row) {
            if (($row['type'] ?? 'item') === 'section') {
                $row['page_no'] = '';

                continue;
            }

            if (($row['page_no'] ?? '') !== '') {
                continue;
            }

            $serial = (string) ($row['serial'] ?? '');
            $row['page_no'] = self::toBnPage(self::pageForSerial(
                $serial,
                $financialPage,
                $classificationPage,
                $financialDetailPage,
                $financialPage6,
                $financialPage7,
                $financialPage8,
                $financialPage9,
                $financialPage10,
                $financialPage11,
                $financialPage12,
                $financialPage13,
                $financialPage14,
                $financialPage15,
                $financialPage16,
                $financialPage17,
                $financialPage18,
                $financialPage19,
                $financialPage20,
                $financialPage21
            ));
        }
        unset($row);

        return $rows;
    }

    protected static function pageForSerial(
        string $serial,
        ?int $financialPage,
        ?int $classificationPage,
        ?int $financialDetailPage = null,
        ?int $financialPage6 = null,
        ?int $financialPage7 = null,
        ?int $financialPage8 = null,
        ?int $financialPage9 = null,
        ?int $financialPage10 = null,
        ?int $financialPage11 = null,
        ?int $financialPage12 = null,
        ?int $financialPage13 = null,
        ?int $financialPage14 = null,
        ?int $financialPage15 = null,
        ?int $financialPage16 = null,
        ?int $financialPage17 = null,
        ?int $financialPage18 = null,
        ?int $financialPage19 = null,
        ?int $financialPage20 = null,
        ?int $financialPage21 = null,
    ): ?int {
        // ১.৩ → page 5 detail sheet
        if (preg_match('/^১[\.٫]৩$/u', $serial)) {
            return $financialDetailPage ?? $financialPage;
        }

        // ১.৪–১.৫ → page 6
        if (preg_match('/^১[\.٫][৪৫]$/u', $serial)) {
            return $financialPage6 ?? $financialDetailPage ?? $financialPage;
        }

        // ১.৬–১.৭ → page 7
        if (preg_match('/^১[\.٫][৬৭]$/u', $serial)) {
            return $financialPage7 ?? $financialPage6 ?? $financialDetailPage ?? $financialPage;
        }

        // ১.৮ → page 8
        if (preg_match('/^১[\.٫]৮$/u', $serial)) {
            return $financialPage8 ?? $financialPage7 ?? $financialPage6 ?? $financialDetailPage ?? $financialPage;
        }

        // ১.৯+ → page 9 (১.৯, ১.১০, …)
        if (preg_match('/^১[\.٫](৯|[১-৯]\d|[১-৯][০-৯])/u', $serial)) {
            return $financialPage9 ?? $financialPage8 ?? $financialPage7 ?? $financialPage6 ?? $financialDetailPage ?? $financialPage;
        }

        if (preg_match('/^১[\.٫]/u', $serial)) {
            return $financialPage;
        }

        // ২.১ → page 10
        if (preg_match('/^২[\.٫]১$/u', $serial)) {
            return $financialPage10 ?? $financialPage ?? $classificationPage;
        }

        // ২.২+ → page 11
        if (preg_match('/^২[\.٫]/u', $serial)) {
            return $financialPage11 ?? $financialPage10 ?? $financialPage ?? $classificationPage;
        }

        // ৩.x → page 12
        if (preg_match('/^৩/u', $serial)) {
            return $financialPage12 ?? $financialPage ?? $classificationPage;
        }

        // ৪.১–৪.২ → page 13
        if (preg_match('/^৪[\.٫][১২]$/u', $serial)) {
            return $financialPage13 ?? $financialPage ?? $classificationPage;
        }

        // ৪.৩–৪.৪ → page 14
        if (preg_match('/^৪[\.٫][৩৪]$/u', $serial)) {
            return $financialPage14 ?? $financialPage ?? $classificationPage;
        }

        // ৪.৫–৪.৬ → page 15
        if (preg_match('/^৪[\.٫][৫৬]$/u', $serial)) {
            return $financialPage15 ?? $financialPage ?? $classificationPage;
        }

        // ৪.৭–৪.৮ → page 16
        if (preg_match('/^৪[\.٫][৭৮]$/u', $serial)) {
            return $financialPage16 ?? $financialPage ?? $classificationPage;
        }

        // ৪.৯ → page 17
        if (preg_match('/^৪[\.٫]৯$/u', $serial)) {
            return $financialPage17 ?? $financialPage16 ?? $financialPage ?? $classificationPage;
        }

        // ৪.১০–৪.১১ → page 18
        if (preg_match('/^৪[\.٫]১[০১]$/u', $serial)) {
            return $financialPage18 ?? $financialPage17 ?? $financialPage16 ?? $financialPage ?? $classificationPage;
        }

        // ৫.x → page 19
        if (preg_match('/^৫/u', $serial)) {
            return $financialPage19 ?? $financialPage18 ?? $financialPage ?? $classificationPage;
        }

        // ৬.x → page 20
        if (preg_match('/^৬/u', $serial)) {
            return $financialPage20 ?? $financialPage19 ?? $financialPage18 ?? $financialPage ?? $classificationPage;
        }

        // ৭.x → page 21
        if (preg_match('/^৭/u', $serial)) {
            return $financialPage21 ?? $financialPage20 ?? $financialPage19 ?? $financialPage18 ?? $financialPage ?? $classificationPage;
        }

        if (preg_match('/^[৪-৬]/u', $serial)) {
            return $financialPage ?? $classificationPage;
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
