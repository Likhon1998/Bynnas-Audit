<x-app-layout>
    <form method="POST" action="{{ route('rule-book.store') }}" class="px-3 py-3 lg:px-5">
        @csrf
        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
            <div>
                <a href="{{ route('rule-book.index') }}" class="text-[11px] font-medium text-slate-500 hover:text-brand-700">Rule book</a>
                <h1 class="text-[15px] font-semibold tracking-tight text-navy-900">{{ count($rules) }} rules from {{ $source }}</h1>
                <p class="text-[12px] text-slate-500">Read each rule. Keep the ones you want. Nothing is saved until you add them.</p>
            </div>
            <div class="flex items-center gap-1.5">
                <button type="submit" name="add_all" value="1" class="inline-flex h-8 items-center rounded-lg border border-emerald-200 bg-emerald-50 px-3 text-[12px] font-semibold text-emerald-800 hover:bg-emerald-100">Add all</button>
                <button type="submit" class="inline-flex h-8 items-center rounded-lg bg-navy-900 px-3 text-[12px] font-semibold text-white hover:bg-navy-800">Add selected</button>
            </div>
        </div>

        <ol class="space-y-2">
            @foreach ($rules as $index => $rule)
                <li class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 shadow-sm">
                    <label class="flex items-start gap-2">
                        <input type="checkbox" name="rules[]" value="{{ $index }}" checked class="mt-1 rounded border-slate-300 text-navy-900 focus:ring-navy-900">
                        <span class="min-w-0">
                            <span class="block text-[12px] font-semibold text-navy-900">{{ $index + 1 }}. {{ $rule['title'] }}</span>
                            <span class="mt-1 block text-[13px] leading-snug text-slate-700">{{ $rule['statement'] }}</span>
                            <span class="mt-1 block text-[11px] text-slate-500">
                                অনুচ্ছেদ: {{ $rule['article'] !== '' ? $rule['article'] : '—' }}
                                · কোথায়: {{ $rule['where'] !== '' ? $rule['where'] : '—' }}
                                · কখন: {{ $rule['when'] !== '' ? $rule['when'] : '—' }}
                                · কে: {{ $rule['who'] !== '' ? $rule['who'] : '—' }}
                            </span>
                        </span>
                    </label>
                </li>
            @endforeach
        </ol>
    </form>
</x-app-layout>
