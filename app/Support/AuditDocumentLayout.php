<?php

namespace App\Support;

/**
 * A4 audit report layout — ISO 216 page, business-document margins,
 * and dynamic table column proportions (text left, numbers right, index center).
 */
class AuditDocumentLayout
{
    public const PAGE_WIDTH_MM = 210;

    public const PAGE_HEIGHT_MM = 297;

    /** ISO / business letter margins (mm). */
    public const MARGIN_TOP = 15;

    public const MARGIN_BOTTOM = 15;

    public const MARGIN_LEFT = 20;

    public const MARGIN_RIGHT = 20;

    public static function contentWidthMm(): float
    {
        return self::PAGE_WIDTH_MM - self::MARGIN_LEFT - self::MARGIN_RIGHT;
    }

    public static function contentHeightMm(): float
    {
        return self::PAGE_HEIGHT_MM - self::MARGIN_TOP - self::MARGIN_BOTTOM;
    }

    public static function pagePaddingCss(): string
    {
        return sprintf(
            '%dmm %dmm %dmm %dmm',
            self::MARGIN_TOP,
            self::MARGIN_RIGHT,
            self::MARGIN_BOTTOM,
            self::MARGIN_LEFT,
        );
    }

    /** DomPDF @page margins — keep inside the printable A4 area. */
    public static function pdfPageMarginCss(): string
    {
        return sprintf(
            '%dmm %dmm %dmm %dmm',
            self::MARGIN_TOP,
            self::MARGIN_RIGHT,
            self::MARGIN_BOTTOM,
            self::MARGIN_LEFT,
        );
    }

    /** @return list<float> Label | Value | Label | Value (percent, sums to 100). */
    public static function glanceColumnWidths(): array
    {
        return [30.0, 20.0, 30.0, 20.0];
    }

    /**
     * TOC: serial | finding | amount | rating | status | page.
     *
     * @return list<float>
     */
    public static function tocColumnWidths(): array
    {
        return [8.0, 40.0, 10.0, 22.0, 11.0, 9.0];
    }

    /**
     * Financial finding row: serial | title | body | rating.
     *
     * @return list<float>
     */
    public static function findingColumnWidths(): array
    {
        return [8.0, 12.0, 58.0, 22.0];
    }

    /**
     * Dynamic staff table widths from header labels (percent, sums to 100).
     *
     * @param  list<string>  $columns
     * @return list<float>
     */
    public static function staffColumnWidths(array $columns): array
    {
        $serial = 7.0;
        $weights = array_map(fn (string $h) => self::staffColumnWeight($h), $columns);
        $totalWeight = array_sum($weights) ?: 1.0;
        $available = 100.0 - $serial;

        $widths = [$serial];
        foreach ($weights as $weight) {
            $widths[] = round($available * ($weight / $totalWeight), 2);
        }

        $diff = round(100.0 - array_sum($widths), 2);
        $widths[count($widths) - 1] += $diff;

        return $widths;
    }

    /** Horizontal alignment for a staff data cell (international table rules). */
    public static function staffColumnAlign(string $header): string
    {
        $h = mb_strtolower(trim($header));

        if (str_contains($h, 'টাকা') || str_contains($h, 'amount') || str_contains($h, 'number') && ! str_contains($h, 'page')) {
            return 'right';
        }

        if (str_contains($h, 'তারিখ') || str_contains($h, 'date') || str_contains($h, 'id') || str_contains($h, 'নং') || str_contains($h, 'ক্রমিক')) {
            return 'center';
        }

        return 'left';
    }

    public static function alignClass(string $align): string
    {
        return match ($align) {
            'right' => 'right-align',
            'center' => 'center',
            default => 'left-align',
        };
    }

    private static function staffColumnWeight(string $header): float
    {
        $h = mb_strtolower(trim($header));

        if (str_contains($h, 'নাম') && ! str_contains($h, 'নাম্বার')) {
            return 3.2;
        }

        if (str_contains($h, 'তারিখ') || str_contains($h, 'date')) {
            return 2.4;
        }

        if (str_contains($h, 'পদবী') || str_contains($h, 'designation')) {
            return 2.0;
        }

        if (str_contains($h, 'নং') || str_contains($h, 'id') || str_contains($h, 'code')) {
            return 1.5;
        }

        return 2.0;
    }
}
