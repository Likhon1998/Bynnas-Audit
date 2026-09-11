<?php

namespace App\Console\Commands;

use App\Models\AuditPlan;
use App\Models\User;
use App\Services\MonthlyWorklistService;
use App\Support\FinancialYear;
use Illuminate\Console\Command;

class ResetMonthlyVisitAllocations extends Command
{
    protected $signature = 'visits:reset-allocations
                            {--wipe-only : Delete allocations without auto-allocating again}
                            {--fy= : FY label e.g. 2026-2027 (default: current)}
                            {--month= : Calendar month 1-12 to re-allocate only (e.g. 9 = September)}
                            {--fy-month= : FY month index 0-11 only (Jul=0 … Jun=11)}';

    protected $description = 'Delete monthly visit allocations; optionally re-allocate all months or one month only';

    public function handle(MonthlyWorklistService $worklist): int
    {
        $deleted = $worklist->clearAllAllocations();
        $this->info("Deleted {$deleted} visit allocations.");

        if ($this->option('wipe-only')) {
            return self::SUCCESS;
        }

        $fyLabel = $this->option('fy');
        if (! $fyLabel || ! preg_match('/^\d{4}-\d{4}$/', (string) $fyLabel)) {
            $fyLabel = FinancialYear::current(now('Asia/Dhaka'))->label;
        }

        $plans = AuditPlan::query()
            ->where('fy_label', $fyLabel)
            ->orderBy('start_date')
            ->get();

        if ($plans->isEmpty()) {
            $this->warn("No annual audit plan for {$fyLabel}. Generate the yearly plan first.");

            return self::SUCCESS;
        }

        $monthIndexes = $this->resolveMonthIndexes();
        $userId = User::query()->where('is_superadmin', true)->value('id');

        foreach ($plans as $plan) {
            if (! $plan->generated_at) {
                $this->warn("Skipping {$plan->fy_label}: yearly plan is not generated.");

                continue;
            }

            $this->info("Plan {$plan->fy_label}:");
            foreach ($monthIndexes as $monthIndex) {
                $result = $worklist->bulkAllocateMonth($plan, $monthIndex, $userId);
                $label = FinancialYear::MONTH_LABELS[$monthIndex] ?? (string) $monthIndex;
                $this->line(sprintf(
                    '  %s (index %d): assigned %d / %d (skipped %d)',
                    $label,
                    $monthIndex,
                    $result['assigned'],
                    $result['total'],
                    $result['skipped']
                ));
            }
        }

        $this->info('Done.');

        return self::SUCCESS;
    }

    /**
     * @return list<int>
     */
    protected function resolveMonthIndexes(): array
    {
        if ($this->option('fy-month') !== null && $this->option('fy-month') !== '') {
            return [max(0, min(11, (int) $this->option('fy-month')))];
        }

        if ($this->option('month') !== null && $this->option('month') !== '') {
            $calendarMonth = (int) $this->option('month');
            if ($calendarMonth >= 1 && $calendarMonth <= 12) {
                // Jul=0 … Jun=11
                return [($calendarMonth + 5) % 12];
            }

            return [max(0, min(11, $calendarMonth))];
        }

        return range(0, 11);
    }
}
