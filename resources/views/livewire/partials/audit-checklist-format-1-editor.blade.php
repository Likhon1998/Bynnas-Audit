{{-- Shared Format editor body. Expects: $formatModel, $definition, $payload --}}
@php
    $sections = $definition['sections'] ?? [];
@endphp

<div class="overflow-hidden rounded-xl border border-slate-300 bg-white shadow-sm">
    <div class="border-b border-slate-200 bg-slate-50 px-4 py-3 text-center">
        <p class="text-[11px] font-semibold text-slate-500">Format: {{ $formatModel?->format_number }}</p>
        <p class="text-[15px] font-bold text-navy-900">{{ $formatModel?->org_name }}</p>
        <p class="text-[12px] font-semibold text-slate-700">{{ $formatModel?->dept_name }}</p>
        <p class="mt-1 text-[13px] font-bold text-navy-900">“{{ $formatModel?->heading }}”</p>
    </div>

    <div class="grid gap-3 border-b border-slate-200 px-4 py-3 sm:grid-cols-2">
        <div>
            <label class="mb-0.5 block text-[11px] font-semibold text-slate-600">শাখার নাম :</label>
            <input type="text" wire:model.live="shakha_name" class="h-9 w-full rounded-md border-slate-200 text-[12px] focus:border-[#2b579a] focus:ring-[#2b579a]">
        </div>
        <div>
            <label class="mb-0.5 block text-[11px] font-semibold text-slate-600">নিরীক্ষা কাল :</label>
            <input type="text" wire:model.live="audit_period" class="h-9 w-full rounded-md border-slate-200 text-[12px] focus:border-[#2b579a] focus:ring-[#2b579a]">
        </div>
    </div>

    <div class="overflow-x-auto px-2 py-3">
        <table class="min-w-[980px] w-full border-collapse text-[11px]">
            <thead>
                <tr class="bg-slate-100 text-center font-semibold text-slate-700">
                    <th class="border border-slate-300 px-1 py-1.5 w-10">ক্রঃ নং</th>
                    <th class="border border-slate-300 px-1 py-1.5 min-w-[140px]">সমিতির নাম ও আইডি</th>
                    <th class="border border-slate-300 px-1 py-1.5 min-w-[100px]">সমিতি শুরুর তারিখ</th>
                    <th class="border border-slate-300 px-1 py-1.5 min-w-[110px]">মাঠকর্মীর নাম</th>
                    <th class="border border-slate-300 px-1 py-1.5" colspan="5">সমিতি গঠন</th>
                    <th class="border border-slate-300 px-1 py-1.5" colspan="3">সমিতি বন্ধ /একত্রিকরণ</th>
                    <th class="border border-slate-300 px-1 py-1.5" colspan="4">সমিতি স্থানান্তর (পার্শ্ববর্তী শাখায়)</th>
                    <th class="border border-slate-300 px-1 py-1.5 w-16">WP Ref.</th>
                    <th class="border border-slate-300 px-1 py-1.5 w-10"></th>
                </tr>
                <tr class="bg-slate-50 text-center font-semibold text-slate-600">
                    <th class="border border-slate-300" colspan="4"></th>
                    @for ($i = 1; $i <= 5; $i++)
                        <th class="border border-slate-300 px-0.5 py-1 w-8">{{ $i }}</th>
                    @endfor
                    @for ($i = 1; $i <= 3; $i++)
                        <th class="border border-slate-300 px-0.5 py-1 w-8">{{ $i }}</th>
                    @endfor
                    @for ($i = 1; $i <= 4; $i++)
                        <th class="border border-slate-300 px-0.5 py-1 w-8">{{ $i }}</th>
                    @endfor
                    <th class="border border-slate-300" colspan="2"></th>
                </tr>
            </thead>
            <tbody>
                @foreach (['formation' => 'সমিতি গঠন', 'closure' => 'সমিতি বন্ধ /একত্রিকরণ', 'transfer' => 'সমিতি স্থানান্তর (পার্শ্ববর্তী শাখায়)'] as $sectionKey => $sectionLabel)
                    @php $rows = $payload['sections'][$sectionKey] ?? []; @endphp
                    <tr class="bg-sky-50/70">
                        <td colspan="18" class="border border-slate-300 px-2 py-1.5 text-[12px] font-bold text-navy-900">
                            <div class="flex items-center justify-between gap-2">
                                <span>{{ $sectionLabel }}</span>
                                <button type="button" wire:click="addRow('{{ $sectionKey }}')" class="rounded border border-sky-200 bg-white px-2 py-0.5 text-[10px] font-semibold text-[#2b579a] hover:bg-sky-50">+ Row</button>
                            </div>
                        </td>
                    </tr>
                    @foreach ($rows as $ri => $row)
                        <tr wire:key="row-{{ $sectionKey }}-{{ $ri }}">
                            <td class="border border-slate-300 px-1 py-1 text-center tabular-nums">{{ $ri + 1 }}</td>
                            <td class="border border-slate-300 p-0.5">
                                <input type="text" wire:model.live="payload.sections.{{ $sectionKey }}.{{ $ri }}.society_name" class="h-8 w-full border-0 bg-transparent px-1 text-[11px] focus:ring-1 focus:ring-[#2b579a]">
                            </td>
                            <td class="border border-slate-300 p-0.5">
                                <input type="text" wire:model.live="payload.sections.{{ $sectionKey }}.{{ $ri }}.start_date" class="h-8 w-full border-0 bg-transparent px-1 text-[11px] focus:ring-1 focus:ring-[#2b579a]">
                            </td>
                            <td class="border border-slate-300 p-0.5">
                                <input type="text" wire:model.live="payload.sections.{{ $sectionKey }}.{{ $ri }}.field_worker" class="h-8 w-full border-0 bg-transparent px-1 text-[11px] focus:ring-1 focus:ring-[#2b579a]">
                            </td>

                            @for ($c = 0; $c < 5; $c++)
                                <td class="border border-slate-300 p-0.5 text-center">
                                    @if ($sectionKey === 'formation')
                                        @include('livewire.partials.audit-checklist-mark-select', [
                                            'wireModel' => 'payload.sections.'.$sectionKey.'.'.$ri.'.checks.'.$c,
                                            'markValue' => data_get($payload, "sections.{$sectionKey}.{$ri}.checks.{$c}", ''),
                                        ])
                                    @endif
                                </td>
                            @endfor

                            @for ($c = 0; $c < 3; $c++)
                                <td class="border border-slate-300 p-0.5 text-center">
                                    @if ($sectionKey === 'closure')
                                        @include('livewire.partials.audit-checklist-mark-select', [
                                            'wireModel' => 'payload.sections.'.$sectionKey.'.'.$ri.'.checks.'.$c,
                                            'markValue' => data_get($payload, "sections.{$sectionKey}.{$ri}.checks.{$c}", ''),
                                        ])
                                    @endif
                                </td>
                            @endfor

                            @for ($c = 0; $c < 4; $c++)
                                <td class="border border-slate-300 p-0.5 text-center">
                                    @if ($sectionKey === 'transfer')
                                        @include('livewire.partials.audit-checklist-mark-select', [
                                            'wireModel' => 'payload.sections.'.$sectionKey.'.'.$ri.'.checks.'.$c,
                                            'markValue' => data_get($payload, "sections.{$sectionKey}.{$ri}.checks.{$c}", ''),
                                        ])
                                    @endif
                                </td>
                            @endfor

                            <td class="border border-slate-300 p-0.5">
                                <input type="text" wire:model.live="payload.sections.{{ $sectionKey }}.{{ $ri }}.wp_ref" class="h-8 w-full border-0 bg-transparent px-1 text-[11px] focus:ring-1 focus:ring-[#2b579a]">
                            </td>
                            <td class="border border-slate-300 p-0.5 text-center">
                                <button type="button" wire:click="removeRow({{ $ri }}, '{{ $sectionKey }}')" class="text-[11px] text-rose-500 hover:text-rose-700">×</button>
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="grid gap-3 border-t border-slate-200 px-4 py-3 lg:grid-cols-3">
        <div class="lg:col-span-3 flex flex-wrap items-center justify-between gap-2">
            <p class="text-[11px] text-slate-500">Fill marks first. AI writes each সারসংক্ষেপ focusing on unusual (✗) points for the report. You can edit the text.</p>
            @if ($aiSummaryReady ?? false)
                <button
                    type="button"
                    wire:click="generateAllSectionSummaries"
                    wire:loading.attr="disabled"
                    wire:target="generateAllSectionSummaries,generateSectionSummary"
                    class="inline-flex h-8 items-center gap-1.5 rounded-md border border-violet-200 bg-violet-50 px-3 text-[11px] font-semibold text-violet-800 hover:bg-violet-100 disabled:opacity-60"
                >
                    <span wire:loading.remove wire:target="generateAllSectionSummaries">AI — সব সারসংক্ষেপ</span>
                    <span wire:loading wire:target="generateAllSectionSummaries">AI লিখছে…</span>
                </button>
            @else
                <span class="text-[11px] text-amber-700">Set OPENAI_API_KEY to enable AI সারসংক্ষেপ.</span>
            @endif
        </div>
        @foreach ($sections as $sectionKey => $section)
            <div class="rounded-lg border border-slate-200 bg-slate-50/60 p-3">
                <p class="mb-2 text-[12px] font-bold text-navy-900">{{ $section['label'] }}</p>
                <ol class="mb-3 list-decimal space-y-1.5 pl-4 text-[11px] leading-snug text-slate-700">
                    @foreach ($section['questions'] as $q)
                        <li>{{ $q }}</li>
                    @endforeach
                </ol>
                <div class="mb-1 flex items-center justify-between gap-2">
                    <label class="block text-[11px] font-bold text-navy-900">সারসংক্ষেপ — {{ $section['label'] }}</label>
                    @if ($aiSummaryReady ?? false)
                        <button
                            type="button"
                            wire:click="generateSectionSummary('{{ $sectionKey }}')"
                            wire:loading.attr="disabled"
                            wire:target="generateSectionSummary('{{ $sectionKey }}'),generateAllSectionSummaries"
                            class="shrink-0 rounded border border-violet-200 bg-white px-2 py-0.5 text-[10px] font-semibold text-violet-700 hover:bg-violet-50 disabled:opacity-60"
                        >
                            <span wire:loading.remove wire:target="generateSectionSummary('{{ $sectionKey }}')">AI লিখুন</span>
                            <span wire:loading wire:target="generateSectionSummary('{{ $sectionKey }}')">…</span>
                        </button>
                    @endif
                </div>
                <textarea
                    wire:model.live="payload.section_summaries.{{ $sectionKey }}"
                    rows="4"
                    class="w-full rounded-md border-slate-200 text-[12px] focus:border-[#2b579a] focus:ring-[#2b579a]"
                    placeholder="এই অংশের সারসংক্ষেপ লিখুন বা AI দিয়ে তৈরি করুন…"
                ></textarea>
                @php
                    $seedKey = \App\Services\ChecklistReportInfluenceService::sectionSummarySeedKey((string) ($formatModel->code ?? 'format-1'), (string) $sectionKey);
                    $alreadyAdded = in_array($seedKey, $addedSummaryKeys ?? [], true);
                @endphp
                @include('livewire.partials.audit-checklist-add-to-report', [
                    'alreadyAdded' => $alreadyAdded,
                    'aiReady' => $aiSummaryReady ?? false,
                    'wireWithoutAi' => "addSectionSummaryToReport('{$sectionKey}', false)",
                    'wireWithAi' => "addSectionSummaryToReport('{$sectionKey}', true)",
                ])
            </div>
        @endforeach
    </div>
</div>
