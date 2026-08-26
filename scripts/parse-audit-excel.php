<?php

require __DIR__.'/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

class FirstRowsFilter implements IReadFilter
{
    public function __construct(private int $maxRow = 6) {}

    public function readCell($columnAddress, $row, $worksheetName = ''): bool
    {
        return $row <= $this->maxRow;
    }
}

$path = __DIR__.'/../storage/app/templates/audit/AUDIT_FINDINGS_CONSOLIDATED_FORMAT.xlsx';
$outDir = __DIR__.'/../storage/app/templates/audit';

$reader = IOFactory::createReader('Xlsx');
$reader->setReadDataOnly(true);
$reader->setReadFilter(new FirstRowsFilter(6));
$ss = $reader->load($path);

file_put_contents($outDir.'/excel_sheets.json', json_encode([
    'sheets' => $ss->getSheetNames(),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

foreach ($ss->getWorksheetIterator() as $index => $sheet) {
    $name = $sheet->getTitle();
    $highestCol = $sheet->getHighestDataColumn();
    $highestColIndex = Coordinate::columnIndexFromString($highestCol);
    $highestRow = min($sheet->getHighestDataRow(), 6);

    $rows = [];
    for ($r = 1; $r <= $highestRow; $r++) {
        $row = [];
        for ($c = 1; $c <= $highestColIndex; $c++) {
            $val = $sheet->getCell(Coordinate::stringFromColumnIndex($c).$r)->getValue();
            if (is_object($val)) {
                $val = method_exists($val, '__toString') ? (string) $val : json_encode($val);
            }
            $row[] = is_string($val) ? trim(preg_replace('/\s+/u', ' ', $val)) : $val;
        }
        // trim trailing nulls
        while ($row !== [] && ($row[array_key_last($row)] === null || $row[array_key_last($row)] === '')) {
            array_pop($row);
        }
        $rows[] = $row;
    }

    $safe = preg_replace('/[^A-Za-z0-9_-]+/', '_', $name);
    file_put_contents(
        $outDir."/excel_sheet_{$index}_{$safe}.json",
        json_encode([
            'name' => $name,
            'highest_col' => $highestCol,
            'highest_col_index' => $highestColIndex,
            'sample_rows' => $rows,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );

    fwrite(STDOUT, "Wrote sheet {$index}: {$name} cols={$highestColIndex} dataCols=".count($rows[0] ?? [])."\n");
}

fwrite(STDOUT, "DONE\n");
