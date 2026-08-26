<?php

$path = $argv[1] ?? __DIR__.'/../storage/app/templates/audit/MF_Audit_Report_Format.doc';
$out = $argv[2] ?? __DIR__.'/../storage/app/templates/audit/MF_Audit_Report_Format_extracted.txt';

$bin = file_get_contents($path);
if ($bin === false) {
    fwrite(STDERR, "Cannot read $path\n");
    exit(1);
}

fwrite(STDOUT, 'File size: '.strlen($bin)."\n");
fwrite(STDOUT, 'Header: '.bin2hex(substr($bin, 0, 8))."\n");

// UTF-16LE readable sequences
$texts = [];
$len = strlen($bin);
$buf = '';
for ($i = 0; $i < $len - 1; $i += 2) {
    $code = ord($bin[$i]) | (ord($bin[$i + 1]) << 8);
    if ($code === 0x0A || $code === 0x0D || ($code >= 0x20 && $code <= 0x7E) || ($code >= 0x0980 && $code <= 0x09FF) || ($code >= 0xA0 && $code <= 0x024F)) {
        $buf .= mb_convert_encoding(pack('v', $code), 'UTF-8', 'UTF-16LE');
    } else {
        if (mb_strlen($buf) >= 4) {
            $texts[] = $buf;
        }
        $buf = '';
    }
}
if (mb_strlen($buf) >= 4) {
    $texts[] = $buf;
}

// Also ASCII runs
if (preg_match_all('/[\x09\x0A\x0D\x20-\x7E]{6,}/', $bin, $m)) {
    foreach ($m[0] as $chunk) {
        $texts[] = $chunk;
    }
}

$joined = implode("\n", $texts);
$joined = preg_replace("/[ \t]{2,}/", ' ', $joined);
$joined = preg_replace("/\n{3,}/", "\n\n", $joined);

file_put_contents($out, $joined);
fwrite(STDOUT, 'Chunks: '.count($texts).' bytes: '.strlen($joined)."\n");
fwrite(STDOUT, substr($joined, 0, 2000)."\n");
