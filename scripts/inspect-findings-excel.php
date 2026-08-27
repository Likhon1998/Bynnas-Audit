<?php

require __DIR__.'/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$path = 'c:/Users/eGen/Downloads/AUDIT FINDINGD CONSOLATED FORMATE (1).xlsx';
$ss = IOFactory::load($path);

echo 'SHEETS: '.implode(' | ', $ss->getSheetNames()).PHP_EOL;

foreach ($ss->getSheetNames() as $n) {
    $s = $ss->getSheetByName($n);
    echo PHP_EOL.'=== '.$n.' rows='.$s->getHighestDataRow().' cols='.$s->getHighestDataColumn().PHP_EOL;
    for ($r = 1; $r <= 8; $r++) {
        $vals = [];
        foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $c) {
            $v = trim((string) $s->getCell($c.$r)->getFormattedValue());
            $vals[] = $c.':'.mb_substr($v, 0, 80);
        }
        echo 'R'.$r.' '.implode(' || ', $vals).PHP_EOL;
    }
}
