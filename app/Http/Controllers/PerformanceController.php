<?php

namespace App\Http\Controllers;

use App\Models\PerformanceMark;
use App\Models\PerformanceRule;
use App\Models\User;
use App\Services\PerformanceService;
use App\Support\AppTime;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PerformanceController extends Controller
{
    public function index(Request $request, PerformanceService $performance): View
    {
        $period = $this->period($request, $performance);

        return view('performance.index', [
            'board' => $performance->board($period),
            'period' => $period,
            'performance' => $performance,
        ]);
    }

    public function export(Request $request, PerformanceService $performance): StreamedResponse
    {
        $period = $this->period($request, $performance);
        $rows = $performance->csvRows($performance->board($period));
        $name = 'performance-'.str_replace([' ', '–'], ['-', 'to'], strtolower($period['label'])).'.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, preg_replace('/-+/', '-', $name), ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function show(Request $request, User $user, PerformanceService $performance): View
    {
        $year = $this->year($request);

        return view('performance.show', [
            'person' => $user->loadMissing('roles:id,name'),
            'card' => $performance->scorecard($user, $year),
            'markRules' => $performance->rules(),
            'performance' => $performance,
        ]);
    }

    public function rules(PerformanceService $performance): View
    {
        $rules = $performance->rules(false);

        return view('performance.rules', [
            'rules' => $rules,
            'freeActivities' => collect(PerformanceRule::activities())->except($rules->pluck('key')->all())->all(),
            'markCounts' => PerformanceMark::query()->whereNotNull('performance_rule_id')->selectRaw('performance_rule_id, count(*) as n')->groupBy('performance_rule_id')->pluck('n', 'performance_rule_id'),
            'performance' => $performance,
        ]);
    }

    public function saveRules(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'rules' => ['required', 'array'],
            'rules.*.label' => ['required', 'string', 'max:80'],
            'rules.*.description' => ['nullable', 'string', 'max:255'],
            'rules.*.points' => ['required', 'numeric', 'between:-1000,1000'],
            'rules.*.monthly_cap' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'rules.*.is_active' => ['nullable', 'boolean'],
        ], [], ['rules.*.label' => 'rule name']);

        foreach ($data['rules'] as $id => $values) {
            PerformanceRule::query()->whereKey((int) $id)->update([
                'label' => trim($values['label']),
                'description' => trim((string) ($values['description'] ?? '')) ?: null,
                'points' => round((float) $values['points'], 2),
                'monthly_cap' => ($values['monthly_cap'] ?? null) ?: null,
                'is_active' => (bool) ($values['is_active'] ?? false),
            ]);
        }

        return redirect()->route('performance.rules')->with('status', 'Scoring rules saved. Every leaderboard now uses the new points.');
    }

    public function storeRule(Request $request): RedirectResponse
    {
        $taken = PerformanceRule::query()->pluck('key')->all();
        $free = array_keys(collect(PerformanceRule::activities())->except($taken)->all());

        $data = $request->validate([
            'counts' => ['required', Rule::in(array_merge([PerformanceRule::SOURCE_MANUAL], $free))],
            'label' => ['required', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:255'],
            'points' => ['required', 'numeric', 'between:-1000,1000'],
            'monthly_cap' => ['nullable', 'integer', 'min:1', 'max:10000'],
        ], [], ['label' => 'rule name', 'counts' => 'what counts']);

        $manual = $data['counts'] === PerformanceRule::SOURCE_MANUAL;
        $key = $manual ? 'custom_'.Str::lower(Str::random(10)) : $data['counts'];

        PerformanceRule::query()->create([
            'key' => $key,
            'source' => $manual ? PerformanceRule::SOURCE_MANUAL : PerformanceRule::SOURCE_AUTO,
            'label' => trim($data['label']),
            'description' => trim((string) ($data['description'] ?? '')) ?: null,
            'points' => round((float) $data['points'], 2),
            'monthly_cap' => ($data['monthly_cap'] ?? null) ?: null,
            'is_active' => true,
            'sort_order' => (int) PerformanceRule::query()->max('sort_order') + 1,
        ]);

        return redirect()->route('performance.rules')->with('status', $manual
            ? 'Rule "'.trim($data['label']).'" added. Give it to people from Special marks.'
            : 'Rule "'.trim($data['label']).'" added. It counts automatically from the activity log.');
    }

    public function destroyRule(PerformanceRule $rule): RedirectResponse
    {
        $marks = PerformanceMark::query()->where('performance_rule_id', $rule->id)->update([
            'performance_rule_id' => null,
            'points' => $rule->points,
        ]);
        $label = $rule->label;
        $rule->delete();

        return redirect()->route('performance.rules')->with('status', 'Rule "'.$label.'" removed.'
            .($marks ? ' '.$marks.' special '.($marks === 1 ? 'mark' : 'marks').' given with it stay, worth '.$this->points($rule->points).' each.' : ''));
    }

    public function resetRules(): RedirectResponse
    {
        foreach (PerformanceRule::defaults() as $rule) {
            PerformanceRule::query()->updateOrCreate(['key' => $rule['key']], $rule);
        }

        return redirect()->route('performance.rules')->with('status', 'Built-in rules reset to the defaults. Your own rules were kept.');
    }

    public function marks(Request $request, PerformanceService $performance): View
    {
        $year = $this->year($request);
        $people = $performance->people();
        $userId = (int) $request->query('user') ?: null;

        $marks = PerformanceMark::query()
            ->with(['user:id,name,email', 'rule:id,key,label,points,is_active', 'awardedByUser:id,name'])
            ->whereYear('awarded_on', $year)
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->orderByDesc('awarded_on')
            ->orderByDesc('id')
            ->get();

        return view('performance.marks', [
            'year' => $year,
            'people' => $people,
            'userId' => $userId,
            'rules' => $performance->rules(),
            'marks' => $marks,
            'performance' => $performance,
        ]);
    }

    public function storeMark(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'performance_rule_id' => ['nullable', 'integer', Rule::exists('performance_rules', 'id')->where('is_active', true)],
            'label' => ['required_without:performance_rule_id', 'nullable', 'string', 'max:120'],
            'points' => ['required_without:performance_rule_id', 'nullable', 'numeric', 'between:-1000,1000', 'not_in:0'],
            'note' => ['nullable', 'string', 'max:1000'],
            'awarded_on' => ['required', 'date', 'before_or_equal:'.AppTime::now()->addDay()->toDateString()],
        ], [
            'label.required_without' => 'Give the special mark a reason, or pick a rule.',
            'points.required_without' => 'Enter the points, or pick a rule.',
            'points.not_in' => 'Points cannot be zero.',
        ]);

        $rule = ! empty($data['performance_rule_id']) ? PerformanceRule::query()->find($data['performance_rule_id']) : null;

        $mark = PerformanceMark::query()->create([
            'user_id' => (int) $data['user_id'],
            'performance_rule_id' => $rule?->id,
            'label' => $rule ? $rule->label : trim((string) $data['label']),
            'points' => $rule ? $rule->points : round((float) $data['points'], 2),
            'note' => trim((string) ($data['note'] ?? '')) ?: null,
            'awarded_on' => $data['awarded_on'],
            'awarded_by' => $request->user()->id,
        ]);

        $name = User::query()->whereKey($mark->user_id)->value('name');

        return back()->with('status', $this->points($mark->points).' points given to '.$name.' for "'.$mark->label.'".');
    }

    public function destroyMark(PerformanceMark $mark): RedirectResponse
    {
        $mark->delete();

        return back()->with('status', 'Special mark removed. Scores are updated.');
    }

    private function points(float $points): string
    {
        return app(PerformanceService::class)->formatPoints($points);
    }

    private function year(Request $request): int
    {
        $year = (int) $request->query('year', AppTime::now()->year);

        return $year >= 2000 && $year <= 2100 ? $year : (int) AppTime::now()->year;
    }

    private function period(Request $request, PerformanceService $performance): array
    {
        return $performance->period(
            $request->query('period'),
            $request->query('year'),
            $request->query('month'),
            $request->query('from'),
            $request->query('to'),
        );
    }
}
