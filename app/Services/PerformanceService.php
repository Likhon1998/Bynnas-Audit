<?php

namespace App\Services;

use App\Models\PerformanceMark;
use App\Models\PerformanceRule;
use App\Models\User;
use App\Support\AppTime;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Turns auditor activity into points using the admin's scoring rules,
 * then ranks people for a month, a year or any date range.
 */
class PerformanceService
{
    public function __construct(private AuditorActivityService $activity) {}

    /**
     * @return array{type:string, year:int, month:int, from:CarbonImmutable, to:CarbonImmutable, label:string, prev_from:CarbonImmutable, prev_to:CarbonImmutable, prev_label:string, query:array<string,mixed>}
     */
    public function period(?string $type, $year = null, $month = null, ?string $from = null, ?string $to = null): array
    {
        $now = CarbonImmutable::parse(AppTime::now());
        $type = in_array($type, ['month', 'year', 'range'], true) ? $type : 'month';
        $year = (int) ($year ?: $now->year);
        $year = $year >= 2000 && $year <= 2100 ? $year : (int) $now->year;
        $month = (int) ($month ?: $now->month);
        $month = $month >= 1 && $month <= 12 ? $month : (int) $now->month;

        if ($type === 'year') {
            $start = CarbonImmutable::create($year, 1, 1)->startOfDay();
            $end = $start->endOfYear();
            $prevStart = $start->subYear();

            return [
                'type' => 'year', 'year' => $year, 'month' => $month,
                'from' => $start, 'to' => $end, 'label' => (string) $year,
                'prev_from' => $prevStart, 'prev_to' => $prevStart->endOfYear(), 'prev_label' => (string) ($year - 1),
                'query' => ['period' => 'year', 'year' => $year],
            ];
        }

        if ($type === 'range') {
            $start = $this->parseDate($from) ?? $now->startOfMonth();
            $end = $this->parseDate($to) ?? $now;
            if ($end->lessThan($start)) {
                [$start, $end] = [$end, $start];
            }
            $start = $start->startOfDay();
            $end = $end->endOfDay();
            $days = (int) $start->diffInDays($end) + 1;
            $prevEnd = $start->subDay()->endOfDay();
            $prevStart = $prevEnd->subDays($days - 1)->startOfDay();

            return [
                'type' => 'range', 'year' => $year, 'month' => $month,
                'from' => $start, 'to' => $end, 'label' => $start->format('d M Y').' – '.$end->format('d M Y'),
                'prev_from' => $prevStart, 'prev_to' => $prevEnd, 'prev_label' => $prevStart->format('d M').' – '.$prevEnd->format('d M Y'),
                'query' => ['period' => 'range', 'from' => $start->toDateString(), 'to' => $end->toDateString()],
            ];
        }

        $start = CarbonImmutable::create($year, $month, 1)->startOfDay();
        $prevStart = $start->subMonth();

        return [
            'type' => 'month', 'year' => $year, 'month' => $month,
            'from' => $start, 'to' => $start->endOfMonth(), 'label' => $start->format('F Y'),
            'prev_from' => $prevStart, 'prev_to' => $prevStart->endOfMonth(), 'prev_label' => $prevStart->format('F Y'),
            'query' => ['period' => 'month', 'year' => $year, 'month' => $month],
        ];
    }

    /**
     * People who can be ranked: report makers, without the Super Admin account.
     *
     * @return Collection<int, User>
     */
    public function people(): Collection
    {
        return $this->activity->auditors()
            ->reject(fn (User $user) => $user->isSuperAdmin() || $user->is_active === false)
            ->values();
    }

    /**
     * @return Collection<int, PerformanceRule>
     */
    public function rules(bool $activeOnly = true): Collection
    {
        return PerformanceRule::query()
            ->when($activeOnly, fn ($q) => $q->where('is_active', true))
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /**
     * How many times each rule happened, month by month (caps are per month).
     *
     * @param  Collection<int, array<string,mixed>>  $events
     * @return array<string, array<string, int>>
     */
    public function monthlyCounts(Collection $events): array
    {
        $months = [];
        foreach ($events->groupBy(fn ($e) => $e['at']->format('Y-m')) as $ym => $allEvents) {
            $monthEvents = $allEvents->where('type', '!=', 'mark');
            $months[$ym] = [
                'started' => $monthEvents->where('type', 'started')->count(),
                'completed' => $monthEvents->where('type', 'completed')->count(),
                'sent' => $monthEvents->whereIn('type', ['submitted', 'resubmitted'])->count(),
                'confirmed' => $monthEvents->where('type', 'approved')->count(),
                'first_time' => $monthEvents->where('type', 'approved')->where('first_time', true)->count(),
                'perfect' => $monthEvents->where('type', 'approved')->where('perfect', true)->count(),
                'returned' => $monthEvents->where('type', 'returned')->count(),
                'maker_done' => $monthEvents->where('type', 'maker_done')->count(),
                'visit_done' => $monthEvents->where('type', 'visit_done')->count(),
                'visit_delayed' => $monthEvents->where('type', 'visit_delayed')->count(),
                'email' => $monthEvents->where('type', 'email')->count(),
                'checklist' => $monthEvents->where('type', 'checklist')->count(),
                'active_day' => $monthEvents->map(fn ($e) => $e['at']->toDateString())->unique()->count(),
            ];
            foreach ($allEvents->where('type', 'mark')->whereNotNull('rule_key')->groupBy('rule_key') as $key => $marks) {
                $months[$ym][$key] = ($months[$ym][$key] ?? 0) + $marks->count();
            }
        }

        return $months;
    }

    /**
     * Special marks from the admin, shaped like activity events so they score the same way.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function markEvents(Collection $users, CarbonImmutable $from, CarbonImmutable $to): Collection
    {
        return PerformanceMark::query()
            ->with('rule:id,key')
            ->whereIn('user_id', $users->pluck('id'))
            ->whereBetween('awarded_on', [$from->toDateString(), $to->toDateString()])
            ->get()
            ->map(fn (PerformanceMark $mark) => [
                'type' => 'mark',
                'user_id' => (int) $mark->user_id,
                'at' => CarbonImmutable::parse($mark->awarded_on->toDateString())->setTime(12, 0),
                'rule_key' => $mark->rule?->key,
                'points' => (float) $mark->points,
                'label' => $mark->label,
            ]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function eventsFor(Collection $users, CarbonImmutable $from, CarbonImmutable $to): Collection
    {
        return $this->activity->events($users, $from, $to)->concat($this->markEvents($users, $from, $to))->values();
    }

    /**
     * @param  Collection<int, array<string,mixed>>  $events
     * @param  Collection<int, PerformanceRule>  $rules
     * @return array{total:float, plus:float, minus:float, lines:array<string, array{label:string, count:int, counted:int, points:float, subtotal:float}>}
     */
    public function score(Collection $events, Collection $rules): array
    {
        $months = $this->monthlyCounts($events);
        $lines = [];
        $total = 0.0;
        $plus = 0.0;
        $minus = 0.0;

        foreach ($rules as $rule) {
            $count = 0;
            $counted = 0;
            foreach ($months as $month) {
                $n = (int) ($month[$rule->key] ?? 0);
                $count += $n;
                $counted += $rule->monthly_cap ? min($n, (int) $rule->monthly_cap) : $n;
            }
            $subtotal = round($counted * (float) $rule->points, 2);
            $total += $subtotal;
            $subtotal >= 0 ? $plus += $subtotal : $minus += $subtotal;
            $lines[$rule->key] = [
                'label' => $rule->label,
                'count' => $count,
                'counted' => $counted,
                'points' => (float) $rule->points,
                'subtotal' => $subtotal,
            ];
        }

        $special = $events->where('type', 'mark')->whereNull('rule_key');
        $specialPlus = round($special->where('points', '>', 0)->sum('points'), 2);
        $specialMinus = round($special->where('points', '<', 0)->sum('points'), 2);
        $total += $specialPlus + $specialMinus;
        $plus += $specialPlus;
        $minus += $specialMinus;
        $lines['special'] = [
            'label' => 'Special marks',
            'count' => $special->count(),
            'counted' => $special->count(),
            'points' => null,
            'subtotal' => round($specialPlus + $specialMinus, 2),
            'plus' => $specialPlus,
        ];

        return ['total' => round($total, 2), 'plus' => round($plus, 2), 'minus' => round($minus, 2), 'lines' => $lines];
    }

    /**
     * Ranked leaderboard for a period, compared with the period before it.
     *
     * @return array<string, mixed>
     */
    public function board(array $period): array
    {
        $people = $this->people();
        $rules = $this->rules();
        $current = $this->eventsFor($people, $period['from'], $period['to'])->groupBy('user_id');
        $previous = $this->eventsFor($people, $period['prev_from'], $period['prev_to'])->groupBy('user_id');

        $rows = [];
        foreach ($people as $person) {
            $score = $this->score($current->get($person->id, collect()), $rules);
            $prev = $this->score($previous->get($person->id, collect()), $rules);
            $rows[] = [
                'id' => (int) $person->id,
                'name' => $person->name,
                'email' => $person->email,
                'role' => $person->roles->pluck('name')->map(fn ($n) => Str::headline($n))->join(', '),
                'score' => $score['total'],
                'plus' => $score['plus'],
                'minus' => $score['minus'],
                'lines' => $score['lines'],
                'prev_score' => $prev['total'],
                'delta' => round($score['total'] - $prev['total'], 2),
                'delta_pct' => $prev['total'] > 0 ? (int) round(($score['total'] - $prev['total']) / $prev['total'] * 100) : null,
                'activities' => $current->get($person->id, collect())->where('type', '!=', 'mark')->count(),
            ];
        }

        $rows = $this->rank($rows, 'score', 'rank');
        $prevRanks = collect($this->rank($rows, 'prev_score', 'prev_rank'))->pluck('prev_rank', 'id');
        foreach ($rows as &$row) {
            $row['prev_rank'] = $row['prev_score'] != 0 ? $prevRanks[$row['id']] : null;
        }
        unset($row);

        $count = fn (array $row, string $key) => (int) ($row['lines'][$key]['count'] ?? 0);
        $pick = function (callable $metric, ?callable $tie = null) use ($rows) {
            $best = null;
            foreach ($rows as $row) {
                $value = $metric($row);
                if ($value <= 0) {
                    continue;
                }
                if ($best === null || $value > $best['value'] || ($value == $best['value'] && $tie && $tie($row) > $tie($best['row']))) {
                    $best = ['row' => $row, 'value' => $value];
                }
            }

            return $best;
        };

        $awards = [
            'employee' => $pick(fn ($r) => $r['score'], fn ($r) => $count($r, 'confirmed')),
            'progressive' => $pick(fn ($r) => $r['delta'], fn ($r) => $r['score']),
            'quality' => $pick(fn ($r) => $count($r, 'first_time') + $count($r, 'perfect'), fn ($r) => $count($r, 'confirmed')),
            'field' => $pick(fn ($r) => $count($r, 'visit_done'), fn ($r) => $r['score']),
        ];

        $scored = collect($rows)->filter(fn ($r) => $r['score'] != 0);

        return [
            'period' => $period,
            'rules' => $rules,
            'rows' => $rows,
            'awards' => $awards,
            'team' => [
                'people' => count($rows),
                'scored' => $scored->count(),
                'total' => round(collect($rows)->sum('score'), 2),
                'average' => $scored->isNotEmpty() ? round($scored->avg('score'), 1) : 0,
                'prev_total' => round(collect($rows)->sum('prev_score'), 2),
                'improved' => collect($rows)->where('delta', '>', 0)->count(),
            ],
        ];
    }

    /**
     * One person's year: score and rank every month, plus the yearly breakdown.
     *
     * @return array<string, mixed>
     */
    public function scorecard(User $user, int $year): array
    {
        $people = $this->people();
        if (! $people->contains('id', $user->id)) {
            $people->push($user->loadMissing('roles:id,name'));
        }
        $rules = $this->rules();
        $from = CarbonImmutable::create($year, 1, 1)->startOfDay();
        $to = $from->endOfYear();
        $byUser = $this->eventsFor($people, $from, $to)->groupBy('user_id');

        $months = [];
        $now = CarbonImmutable::parse(AppTime::now());
        for ($m = 1; $m <= 12; $m++) {
            $monthStart = CarbonImmutable::create($year, $m, 1);
            $scores = [];
            foreach ($people as $person) {
                $events = $byUser->get($person->id, collect())->filter(fn ($e) => (int) $e['at']->month === $m);
                $scores[] = ['id' => (int) $person->id, 'score' => $this->score($events, $rules)['total']];
            }
            $ranked = collect($this->rank($scores, 'score', 'rank'))->keyBy('id');
            $mine = $ranked[$user->id];
            $months[$m] = [
                'label' => $monthStart->format('M'),
                'name' => $monthStart->format('F'),
                'score' => $mine['score'],
                'rank' => $mine['score'] != 0 ? $mine['rank'] : null,
                'future' => $monthStart->greaterThan($now),
                'best' => $mine['score'] > 0 && $mine['rank'] === 1,
            ];
        }

        $yearScores = [];
        foreach ($people as $person) {
            $yearScores[] = ['id' => (int) $person->id, 'score' => $this->score($byUser->get($person->id, collect()), $rules)['total']];
        }
        $yearRank = collect($this->rank($yearScores, 'score', 'rank'))->keyBy('id')[$user->id];
        $breakdown = $this->score($byUser->get($user->id, collect()), $rules);
        $prevBreakdown = $this->score(
            $this->eventsFor(collect([$user]), $from->subYear(), $to->subYear())->where('user_id', $user->id)->values(),
            $rules
        );

        $scoredMonths = collect($months)->filter(fn ($m) => $m['score'] != 0);
        $bestMonth = $scoredMonths->sortByDesc('score')->first();

        return [
            'year' => $year,
            'rules' => $rules,
            'months' => $months,
            'max' => max(1, ...array_map(fn ($m) => abs($m['score']), $months)),
            'total' => $breakdown['total'],
            'breakdown' => $breakdown,
            'prev_total' => $prevBreakdown['total'],
            'rank' => $breakdown['total'] != 0 ? $yearRank['rank'] : null,
            'people' => $people->count(),
            'best_month' => $bestMonth,
            'times_best' => collect($months)->where('best', true)->count(),
            'top_three' => collect($months)->filter(fn ($m) => $m['rank'] !== null && $m['rank'] <= 3)->count(),
            'average' => $scoredMonths->isNotEmpty() ? round($scoredMonths->avg('score'), 1) : 0,
            'marks' => PerformanceMark::query()
                ->with(['rule:id,key,label,points,is_active', 'awardedByUser:id,name'])
                ->where('user_id', $user->id)
                ->whereBetween('awarded_on', [$from->toDateString(), $to->toDateString()])
                ->orderByDesc('awarded_on')
                ->orderByDesc('id')
                ->get(),
        ];
    }

    /**
     * CSV lines for a leaderboard (header first).
     *
     * @return list<list<string|int|float>>
     */
    public function csvRows(array $board): array
    {
        $rules = $board['rules'];
        $header = ['Rank', 'Name', 'Email', 'Role', 'Score', 'Previous score', 'Change'];
        foreach ($rules as $rule) {
            $header[] = $rule->label.' ('.$this->formatPoints($rule->points).')';
        }
        $header[] = 'Special marks (points)';

        $lines = [$header];
        foreach ($board['rows'] as $row) {
            $line = [$row['rank'], $row['name'], $row['email'], $row['role'], $row['score'], $row['prev_score'], $row['delta']];
            foreach ($rules as $rule) {
                $line[] = $row['lines'][$rule->key]['count'] ?? 0;
            }
            $line[] = $row['lines']['special']['subtotal'] ?? 0;
            $lines[] = $line;
        }

        return $lines;
    }

    public function formatPoints(float $points): string
    {
        $text = rtrim(rtrim(number_format(abs($points), 2, '.', ''), '0'), '.');

        return ($points > 0 ? '+' : ($points < 0 ? '−' : '')).$text;
    }

    /**
     * Competition ranking (1, 2, 2, 4) by the given score key.
     *
     * @param  list<array<string,mixed>>  $rows
     * @return list<array<string,mixed>>
     */
    private function rank(array $rows, string $key, string $as): array
    {
        usort($rows, fn ($a, $b) => [$b[$key], $a['name'] ?? ''] <=> [$a[$key], $b['name'] ?? '']);
        $rank = 0;
        $last = null;
        foreach ($rows as $i => &$row) {
            if ($last === null || $row[$key] != $last) {
                $rank = $i + 1;
                $last = $row[$key];
            }
            $row[$as] = $rank;
        }
        unset($row);

        return $rows;
    }

    private function parseDate(?string $value): ?CarbonImmutable
    {
        if (! $value) {
            return null;
        }

        try {
            return CarbonImmutable::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }
}
