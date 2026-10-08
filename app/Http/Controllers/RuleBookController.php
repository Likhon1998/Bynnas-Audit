<?php

namespace App\Http\Controllers;

use App\Models\Rule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RuleBookController extends Controller
{
    public function index(): View
    {
        $old = old('rules');
        $formRows = [];
        if (is_array($old)) {
            foreach ($old as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $formRows[] = [
                    'statement' => (string) ($row['statement'] ?? ''),
                    'article' => (string) ($row['article'] ?? ''),
                ];
            }
        }

        $rules = Rule::query()->orderBy('serial')->orderBy('id')->get();
        $groups = $rules->groupBy(fn (Rule $rule) => $this->documentName($rule));

        return view('rule-book.index', [
            'groups' => $groups,
            'ruleCount' => $rules->count(),
            'catalog' => $rules->map(fn (Rule $rule) => [
                'id' => $rule->id,
                'source' => $this->documentName($rule),
                'haystack' => mb_strtolower(implode(' ', array_filter([
                    $rule->statement,
                    $rule->article,
                    $rule->source_name,
                ]))),
            ])->values(),
            'formRows' => $formRows !== [] ? $formRows : [[
                'statement' => '',
                'article' => '',
            ]],
            'openComposer' => $formRows !== [] && session()->hasOldInput(),
        ]);
    }

    private function documentName(Rule $rule): string
    {
        $name = trim((string) $rule->source_name);

        return $name !== '' ? $name : 'No document';
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'source_name' => ['nullable', 'string', 'max:255'],
            'rules' => ['required', 'array', 'min:1', 'max:40'],
            'rules.*.statement' => ['nullable', 'string', 'max:5000'],
            'rules.*.article' => ['nullable', 'string', 'max:255'],
        ]);

        $source = trim((string) ($data['source_name'] ?? ''));
        $next = ((int) Rule::query()->max('serial')) + 1;
        $added = 0;

        foreach ($data['rules'] as $row) {
            $statement = trim((string) ($row['statement'] ?? ''));
            if ($statement === '') {
                continue;
            }

            Rule::query()->create([
                'serial' => $next++,
                'title' => $this->titleFrom($statement),
                'statement' => $statement,
                'article' => trim((string) ($row['article'] ?? '')),
                'reference_where' => '',
                'reference_when' => '',
                'reference_who' => '',
                'source_name' => $source,
                'created_by' => $request->user()?->id,
            ]);
            $added++;
        }

        if ($added === 0) {
            return back()
                ->withErrors(['rules' => 'Write at least one rule before saving.'])
                ->withInput();
        }

        return redirect()
            ->route('rule-book.index')
            ->with('status', $added.' rule'.($added === 1 ? '' : 's').' saved.');
    }

    public function update(Request $request, Rule $rule): RedirectResponse
    {
        $data = $request->validate([
            'statement' => ['required', 'string', 'max:5000'],
            'article' => ['nullable', 'string', 'max:255'],
            'source_name' => ['nullable', 'string', 'max:255'],
        ]);

        $statement = trim($data['statement']);
        $rule->update([
            'title' => $this->titleFrom($statement),
            'statement' => $statement,
            'article' => trim((string) ($data['article'] ?? '')),
            'reference_where' => '',
            'reference_when' => '',
            'reference_who' => '',
            'source_name' => trim((string) ($data['source_name'] ?? '')),
        ]);

        return back()->with('status', 'Rule saved.');
    }

    public function quickStore(Request $request): JsonResponse
    {
        $data = $request->validate([
            'statement' => ['required', 'string', 'max:5000'],
            'article' => ['nullable', 'string', 'max:255'],
            'source_name' => ['nullable', 'string', 'max:255'],
        ]);
        $statement = trim((string) $data['statement']);
        $statement = preg_replace('/\s+/u', ' ', $statement) ?? $statement;
        $article = trim((string) ($data['article'] ?? ''));
        $source = trim((string) ($data['source_name'] ?? ''));

        $existing = Rule::query()->orderBy('id')->get()->first(function (Rule $rule) use ($statement) {
            $saved = preg_replace('/\s+/u', ' ', trim((string) $rule->statement)) ?? '';
            $cited = preg_replace('/\s+/u', ' ', trim($rule->criteriaText())) ?? '';

            return $saved === $statement || $cited === $statement;
        });

        if ($existing instanceof Rule) {
        return response()->json([
            'added' => false,
            'group' => $this->documentName($existing),
            'label' => $this->pickerLabel($existing),
            'value' => $existing->criteriaText(),
            'article' => trim((string) $existing->article) !== '' ? $existing->article : (string) $existing->serial,
            'statement' => $existing->statement,
        ]);
        }

        $rule = Rule::query()->create([
            'serial' => ((int) Rule::query()->max('serial')) + 1,
            'title' => $this->titleFrom($statement),
            'statement' => $statement,
            'article' => $article,
            'reference_where' => '',
            'reference_when' => '',
            'reference_who' => '',
            'source_name' => $source,
            'created_by' => $request->user()?->id,
        ]);

        return response()->json([
            'added' => true,
            'group' => $this->documentName($rule),
            'label' => $this->pickerLabel($rule),
            'value' => $rule->criteriaText(),
            'article' => $article !== '' ? $article : (string) $rule->serial,
            'statement' => $rule->statement,
        ]);
    }

    private function pickerLabel(Rule $rule): string
    {
        $number = trim((string) $rule->article) !== '' ? $rule->article : (string) $rule->serial;

        return $number.'. '.\Illuminate\Support\Str::limit((string) $rule->statement, 90);
    }

    public function destroy(Rule $rule): RedirectResponse
    {
        $rule->delete();

        return back()->with('status', 'Rule removed.');
    }

    private function titleFrom(string $statement): string
    {
        return Rule::titleFromStatement($statement);
    }
}
