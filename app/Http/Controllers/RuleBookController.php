<?php

namespace App\Http\Controllers;

use App\Models\Rule;
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
                    'where' => (string) ($row['where'] ?? ''),
                    'when' => (string) ($row['when'] ?? ''),
                    'who' => (string) ($row['who'] ?? ''),
                ];
            }
        }

        return view('rule-book.index', [
            'rules' => Rule::query()->orderBy('serial')->orderBy('id')->get(),
            'formRows' => $formRows !== [] ? $formRows : [[
                'statement' => '',
                'article' => '',
                'where' => '',
                'when' => '',
                'who' => '',
            ]],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'source_name' => ['nullable', 'string', 'max:255'],
            'rules' => ['required', 'array', 'min:1', 'max:40'],
            'rules.*.statement' => ['nullable', 'string', 'max:5000'],
            'rules.*.article' => ['nullable', 'string', 'max:255'],
            'rules.*.where' => ['nullable', 'string', 'max:255'],
            'rules.*.when' => ['nullable', 'string', 'max:255'],
            'rules.*.who' => ['nullable', 'string', 'max:255'],
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
                'reference_where' => trim((string) ($row['where'] ?? '')) ?: $source,
                'reference_when' => trim((string) ($row['when'] ?? '')),
                'reference_who' => trim((string) ($row['who'] ?? '')),
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
            'where' => ['nullable', 'string', 'max:255'],
            'when' => ['nullable', 'string', 'max:255'],
            'who' => ['nullable', 'string', 'max:255'],
            'source_name' => ['nullable', 'string', 'max:255'],
        ]);

        $statement = trim($data['statement']);
        $rule->update([
            'title' => $this->titleFrom($statement),
            'statement' => $statement,
            'article' => trim((string) ($data['article'] ?? '')),
            'reference_where' => trim((string) ($data['where'] ?? '')),
            'reference_when' => trim((string) ($data['when'] ?? '')),
            'reference_who' => trim((string) ($data['who'] ?? '')),
            'source_name' => trim((string) ($data['source_name'] ?? '')),
        ]);

        return back()->with('status', 'Rule saved.');
    }

    public function destroy(Rule $rule): RedirectResponse
    {
        $rule->delete();

        return back()->with('status', 'Rule removed.');
    }

    private function titleFrom(string $statement): string
    {
        $words = preg_split('/\s+/u', $statement) ?: [];
        $title = trim(implode(' ', array_slice($words, 0, 10)), " ।,;.");

        return $title !== '' ? $title : 'Rule';
    }
}
