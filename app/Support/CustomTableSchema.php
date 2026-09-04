<?php

namespace App\Support;

/**
 * Nested-column custom tables for audit report blocks.
 *
 * Example: 4 top columns where the 3rd has 4 sub-columns → 7 leaf cells per body row.
 */
class CustomTableSchema
{
    /**
     * @return array{type:string,title:string,columns:list<array<string,mixed>>,rows:list<array<string,mixed>>}
     */
    public static function blank(int $columnCount = 3, int $rowCount = 3, string $title = 'টেবিল:'): array
    {
        $columns = [];
        for ($i = 0; $i < max(1, $columnCount); $i++) {
            $columns[] = self::columnNode('কলাম '.($i + 1));
        }

        $leaf = count(self::leafColumns($columns));
        $rows = [];
        for ($r = 0; $r < max(1, $rowCount); $r++) {
            $rows[] = self::blankRow($leaf);
        }

        return [
            'type' => 'custom_table',
            'title' => $title,
            'columns' => $columns,
            'rows' => $rows,
            'merges' => [],
        ];
    }

    /**
     * Sample expense / VAT / Tax layout from the report (nested groups).
     *
     * @return array{type:string,title:string,columns:list<array<string,mixed>>,rows:list<array<string,mixed>>}
     */
    public static function expenseVatTaxTemplate(): array
    {
        $columns = [
            self::columnNode('তারিখ/মাসের নাম'),
            self::columnNode('ভাউচার নং'),
            self::columnNode('বিবরণ'),
            self::columnNode('খরচ (টাকা)'),
            self::columnNode('ভ্যাট সংক্রান্ত', [
                self::columnNode('প্রযোজ্য'),
                self::columnNode('প্রদানকৃত'),
                self::columnNode('কম/বেশি প্রদান'),
            ]),
            self::columnNode('ট্যাক্স সংক্রান্ত', [
                self::columnNode('প্রযোজ্য'),
                self::columnNode('প্রদানকৃত'),
                self::columnNode('কম/বেশি প্রদান'),
            ]),
        ];

        $descriptions = [
            'স্টেশনারী',
            'আপ্যায়ন ভ্যাটএবল',
            'আপ্যায়ন ননভ্যাটএবল',
            'গ্যাস সিলিন্ডার',
            'ইন্টারনেট বিল',
            'মিটিং খরচ',
            'স্ক্যানার',
            'ওয়াল ফ্যান',
            'বিবিধ',
            'আবাসন ভাড়া',
            'অফিস ভাড়া',
        ];

        $leaf = count(self::leafColumns($columns));
        $dataRowCount = count($descriptions);
        $rows = [];
        foreach ($descriptions as $i => $desc) {
            $cells = array_fill(0, $leaf, '');
            $cells[0] = $i === 0 ? "জুলাই '২৫ হতে জুন '২৬" : '';
            $cells[1] = $i === 0 ? 'সংশ্লিষ্ট সকল' : '';
            $cells[2] = $desc;
            $rows[] = [
                'cells' => $cells,
                'is_total' => false,
                'lead_colspan' => 1,
            ];
        }
        $totalCells = array_fill(0, $leaf, '');
        $totalCells[0] = 'মোট';
        $rows[] = [
            'cells' => $totalCells,
            'is_total' => true,
            'lead_colspan' => 3,
        ];

        // Wider description column; merge date + voucher down the data rows (like the sample).
        $columns[0]['width'] = 14;
        $columns[1]['width'] = 10;
        $columns[2]['width'] = 22;
        $columns[3]['width'] = 10;

        return [
            'type' => 'custom_table',
            'title' => 'বিস্তারিত নিম্নে দেওয়া হল:',
            'columns' => $columns,
            'rows' => $rows,
            'merges' => [
                ['r' => 0, 'c' => 0, 'rowspan' => $dataRowCount, 'colspan' => 1],
                ['r' => 0, 'c' => 1, 'rowspan' => $dataRowCount, 'colspan' => 1],
            ],
        ];
    }

    /**
     * @param  list<array<string,mixed>>  $children
     * @return array{id:string,label:string,children:list<array<string,mixed>>}
     */
    public static function columnNode(string $label, array $children = [], ?float $width = null): array
    {
        $node = [
            'id' => self::newId(),
            'label' => $label,
            'children' => array_values($children),
        ];
        if ($width !== null) {
            $node['width'] = $width;
        }

        return $node;
    }

    public static function newId(): string
    {
        return 'c_'.bin2hex(random_bytes(4));
    }

    /**
     * @param  array<string,mixed>  $block
     * @return array{type:string,title:string,columns:list<array<string,mixed>>,rows:list<array<string,mixed>>}
     */
    public static function normalize(array $block): array
    {
        $columns = self::normalizeColumns(array_values((array) ($block['columns'] ?? [])));
        if ($columns === []) {
            $columns = [self::columnNode('কলাম ১'), self::columnNode('কলাম ২'), self::columnNode('কলাম ৩')];
        }

        $leafCount = count(self::leafColumns($columns));
        $rows = [];
        foreach (array_values((array) ($block['rows'] ?? [])) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $rows[] = self::normalizeRow($row, $leafCount);
        }
        if ($rows === []) {
            $rows = [self::blankRow($leafCount), self::blankRow($leafCount)];
        }

        return [
            'type' => 'custom_table',
            'title' => (string) ($block['title'] ?? 'টেবিল:'),
            'columns' => $columns,
            'rows' => $rows,
            'merges' => self::normalizeMerges(array_values((array) ($block['merges'] ?? [])), count($rows), $leafCount),
        ];
    }

    /**
     * @param  list<array<string,mixed>>  $merges
     * @return list<array{r:int,c:int,rowspan:int,colspan:int}>
     */
    public static function normalizeMerges(array $merges, int $rowCount, int $leafCount): array
    {
        $out = [];
        foreach ($merges as $m) {
            if (! is_array($m)) {
                continue;
            }
            $r = (int) ($m['r'] ?? -1);
            $c = (int) ($m['c'] ?? -1);
            $rs = max(1, (int) ($m['rowspan'] ?? 1));
            $cs = max(1, (int) ($m['colspan'] ?? 1));
            if ($r < 0 || $c < 0 || $r >= $rowCount || $c >= $leafCount) {
                continue;
            }
            $rs = min($rs, $rowCount - $r);
            $cs = min($cs, $leafCount - $c);
            if ($rs === 1 && $cs === 1) {
                continue;
            }
            $out[] = ['r' => $r, 'c' => $c, 'rowspan' => $rs, 'colspan' => $cs];
        }

        return $out;
    }

    /**
     * @param  list<array<string,mixed>>  $columns
     * @return list<array{id:string,label:string,children:list<array<string,mixed>>}>
     */
    public static function normalizeColumns(array $columns): array
    {
        $out = [];
        foreach ($columns as $col) {
            if (! is_array($col)) {
                continue;
            }
            $children = self::normalizeColumns(array_values((array) ($col['children'] ?? [])));
            $node = [
                'id' => (string) ($col['id'] ?? self::newId()),
                'label' => (string) ($col['label'] ?? 'কলাম'),
                'children' => $children,
            ];
            if (isset($col['width']) && is_numeric($col['width'])) {
                $node['width'] = max(4, min(80, (float) $col['width']));
            }
            $out[] = $node;
        }

        return $out;
    }

    /**
     * @param  array<string,mixed>  $row
     * @return array{cells:list<string>,is_total:bool,lead_colspan:int}
     */
    public static function normalizeRow(array $row, int $leafCount): array
    {
        $cells = array_values((array) ($row['cells'] ?? []));
        $cells = array_map(static fn ($c) => (string) $c, $cells);
        while (count($cells) < $leafCount) {
            $cells[] = '';
        }
        if (count($cells) > $leafCount) {
            $cells = array_slice($cells, 0, $leafCount);
        }

        return [
            'cells' => $cells,
            'is_total' => (bool) ($row['is_total'] ?? false),
            'lead_colspan' => max(1, min($leafCount, (int) ($row['lead_colspan'] ?? 1))),
        ];
    }

    /**
     * @return array{cells:list<string>,is_total:bool,lead_colspan:int}
     */
    public static function blankRow(int $leafCount): array
    {
        return [
            'cells' => array_fill(0, max(1, $leafCount), ''),
            'is_total' => false,
            'lead_colspan' => 1,
        ];
    }

    /**
     * Flatten to leaf columns (body cell order).
     *
     * @param  list<array<string,mixed>>  $columns
     * @return list<array{id:string,label:string,width:?float}>
     */
    public static function leafColumns(array $columns): array
    {
        $leaves = [];
        foreach ($columns as $col) {
            $children = array_values((array) ($col['children'] ?? []));
            if ($children === []) {
                $leaves[] = [
                    'id' => (string) ($col['id'] ?? ''),
                    'label' => (string) ($col['label'] ?? ''),
                    'width' => isset($col['width']) && is_numeric($col['width']) ? (float) $col['width'] : null,
                ];
            } else {
                foreach (self::leafColumns($children) as $leaf) {
                    $leaves[] = $leaf;
                }
            }
        }

        return $leaves;
    }

    /**
     * Resolved width % per leaf (sums to ~100).
     *
     * @param  list<array<string,mixed>>  $columns
     * @return list<float>
     */
    public static function leafWidths(array $columns): array
    {
        $leaves = self::leafColumns($columns);
        $n = count($leaves);
        if ($n === 0) {
            return [];
        }
        $explicit = [];
        $unset = 0;
        $sumSet = 0.0;
        foreach ($leaves as $i => $leaf) {
            if ($leaf['width'] !== null) {
                $explicit[$i] = (float) $leaf['width'];
                $sumSet += $explicit[$i];
            } else {
                $unset++;
            }
        }
        $remaining = max(0, 100 - $sumSet);
        $default = $unset > 0 ? $remaining / $unset : 0;
        if ($unset === 0 && $sumSet > 0) {
            // normalize explicit to 100
            $out = [];
            foreach ($leaves as $i => $_) {
                $out[] = round(($explicit[$i] / $sumSet) * 100, 2);
            }

            return $out;
        }
        $out = [];
        foreach ($leaves as $i => $_) {
            $out[] = isset($explicit[$i]) ? round($explicit[$i], 2) : round($default, 2);
        }

        return $out;
    }

    /**
     * Body paint plan respecting merges (skip covered cells).
     *
     * @param  array<string,mixed>  $block
     * @return list<list<array{skip:bool,text:string,rowspan:int,colspan:int,r:int,c:int}|null>>
     */
    public static function bodyPaintPlan(array $block): array
    {
        $block = self::normalize($block);
        $rows = $block['rows'];
        $leafCount = self::leafCount($block['columns']);
        $rowCount = count($rows);
        $covered = array_fill(0, $rowCount, array_fill(0, $leafCount, false));
        $starts = [];

        foreach ($block['merges'] as $m) {
            $r = $m['r'];
            $c = $m['c'];
            $rs = $m['rowspan'];
            $cs = $m['colspan'];
            $starts[$r][$c] = ['rowspan' => $rs, 'colspan' => $cs];
            for ($rr = $r; $rr < $r + $rs && $rr < $rowCount; $rr++) {
                for ($cc = $c; $cc < $c + $cs && $cc < $leafCount; $cc++) {
                    if ($rr === $r && $cc === $c) {
                        continue;
                    }
                    $covered[$rr][$cc] = true;
                }
            }
        }

        // Total-row lead colspan as a horizontal merge if no conflicting merge start
        foreach ($rows as $ri => $row) {
            if (! ($row['is_total'] ?? false)) {
                continue;
            }
            $lead = max(1, min($leafCount, (int) ($row['lead_colspan'] ?? 1)));
            if ($lead <= 1 || isset($starts[$ri][0])) {
                continue;
            }
            $starts[$ri][0] = ['rowspan' => 1, 'colspan' => $lead];
            for ($cc = 1; $cc < $lead; $cc++) {
                $covered[$ri][$cc] = true;
            }
        }

        $plan = [];
        for ($r = 0; $r < $rowCount; $r++) {
            $plan[$r] = [];
            $cells = array_values((array) ($rows[$r]['cells'] ?? []));
            for ($c = 0; $c < $leafCount; $c++) {
                if ($covered[$r][$c]) {
                    $plan[$r][$c] = ['skip' => true, 'text' => '', 'rowspan' => 1, 'colspan' => 1, 'r' => $r, 'c' => $c];
                    continue;
                }
                $rs = $starts[$r][$c]['rowspan'] ?? 1;
                $cs = $starts[$r][$c]['colspan'] ?? 1;
                $plan[$r][$c] = [
                    'skip' => false,
                    'text' => (string) ($cells[$c] ?? ''),
                    'rowspan' => $rs,
                    'colspan' => $cs,
                    'r' => $r,
                    'c' => $c,
                ];
            }
        }

        return $plan;
    }

    public static function leafCount(array $columns): int
    {
        return count(self::leafColumns($columns));
    }

    public static function treeDepth(array $columns): int
    {
        $max = 1;
        foreach ($columns as $col) {
            $children = array_values((array) ($col['children'] ?? []));
            if ($children !== []) {
                $max = max($max, 1 + self::treeDepth($children));
            }
        }

        return $max;
    }

    /**
     * HTML/PDF thead matrix: list of rows, each cell = text, colspan, rowspan.
     *
     * @param  list<array<string,mixed>>  $columns
     * @return list<list<array{text:string,colspan:int,rowspan:int}>>
     */
    public static function headerMatrix(array $columns): array
    {
        $depth = self::treeDepth($columns);
        $matrix = array_fill(0, $depth, []);
        self::fillHeaderMatrix($columns, 0, $depth, $matrix);

        return $matrix;
    }

    /**
     * @param  list<array<string,mixed>>  $columns
     * @param  list<list<array{text:string,colspan:int,rowspan:int}>>  $matrix
     */
    protected static function fillHeaderMatrix(array $columns, int $level, int $tableDepth, array &$matrix): void
    {
        foreach ($columns as $col) {
            $children = array_values((array) ($col['children'] ?? []));
            $span = $children === [] ? 1 : self::leafCount($children);
            $rowspan = $children === [] ? max(1, $tableDepth - $level) : 1;

            $matrix[$level][] = [
                'text' => (string) ($col['label'] ?? ''),
                'colspan' => max(1, $span),
                'rowspan' => $rowspan,
            ];

            if ($children !== []) {
                self::fillHeaderMatrix($children, $level + 1, $tableDepth, $matrix);
            }
        }
    }

    /**
     * @param  list<array<string,mixed>>  $columns
     * @return list<int>|null  path of indexes into nested children
     */
    public static function findColumnPath(array $columns, string $id, array $prefix = []): ?array
    {
        foreach ($columns as $i => $col) {
            $path = [...$prefix, $i];
            if (($col['id'] ?? '') === $id) {
                return $path;
            }
            $children = array_values((array) ($col['children'] ?? []));
            if ($children !== []) {
                $found = self::findColumnPath($children, $id, $path);
                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }

    /**
     * @param  list<array<string,mixed>>  $columns
     * @param  list<int>  $path
     */
    public static function getColumnAt(array $columns, array $path): ?array
    {
        $cursor = $columns;
        $last = null;
        foreach ($path as $i => $index) {
            if (! isset($cursor[$index]) || ! is_array($cursor[$index])) {
                return null;
            }
            $last = $cursor[$index];
            $cursor = array_values((array) ($last['children'] ?? []));
        }

        return $last;
    }

    /**
     * @param  list<array<string,mixed>>  $columns
     * @param  list<int>  $path
     * @return list<array<string,mixed>>
     */
    public static function setColumnAt(array $columns, array $path, array $column): array
    {
        if ($path === []) {
            return $columns;
        }
        $index = $path[0];
        if (! isset($columns[$index])) {
            return $columns;
        }
        if (count($path) === 1) {
            $columns[$index] = $column;

            return array_values($columns);
        }
        $children = array_values((array) ($columns[$index]['children'] ?? []));
        $columns[$index]['children'] = self::setColumnAt($children, array_slice($path, 1), $column);

        return array_values($columns);
    }

    /**
     * @param  list<array<string,mixed>>  $columns
     * @param  list<int>  $path
     * @return list<array<string,mixed>>
     */
    public static function removeColumnAt(array $columns, array $path): array
    {
        if ($path === []) {
            return $columns;
        }
        $index = $path[0];
        if (! isset($columns[$index])) {
            return $columns;
        }
        if (count($path) === 1) {
            unset($columns[$index]);

            return array_values($columns);
        }
        $children = array_values((array) ($columns[$index]['children'] ?? []));
        $columns[$index]['children'] = self::removeColumnAt($children, array_slice($path, 1));

        return array_values($columns);
    }

    /**
     * @param  list<array<string,mixed>>  $columns
     * @param  list<int>|null  $parentPath  null = top level
     * @return list<array<string,mixed>>
     */
    public static function addColumn(array $columns, ?array $parentPath, array $newColumn): array
    {
        if ($parentPath === null || $parentPath === []) {
            $columns[] = $newColumn;

            return array_values($columns);
        }

        $parent = self::getColumnAt($columns, $parentPath);
        if ($parent === null) {
            return $columns;
        }
        $children = array_values((array) ($parent['children'] ?? []));
        $children[] = $newColumn;
        $parent['children'] = $children;

        return self::setColumnAt($columns, $parentPath, $parent);
    }

    /**
     * Resize all row cell arrays to match leaf count.
     *
     * @param  array<string,mixed>  $block
     * @return array<string,mixed>
     */
    public static function syncRowWidths(array $block): array
    {
        $block = self::normalize($block);
        $leaf = self::leafCount($block['columns']);
        foreach ($block['rows'] as $i => $row) {
            $block['rows'][$i] = self::normalizeRow($row, $leaf);
        }
        $block['merges'] = self::normalizeMerges($block['merges'] ?? [], count($block['rows']), $leaf);

        return $block;
    }

    /**
     * Find merge covering cell (r,c), if any.
     *
     * @param  array<string,mixed>  $block
     * @return array{r:int,c:int,rowspan:int,colspan:int}|null
     */
    public static function mergeAt(array $block, int $r, int $c): ?array
    {
        $block = self::normalize($block);
        foreach ($block['merges'] as $m) {
            if (
                $r >= $m['r'] && $r < $m['r'] + $m['rowspan']
                && $c >= $m['c'] && $c < $m['c'] + $m['colspan']
            ) {
                return $m;
            }
        }

        return null;
    }

    /**
     * Grow/shrink merge at (r,c) by deltas. Creating from unmerged cell works too.
     *
     * @param  array<string,mixed>  $block
     * @return array<string,mixed>
     */
    public static function adjustMerge(array $block, int $r, int $c, int $deltaRowspan, int $deltaColspan): array
    {
        $block = self::normalize($block);
        $existing = self::mergeAt($block, $r, $c);
        if ($existing === null) {
            $rs = max(1, 1 + $deltaRowspan);
            $cs = max(1, 1 + $deltaColspan);
            if ($rs === 1 && $cs === 1) {
                return $block;
            }

            return self::setMerge($block, $r, $c, $rs, $cs);
        }

        $rs = max(1, $existing['rowspan'] + $deltaRowspan);
        $cs = max(1, $existing['colspan'] + $deltaColspan);

        return self::setMerge($block, $existing['r'], $existing['c'], $rs, $cs);
    }

    /**
     * Whether two rectangular regions overlap (inclusive start, exclusive of size).
     */
    public static function regionsOverlap(
        int $r1,
        int $c1,
        int $rs1,
        int $cs1,
        int $r2,
        int $c2,
        int $rs2,
        int $cs2
    ): bool {
        return ! ($r1 + $rs1 <= $r2 || $r2 + $rs2 <= $r1 || $c1 + $cs1 <= $c2 || $c2 + $cs2 <= $c1);
    }

    /**
     * Set / replace a body merge starting at (r,c). Overlapping merges are removed.
     *
     * @param  array<string,mixed>  $block
     * @return array<string,mixed>
     */
    public static function setMerge(array $block, int $r, int $c, int $rowspan, int $colspan): array
    {
        $block = self::normalize($block);
        $rowCount = count($block['rows']);
        $leaf = self::leafCount($block['columns']);
        if ($r < 0 || $c < 0 || $r >= $rowCount || $c >= $leaf) {
            return $block;
        }
        $rowspan = max(1, min($rowspan, $rowCount - $r));
        $colspan = max(1, min($colspan, $leaf - $c));

        $merges = [];
        foreach ($block['merges'] as $m) {
            if (self::regionsOverlap($m['r'], $m['c'], $m['rowspan'], $m['colspan'], $r, $c, $rowspan, $colspan)) {
                continue;
            }
            $merges[] = $m;
        }
        if ($rowspan > 1 || $colspan > 1) {
            $merges[] = ['r' => $r, 'c' => $c, 'rowspan' => $rowspan, 'colspan' => $colspan];
        }
        $block['merges'] = $merges;

        return self::normalize($block);
    }

    /**
     * Clear merge that starts at (r,c), or any merge covering that cell.
     *
     * @param  array<string,mixed>  $block
     * @return array<string,mixed>
     */
    public static function clearMergeAt(array $block, int $r, int $c): array
    {
        $block = self::normalize($block);
        $block['merges'] = array_values(array_filter(
            $block['merges'],
            static function (array $m) use ($r, $c) {
                $covers = $r >= $m['r'] && $r < $m['r'] + $m['rowspan']
                    && $c >= $m['c'] && $c < $m['c'] + $m['colspan'];

                return ! $covers;
            }
        ));

        return self::normalize($block);
    }

    /**
     * Set width % on a leaf column by id.
     *
     * @param  list<array<string,mixed>>  $columns
     * @return list<array<string,mixed>>
     */
    public static function setLeafWidth(array $columns, string $leafId, float $width): array
    {
        $path = self::findColumnPath($columns, $leafId);
        if ($path === null) {
            return $columns;
        }
        $col = self::getColumnAt($columns, $path);
        if ($col === null) {
            return $columns;
        }
        if (array_values((array) ($col['children'] ?? [])) !== []) {
            return $columns;
        }
        $col['width'] = max(4, min(80, $width));

        return self::setColumnAt($columns, $path, $col);
    }

    /**
     * Resize top-level column count and/or body row count.
     *
     * @param  array<string,mixed>  $block
     * @return array<string,mixed>
     */
    public static function resize(array $block, int $topColumns, int $rowCount): array
    {
        $block = self::normalize($block);
        $topColumns = max(1, min(20, $topColumns));
        $rowCount = max(1, min(100, $rowCount));
        $columns = array_values($block['columns']);
        while (count($columns) < $topColumns) {
            $columns[] = self::columnNode('কলাম '.(count($columns) + 1));
        }
        while (count($columns) > $topColumns) {
            array_pop($columns);
        }
        $block['columns'] = $columns;
        $leaf = self::leafCount($columns);
        $rows = array_values($block['rows']);
        while (count($rows) < $rowCount) {
            $rows[] = self::blankRow($leaf);
        }
        while (count($rows) > $rowCount) {
            array_pop($rows);
        }
        $block['rows'] = $rows;
        $block['merges'] = self::normalizeMerges($block['merges'] ?? [], count($rows), $leaf);

        return self::normalize($block);
    }
}
