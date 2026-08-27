<?php

namespace App\Console\Commands;

use App\Models\AuditFinding;
use App\Models\AuditIndicator;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use Throwable;

class ImportAuditIndicators extends Command
{
    protected $signature = 'audit:import-indicators
                            {filepath : Absolute or relative path to the consolidated Excel workbook}
                            {--sheet=August, 2026 : Worksheet name to read}
                            {--fresh : Delete all existing indicators (and findings) before import}';

    protected $description = 'Import all audit indicator rules from Excel cols A–E (including uncoded new rows)';

    public function handle(): int
    {
        $filepath = $this->argument('filepath');
        $sheetName = (string) $this->option('sheet');

        if (! is_file($filepath)) {
            $this->error("File not found: {$filepath}");

            return self::FAILURE;
        }

        if ($this->option('fresh')) {
            $findingsDeleted = AuditFinding::query()->count();
            $indicatorsDeleted = AuditIndicator::query()->count();
            AuditFinding::query()->delete();
            AuditIndicator::query()->delete();
            $this->warn("Fresh import: deleted {$indicatorsDeleted} indicators and {$findingsDeleted} findings.");
        }

        $this->info("Loading columns A–E from: {$filepath}");
        $this->info("Sheet: {$sheetName}");

        try {
            $reader = IOFactory::createReaderForFile($filepath);
            $reader->setReadDataOnly(true);
            $reader->setReadEmptyCells(false);

            if (method_exists($reader, 'setLoadSheetsOnly')) {
                $reader->setLoadSheetsOnly([$sheetName]);
            }

            $reader->setReadFilter(new class implements IReadFilter
            {
                public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool
                {
                    $col = Coordinate::columnIndexFromString($columnAddress);

                    return $col >= 1 && $col <= 5;
                }
            });

            $spreadsheet = $reader->load($filepath);
        } catch (Throwable $e) {
            $this->error('Could not open workbook: '.$e->getMessage());

            return self::FAILURE;
        }

        $sheet = $spreadsheet->getSheetByName($sheetName);
        if ($sheet === null) {
            $names = implode(', ', $spreadsheet->getSheetNames());
            $this->error("Sheet \"{$sheetName}\" not found. Available: {$names}");

            return self::FAILURE;
        }

        $highestRow = (int) $sheet->getHighestDataRow();
        $this->info("Scanning rows 4..{$highestRow}");

        $currentCategory = null;
        $currentSubCategory = null;
        $imported = 0;
        $updated = 0;
        $autoCoded = 0;
        $skipped = 0;
        $seenCodes = [];

        $bar = $this->output->createProgressBar(max(1, $highestRow - 3));
        $bar->start();

        for ($row = 4; $row <= $highestRow; $row++) {
            $categoryCell = $this->cellString($sheet->getCell('A'.$row)->getValue());
            $subCategoryCell = $this->cellString($sheet->getCell('B'.$row)->getValue());
            $indicatorCode = $this->cellString($sheet->getCell('C'.$row)->getValue());
            $title = $this->cellString($sheet->getCell('D'.$row)->getValue());
            $riskRating = $this->cellString($sheet->getCell('E'.$row)->getValue());

            if ($categoryCell !== '') {
                $currentCategory = $categoryCell;
            }
            if ($subCategoryCell !== '') {
                $currentSubCategory = $subCategoryCell;
            }

            if ($title === '') {
                $skipped++;
                $bar->advance();

                continue;
            }

            $isAuto = false;
            if ($indicatorCode === '') {
                // Excel often leaves Col C blank for newly added rules / wrap rows.
                // Keep every titled row as a rule with a stable unique code.
                $indicatorCode = 'নতুন-'.$row;
                $isAuto = true;
            }

            if (isset($seenCodes[$indicatorCode])) {
                // True duplicate code in sheet — suffix to keep both titles.
                $indicatorCode = $indicatorCode.'-R'.$row;
                $isAuto = true;
            }
            $seenCodes[$indicatorCode] = true;

            $indicator = AuditIndicator::query()->updateOrCreate(
                ['indicator_code' => $indicatorCode],
                [
                    'category' => $currentCategory,
                    'sub_category' => $currentSubCategory,
                    'title' => $title,
                    'risk_rating' => $riskRating !== '' ? $riskRating : null,
                    'is_active' => true,
                ]
            );

            if ($indicator->wasRecentlyCreated) {
                $imported++;
            } else {
                $updated++;
            }
            if ($isAuto) {
                $autoCoded++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        $total = AuditIndicator::query()->count();
        $withCategory = AuditIndicator::query()->whereNotNull('category')->where('category', '!=', '')->count();

        $this->info('Audit indicators import finished.');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Created', $imported],
                ['Updated', $updated],
                ['Auto-coded (blank Col C)', $autoCoded],
                ['Skipped (no title)', $skipped],
                ['Total in database', $total],
                ['With category', $withCategory],
            ]
        );

        $first = AuditIndicator::query()->orderBy('id')->first();
        $last = AuditIndicator::query()->orderByDesc('id')->first();
        if ($first && $last) {
            $this->line("First: {$first->indicator_code} — ".mb_substr($first->title, 0, 70));
            $this->line("Last:  {$last->indicator_code} — ".mb_substr($last->title, 0, 70));
        }

        if ($total >= 420) {
            $this->info("Success — all {$total} titled rule rows are in the database.");
        } else {
            $this->warn("Loaded {$total} rules. Expected ~432 titled rows from the sheet.");
        }

        return self::SUCCESS;
    }

    private function cellString(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_float($value) || is_int($value)) {
            if (is_float($value) && floor($value) == $value) {
                return (string) (int) $value;
            }

            return trim((string) $value);
        }

        $text = trim((string) $value);
        $text = preg_replace("/\x{00A0}/u", ' ', $text) ?? $text;
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }
}
