{{-- How-to guide + annotated sample (shown inside Customize Table popup) --}}
@php
    $bi = (int) ($blockIndex ?? 0);
    $insideEditor = (bool) ($insideEditor ?? false);
@endphp

<div
    class="relative"
    x-data="{ open: false }"
    wire:ignore
>
    <button
        type="button"
        @click="open = ! open"
        :class="open ? 'border-sky-600 bg-sky-100 text-sky-950' : 'border-sky-300 bg-sky-50 text-sky-900 hover:bg-sky-100'"
        class="inline-flex items-center gap-1 rounded border px-2.5 py-1 text-[11px] font-semibold"
        title="কীভাবে টেবিল বানাবেন — উদাহরণসহ"
    >
        <span aria-hidden="true">?</span>
        কীভাবে বানাবেন
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition.opacity.duration.150ms
        @click.outside="open = false"
        class="absolute right-0 top-full mt-1.5 w-[min(92vw,620px)] overflow-hidden rounded-lg border border-sky-200 bg-white shadow-2xl"
        style="display: none; z-index: 10080;"
    >
        <div class="flex items-start justify-between gap-2 border-b border-sky-100 bg-sky-50 px-3 py-2.5">
            <div>
                <p class="text-[12px] font-bold text-sky-950">টেবিল কীভাবে বানাবেন (ধাপে ধাপে)</p>
                <p class="text-[10px] text-sky-900/75">রঙিন নোট = কোথায় ক্লিক · নমুনা ছবি = ফলাফল কেমন দেখাবে</p>
            </div>
            <button type="button" @click="open = false" class="shrink-0 rounded border border-sky-200 bg-white px-2 py-0.5 text-[11px] font-semibold text-sky-800 hover:bg-sky-100">বন্ধ</button>
        </div>

        <ol class="space-y-2 border-b border-slate-100 bg-white px-3 py-3">
            <li class="flex gap-2.5">
                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-violet-600 text-[11px] font-bold text-white">১</span>
                <div class="min-w-0">
                    <p class="text-[11px] font-bold text-slate-900">বামে: সারি / কলাম সংখ্যা → প্রয়োগ</p>
                    <p class="text-[10px] text-slate-600">টপ কলাম ও সারি লিখে <span class="rounded bg-violet-700 px-1 py-0.5 text-[9px] font-semibold text-white">প্রয়োগ</span> চাপুন। এই পপআপের <span class="font-semibold text-violet-700">বাম</span> = সেটিংস, <span class="font-semibold text-emerald-700">ডান</span> = লাইভ প্রিভিউ।</p>
                </div>
            </li>
            <li class="flex gap-2.5">
                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-violet-600 text-[11px] font-bold text-white">২</span>
                <div class="min-w-0">
                    <p class="text-[11px] font-bold text-slate-900">কলাম নাম + সাব-কলাম</p>
                    <p class="text-[10px] text-slate-600">নাম লিখুন। গ্রুপ হেডারের জন্য <span class="rounded border border-violet-300 bg-violet-50 px-1 py-0.5 text-[9px] font-semibold text-violet-800">+ সাব</span> চাপুন (যেমন ভ্যাট → প্রযোজ্য / প্রদানকৃত / কম-বেশি)।</p>
                </div>
            </li>
            <li class="flex gap-2.5">
                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-violet-600 text-[11px] font-bold text-white">৩</span>
                <div class="min-w-0">
                    <p class="text-[11px] font-bold text-slate-900">ডানে সেল ক্লিক → মার্জ</p>
                    <p class="text-[10px] text-slate-600">ডান প্রিভিউতে সেল <span class="font-semibold text-rose-700">ক্লিক</span> করুন → বামে “নিচে মার্জ” সংখ্যা দিন → <span class="rounded bg-rose-600 px-1 py-0.5 text-[9px] font-semibold text-white">মার্জ করুন</span>।</p>
                </div>
            </li>
            <li class="flex gap-2.5">
                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-violet-600 text-[11px] font-bold text-white">৪</span>
                <div class="min-w-0">
                    <p class="text-[11px] font-bold text-slate-900">প্রস্থ (%) ও টেক্সট</p>
                    <p class="text-[10px] text-slate-600">বামে <span class="font-semibold">প্রস্থ %</span> বদলান · ডানে টাইপ করুন · শেষে উপরের <span class="font-semibold">বন্ধ</span>।</p>
                </div>
            </li>
        </ol>

        <div class="bg-slate-50 px-3 py-2">
            <p class="mb-1.5 text-[10px] font-bold uppercase tracking-wide text-slate-500">নমুনা ফলাফল — রঙিন নোট = কী দিয়ে বানানো</p>
            <div class="mb-2 flex flex-wrap gap-1.5 text-[9px]">
                <span class="inline-flex items-center gap-1 rounded-full border border-amber-300 bg-amber-100 px-2 py-0.5 font-semibold text-amber-950">A সাব-কলাম (+ সাব)</span>
                <span class="inline-flex items-center gap-1 rounded-full border border-rose-300 bg-rose-100 px-2 py-0.5 font-semibold text-rose-950">B মার্জ (সেল ক্লিক)</span>
                <span class="inline-flex items-center gap-1 rounded-full border border-emerald-300 bg-emerald-100 px-2 py-0.5 font-semibold text-emerald-950">C প্রস্থ %</span>
            </div>

            <div class="overflow-x-auto rounded border border-slate-300 bg-white p-1.5">
                <table class="w-full border-collapse text-[8px] leading-tight" style="table-layout: fixed;">
                    <colgroup>
                        <col style="width:14%">
                        <col style="width:10%">
                        <col style="width:22%">
                        <col style="width:10%">
                        <col style="width:7.3%">
                        <col style="width:7.3%">
                        <col style="width:9.7%">
                        <col style="width:7.3%">
                        <col style="width:7.3%">
                        <col style="width:9.7%">
                    </colgroup>
                    <thead>
                        <tr>
                            <th class="border border-slate-600 bg-slate-200 px-0.5 py-1 text-center font-bold align-middle" rowspan="2">তারিখ/মাসের নাম</th>
                            <th class="border border-slate-600 bg-slate-200 px-0.5 py-1 text-center font-bold align-middle" rowspan="2">ভাউচার নং</th>
                            <th class="relative border border-slate-600 bg-emerald-100 px-0.5 py-1 text-center font-bold align-middle" rowspan="2">
                                বিবরণ
                                <span class="absolute -top-1.5 left-1/2 -translate-x-1/2 rounded bg-emerald-600 px-1 text-[7px] font-bold text-white">C চওড়া</span>
                            </th>
                            <th class="border border-slate-600 bg-slate-200 px-0.5 py-1 text-center font-bold align-middle" rowspan="2">খরচ</th>
                            <th class="relative border border-amber-500 bg-amber-100 px-0.5 py-1 text-center font-bold" colspan="3">
                                ভ্যাট সংক্রান্ত
                                <span class="absolute -top-1.5 left-1/2 -translate-x-1/2 rounded bg-amber-600 px-1 text-[7px] font-bold text-white">A + সাব ×৩</span>
                            </th>
                            <th class="relative border border-amber-500 bg-amber-100 px-0.5 py-1 text-center font-bold" colspan="3">
                                ট্যাক্স সংক্রান্ত
                                <span class="absolute -top-1.5 left-1/2 -translate-x-1/2 rounded bg-amber-600 px-1 text-[7px] font-bold text-white">A + সাব ×৩</span>
                            </th>
                        </tr>
                        <tr>
                            <th class="border border-amber-400 bg-amber-50 px-0.5 py-0.5 text-center font-bold">প্রযোজ্য</th>
                            <th class="border border-amber-400 bg-amber-50 px-0.5 py-0.5 text-center font-bold">প্রদানকৃত</th>
                            <th class="border border-amber-400 bg-amber-50 px-0.5 py-0.5 text-center font-bold">কম/বেশি</th>
                            <th class="border border-amber-400 bg-amber-50 px-0.5 py-0.5 text-center font-bold">প্রযোজ্য</th>
                            <th class="border border-amber-400 bg-amber-50 px-0.5 py-0.5 text-center font-bold">প্রদানকৃত</th>
                            <th class="border border-amber-400 bg-amber-50 px-0.5 py-0.5 text-center font-bold">কম/বেশি</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $sampleRows = ['স্টেশনারী', 'ইন্টারনেট বিল', 'অফিস ভাড়া', '…'];
                            $n = count($sampleRows);
                        @endphp
                        @foreach ($sampleRows as $i => $desc)
                            <tr>
                                @if ($i === 0)
                                    <td class="relative border border-rose-500 bg-rose-50 px-0.5 py-0.5 text-center align-middle font-semibold" rowspan="{{ $n }}">
                                        <span class="absolute left-0.5 top-0.5 rounded bg-rose-600 px-0.5 text-[7px] font-bold text-white">B</span>
                                        জুলাই–জুন<br><span class="text-[7px] font-normal text-rose-800">↓ মার্জ {{ $n }} সারি</span>
                                    </td>
                                    <td class="relative border border-rose-500 bg-rose-50 px-0.5 py-0.5 text-center align-middle font-semibold" rowspan="{{ $n }}">
                                        <span class="absolute left-0.5 top-0.5 rounded bg-rose-600 px-0.5 text-[7px] font-bold text-white">B</span>
                                        সংশ্লিষ্ট সকল
                                    </td>
                                @endif
                                <td class="border border-slate-600 px-1 py-0.5 text-left">{{ $desc }}</td>
                                <td class="border border-slate-600 px-0.5 py-0.5 text-center text-slate-400">—</td>
                                <td class="border border-slate-600 px-0.5 py-0.5 text-center text-slate-400">—</td>
                                <td class="border border-slate-600 px-0.5 py-0.5 text-center text-slate-400">—</td>
                                <td class="border border-slate-600 px-0.5 py-0.5 text-center text-slate-400">—</td>
                                <td class="border border-slate-600 px-0.5 py-0.5 text-center text-slate-400">—</td>
                                <td class="border border-slate-600 px-0.5 py-0.5 text-center text-slate-400">—</td>
                                <td class="border border-slate-600 px-0.5 py-0.5 text-center text-slate-400">—</td>
                            </tr>
                        @endforeach
                        <tr class="font-bold">
                            <td class="border border-slate-600 bg-slate-50 px-1 py-0.5 text-center" colspan="3">মোট</td>
                            @for ($i = 0; $i < 7; $i++)
                                <td class="border border-slate-600 bg-slate-50 px-0.5 py-0.5 text-center text-slate-400">—</td>
                            @endfor
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 border-t border-sky-100 bg-white px-3 py-2.5">
            <button
                type="button"
                class="rounded bg-violet-600 px-3 py-1.5 text-[11px] font-semibold text-white hover:bg-violet-700"
                @click="open = false; $wire.applyCustomTableTemplate({{ $bi }}, 'expense')"
            >এই নমুনা কাঠামো লোড করুন</button>
            <p class="text-[9px] text-slate-500">লোড করলে মার্জ ও সাব-কলাম আগে থেকে থাকবে — তারপর বাম/ডানে বদলান।</p>
        </div>
    </div>
</div>
