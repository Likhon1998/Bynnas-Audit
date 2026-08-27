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
        $col = Coordinate::columnIndexFromString($columnAddress);

        return $col >= 1 && $col <= 5;
    }
});

$ss = $reader->load($path);
$sheet = $ss->getSheetByName('August, 2026');
$highest = (int) $sheet->getHighestDataRow();

$cell = function ($col, $row) use ($sheet) {
    $v = $sheet->getCell($col.$row)->getValue();
    if ($v === null) {
        return '';
    }
    if (is_float($v) && floor($v) == $v) {
        return (string) (int) $v;
    }

    return trim(preg_replace('/\s+/u', ' ', (string) $v) ?? '');
};

$emptyBoth = 0;
$codeOnly = 0;
$titleOnly = 0;
$ok = 0;
$dup = 0;
$seen = [];
$samples = [];

for ($r = 4; $r <= $highest; $r++) {
    $code = $cell('C', $r);
    $title = $cell('D', $r);
    if ($code !== '' && $title !== '') {
        if (isset($seen[$code])) {
            $dup++;
            if (count($samples) < 15) {
                $samples[] = "DUP R{$r} {$code}";
            }
        } else {
            $seen[$code] = true;
            $ok++;
        }
    } elseif ($code === '' && $title === '') {
        $emptyBoth++;
    } elseif ($code !== '' && $title === '') {
        $codeOnly++;
        if (count($samples) < 30) {
            $samples[] = "CODE_ONLY R{$r} {$code} A=".$cell('A', $r).' B='.$cell('B', $r);
        }
    } else {
        $titleOnly++;
        if (count($samples) < 30) {
            $samples[] = "TITLE_ONLY R{$r} ".mb_substr($title, 0, 50).' A='.$cell('A', $r);
        }
    }
}

echo "highest={$highest} ok={$ok} dup={$dup} emptyBoth={$emptyBoth} codeOnly={$codeOnly} titleOnly={$titleOnly}\n";
echo implode("\n", $samples)."\n";

// Check if getFormattedValue recovers more titles
$codeOnlyFmt = 0;
for ($r = 4; $r <= $highest; $r++) {
    $code = $cell('C', $r);
    $title = trim((string) $sheet->getCell('D'.$r)->getFormattedValue());
    $titleVal = $cell('D', $r);
    if ($code !== '' && $titleVal === '' && $title !== '') {
        $codeOnlyFmt++;
        echo "FMT_HELP R{$r} {$code} => ".mb_substr($title, 0, 60)."\n";
    }
}
echo "codeOnly recoverable via formatted={$codeOnlyFmt}\n";
