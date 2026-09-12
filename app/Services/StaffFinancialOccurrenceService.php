<?php

namespace App\Services;

use App\Models\AuditFinding;
use App\Models\AuditReport;
use App\Models\ShakhaEmployee;
use App\Models\ShakhaEmployeeTransfer;
use Illuminate\Support\Collection;

class StaffFinancialOccurrenceService
{
    /**
     * Lifetime financial finding cells naming this employee (by fixed id).
     *
     * @return Collection<int, AuditFinding>
     */
    public function findingsForEmployee(int $employeeId): Collection
    {
        if ($employeeId < 1) {
            return collect();
        }

        return AuditFinding::query()
            ->with(['indicator:id,indicator_code,title,category', 'shakha:id,name,code'])
            ->whereNotNull('responsible_staff_ids')
            ->get()
            ->filter(function (AuditFinding $finding) use ($employeeId) {
                return in_array($employeeId, $finding->responsibleStaffIds(), true);
            })
            ->sortByDesc(fn (AuditFinding $f) => sprintf('%04d-%02d-%06d', $f->audit_year, $f->audit_month, $f->id))
            ->values();
    }

    /**
     * Distinct visit periods (year × month × shakha) — primary “N বার” count.
     *
     * @param  Collection<int, AuditFinding>  $findings
     */
    public function reportVisitCount(Collection $findings): int
    {
        return $findings
            ->map(fn (AuditFinding $f) => $f->audit_year.'-'.$f->audit_month.'-'.$f->shakha_id)
            ->unique()
            ->count();
    }

    /**
     * @param  Collection<int, AuditFinding>  $findings
     */
    public function distinctShakhaCount(Collection $findings): int
    {
        return $findings->pluck('shakha_id')->unique()->filter()->count();
    }

    /**
     * Map employee id → lifetime visit count (for Summary badges).
     *
     * @param  list<int>  $employeeIds
     * @return array<int, int>
     */
    public function lifetimeVisitCounts(array $employeeIds): array
    {
        $employeeIds = array_values(array_unique(array_filter(array_map('intval', $employeeIds), fn ($id) => $id > 0)));
        if ($employeeIds === []) {
            return [];
        }

        $counts = array_fill_keys($employeeIds, 0);
        $keysSeen = array_fill_keys($employeeIds, []);

        AuditFinding::query()
            ->whereNotNull('responsible_staff_ids')
            ->get(['id', 'shakha_id', 'audit_month', 'audit_year', 'responsible_staff_ids'])
            ->each(function (AuditFinding $finding) use ($employeeIds, &$counts, &$keysSeen) {
                $ids = $finding->responsibleStaffIds();
                $visitKey = $finding->audit_year.'-'.$finding->audit_month.'-'.$finding->shakha_id;
                foreach ($employeeIds as $employeeId) {
                    if (! in_array($employeeId, $ids, true)) {
                        continue;
                    }
                    if (isset($keysSeen[$employeeId][$visitKey])) {
                        continue;
                    }
                    $keysSeen[$employeeId][$visitKey] = true;
                    $counts[$employeeId]++;
                }
            });

        return $counts;
    }

    /**
     * Best-effort resolve employee ids from stored name text like "Name (CODE)".
     *
     * @return list<int>
     */
    public function resolveIdsFromStaffName(?string $raw, ?int $preferShakhaId = null): array
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return [];
        }

        $parts = preg_split('/\s*,\s*/u', $raw) ?: [];
        $ids = [];

        foreach ($parts as $part) {
            $part = trim((string) $part);
            if ($part === '') {
                continue;
            }

            $code = null;
            $name = $part;
            if (preg_match('/^(.+?)\s+\(([^)]+)\)\s*$/u', $part, $m)) {
                $name = trim($m[1]);
                $code = trim($m[2]);
            }

            $query = ShakhaEmployee::query();
            if ($code !== null && $code !== '') {
                $employee = (clone $query)->where('employee_code', $code)->first();
                if ($employee) {
                    $ids[] = (int) $employee->id;
                    continue;
                }
            }

            if ($preferShakhaId) {
                $matches = ShakhaEmployee::query()
                    ->where('shakha_id', $preferShakhaId)
                    ->where('name', $name)
                    ->get();
                if ($matches->count() === 1) {
                    $ids[] = (int) $matches->first()->id;
                }
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * @return array{
     *   report_count:int,
     *   finding_count:int,
     *   shakha_count:int,
     *   transfer_count:int,
     *   findings:Collection<int, AuditFinding>,
     *   transfers:Collection<int, ShakhaEmployeeTransfer>,
     *   report_links:array<string, ?string>
     * }
     */
    public function dossierPayload(ShakhaEmployee $employee): array
    {
        $findings = $this->findingsForEmployee((int) $employee->id);
        $transfers = ShakhaEmployeeTransfer::query()
            ->with(['fromShakha:id,name,code', 'toShakha:id,name,code'])
            ->where('shakha_employee_id', $employee->id)
            ->orderByDesc('transferred_at')
            ->orderByDesc('id')
            ->get();

        $reportLinks = [];
        foreach ($findings as $finding) {
            $key = $finding->audit_year.'-'.$finding->audit_month.'-'.$finding->shakha_id;
            if (array_key_exists($key, $reportLinks)) {
                continue;
            }
            $report = AuditReport::query()
                ->where('shakha_id', $finding->shakha_id)
                ->where('report_month', $finding->audit_month)
                ->where('report_year', $finding->audit_year)
                ->orderByDesc('id')
                ->first(['id']);
            $reportLinks[$key] = $report
                ? route('audits.index', ['report' => $report->id])
                : null;
        }

        return [
            'report_count' => $this->reportVisitCount($findings),
            'finding_count' => $findings->count(),
            'shakha_count' => $this->distinctShakhaCount($findings),
            'transfer_count' => $transfers->count(),
            'findings' => $findings,
            'transfers' => $transfers,
            'report_links' => $reportLinks,
        ];
    }
}
