<?php

require __DIR__.'/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

$path = 'c:/Users/eGen/Downloads/AUDIT FINDINGD CONSOLATED FORMATE (1).xlsx';
$reader = IOFactory::createReaderForFile($path);
$reader->setReadDataOnly(true);
$reader->setReadEmptyCells(false);
$reader->setLoadSheetsOnly(['August, 2026']);
$reader->setReadFilter(new class implements IReadFilter
{
    public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool
    {
        return Coordinate::columnIndexFromString($columnAddress) <= 5;
    }
});
$sheet = $reader->load($path)->getSheetByName('August, 2026');

$val = fn ($c, $r) => trim((string) ($sheet->getCell($c.$r)->getValue() ?? ''));

for ($r = 4; $r <= 435; $r++) {
    $code = $val('C', $r);
    $title = $val('D', $r);
    if ($code === '' && $title !== '') {
        $prevC = $val('C', $r - 1);
        $prevD = mb_substr($val('D', $r - 1), -40);
        echo "R{$r}\n";
        echo "  PREV C={$prevC} D...{$prevD}\n";
        echo '  CUR  A='.mb_substr($val('A', $r), 0, 40).' B='.mb_substr($val('B', $r), 0, 40)."\n";
        echo '  CUR  D='.mb_substr($title, 0, 100)."\n\n";
    }
}
