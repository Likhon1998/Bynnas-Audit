<?php

namespace App\Support;

/**
 * Parse Excel / Google Sheets clipboard text (TSV or CSV) into row arrays.
 */
class ExcelTsvParser
{
    /**
     * @param  list<string>  $columns  Field keys in left-to-right column order
     * @return list<array<string, string>>
     */
    public static function toAssocRows(string $raw, array $columns): array
    {
        $columns = array_values(array_filter($columns, fn ($c) => is_string($c) && $c !== ''));
        if ($columns === []) {
            return [];
        }

        $matrix = self::toMatrix($raw);
        if ($matrix === []) {
            return [];
        }

        // Drop a header row if the first cell looks like a label matching our first column hint
        if (self::looksLikeHeaderRow($matrix[0], $columns)) {
            array_shift($matrix);
        }

        $rows = [];
        foreach ($matrix as $cells) {
            if (self::rowIsEmpty($cells)) {
                continue;
            }

            $row = [];
            foreach ($columns as $i => $key) {
                $row[$key] = isset($cells[$i]) ? trim((string) $cells[$i]) : '';
            }
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * @return list<list<string>>
     */
    public static function toMatrix(string $raw): array
    {
        $raw = str_replace(["\r\n", "\r"], "\n", $raw);
        $raw = trim($raw);
        if ($raw === '') {
            return [];
        }

        $lines = preg_split("/\n+/", $raw) ?: [];
        $matrix = [];

        foreach ($lines as $line) {
            $line = rtrim($line, "\n");
            if ($line === '') {
                continue;
            }

            // Excel uses tabs; some locales paste with semicolons
            if (str_contains($line, "\t")) {
                $cells = explode("\t", $line);
            } elseif (substr_count($line, ';') >= substr_count($line, ',')) {
                $cells = str_getcsv($line, ';');
            } else {
                $cells = str_getcsv($line, ',');
            }

            $matrix[] = array_map(
                static fn ($c) => trim((string) $c),
                $cells
            );
        }

        return $matrix;
    }

    /**
     * @param  list<string>  $cells
     * @param  list<string>  $columns
     */
    protected static function looksLikeHeaderRow(array $cells, array $columns): bool
    {
        if ($cells === []) {
            return false;
        }

        $first = mb_strtolower(trim($cells[0]));
        if ($first === '') {
            return false;
        }

        $headerHints = [
            'total', 'sample', 'instant', 'persent', 'percent', 'ক্রমিক', 'বিবরণ', 'সমিতি',
            'তারিখ', 'সদস্য', 'মাস', 'area', 'compliance', 'year', 'external', 'serial',
            'description', 'action', 'management', 'recommendation', 'sl', 'no',
        ];

        foreach ($headerHints as $hint) {
            if (str_contains($first, $hint)) {
                return true;
            }
        }

        // If first pasted cell equals a known Bengali/English header fragment for column 0 key
        $col0 = mb_strtolower($columns[0] ?? '');
        if ($col0 !== '' && str_contains($first, str_replace('_', ' ', $col0))) {
            return true;
        }

        return false;
    }

    /**
     * @param  list<string>  $cells
     */
    protected static function rowIsEmpty(array $cells): bool
    {
        foreach ($cells as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }
}
