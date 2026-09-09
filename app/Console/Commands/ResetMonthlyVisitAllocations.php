<?php

namespace App\Console\Commands;

use App\Models\AuditPlan;
use App\Models\User;
use App\Services\MonthlyWorklistService;
use Illuminate\Console\Command;

class ResetMonthlyVisitAllocations extends Command
{
    protected $signature = 'visits:reset-allocations
                            {--wipe-only : Delete allocations without auto-allocating again}';

    protected $description = 'Delete every monthly visit allocation and optionally re-allocate dates inside each plan month';

    public function handle(MonthlyWorklistService $worklist): int
    {
        $deleted = $worklist->clearAllAllocations();
        $this->info("Deleted {$deleted} visit allocations.");

        if ($this->option('wipe-only')) {
            return self::SUCCESS;
        }

        $plans = AuditPlan::query()->orderBy('start_date')->get();
        if ($plans->isEmpty()) {
            $this->warn('No annual audit plan found. Generate a yearly plan before allocating.');

            return self::SUCCESS;
        }

        $userId = User::query()->where('is_superadmin', true)->value('id');

        foreach ($plans as $plan) {
            if (! $plan->generated_at) {
                $this->warn("Skipping {$plan->fy_label}: yearly plan is not generated.");

                continue;
            }

            for ($monthIndex = 0; $monthIndex < 12; $monthIndex++) {
                $result = $worklist->bulkAllocateMonth($plan, $monthIndex, $userId);
                $this->line(sprintf(
                    '  %s month %d: assigned %d / %d (skipped %d)',
                    $plan->fy_label,
                    $monthIndex,
                    $result['assigned'],
                    $result['total'],
                    $result['skipped']
                ));
            }
        }

        $this->info('Allocations rebuilt with dates inside each selected month.');

        return self::SUCCESS;
    }
}
