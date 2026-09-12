{{-- Global styled confirm. Prefer this over window.confirm().
     JS: await window.bynnasConfirm({ title, message, okLabel, cancelLabel, tone })
     Forms: <form data-bynnas-confirm="Message…" data-bynnas-confirm-title="Title" data-bynnas-confirm-ok="OK" data-bynnas-confirm-tone="emerald|rose|amber|sky">
--}}
<div
    id="bynnas-nice-confirm"
    x-data="bynnasNiceConfirm()"
    x-cloak
    x-show="open"
    class="fixed inset-0 z-[100] flex items-center justify-center p-4"
    @keydown.escape.window="cancel()"
    role="dialog"
    aria-modal="true"
    :aria-hidden="(! open).toString()"
>
    <div
        class="absolute inset-0 bg-slate-900/35 backdrop-blur-[2px]"
        @click="cancel()"
        x-show="open"
        x-transition.opacity.duration.150ms
    ></div>
    <div
        class="relative w-full max-w-[300px] overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xl shadow-slate-900/10"
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.stop
    >
        <div class="mb-3 flex items-start gap-2.5">
            <span
                class="mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-[14px]"
                :class="{
                    'bg-rose-50 text-rose-600': tone === 'rose',
                    'bg-amber-50 text-amber-700': tone === 'amber',
                    'bg-sky-50 text-sky-700': tone === 'sky',
                    'bg-emerald-50 text-emerald-700': tone !== 'rose' && tone !== 'amber' && tone !== 'sky',
                }"
                x-text="tone === 'rose' ? '✕' : (tone === 'amber' ? '!' : '✓')"
            ></span>
            <div class="min-w-0 pt-0.5">
                <p class="text-[13px] font-semibold tracking-tight text-navy-900" x-text="title"></p>
                <p class="mt-1 text-[12px] leading-relaxed text-slate-500" x-text="message"></p>
            </div>
        </div>
        <div class="flex items-center justify-end gap-1.5">
            <button
                type="button"
                class="inline-flex h-8 items-center rounded-lg px-2.5 text-[12px] font-medium text-slate-600 hover:bg-slate-50"
                @click="cancel()"
                x-text="cancelLabel"
            ></button>
            <button
                type="button"
                class="inline-flex h-8 items-center rounded-lg px-3 text-[12px] font-semibold text-white shadow-sm"
                :class="{
                    'bg-rose-600 hover:bg-rose-700': tone === 'rose',
                    'bg-amber-600 hover:bg-amber-700': tone === 'amber',
                    'bg-[#2b579a] hover:bg-[#204072]': tone === 'sky',
                    'bg-emerald-700 hover:bg-emerald-800': tone !== 'rose' && tone !== 'amber' && tone !== 'sky',
                }"
                @click="accept()"
                x-text="okLabel"
            ></button>
        </div>
    </div>
</div>

<script>
    (function () {
        if (window.bynnasConfirm) return;

        let resolver = null;

        window.__bynnasConfirmResolve = function (ok) {
            if (typeof resolver === 'function') {
                const r = resolver;
                resolver = null;
                r(!!ok);
            }
        };

        window.bynnasConfirm = function (opts = {}) {
            return new Promise((resolve) => {
                resolver = resolve;
                window.dispatchEvent(new CustomEvent('bynnas-confirm-open', {
                    detail: {
                        title: opts.title || 'Are you sure?',
                        message: opts.message || '',
                        okLabel: opts.okLabel || 'OK',
                        cancelLabel: opts.cancelLabel || 'Cancel',
                        tone: opts.tone || 'emerald',
                    },
                }));
            });
        };

        document.addEventListener('submit', function (e) {
            const form = e.target;
            if (!(form instanceof HTMLFormElement)) return;
            if (!form.hasAttribute('data-bynnas-confirm')) return;
            if (form.dataset.bynnasConfirmBypass === '1') {
                delete form.dataset.bynnasConfirmBypass;
                return;
            }

            e.preventDefault();
            e.stopImmediatePropagation();

            window.bynnasConfirm({
                title: form.dataset.bynnasConfirmTitle || 'Are you sure?',
                message: form.dataset.bynnasConfirmMessage || form.getAttribute('data-bynnas-confirm') || '',
                okLabel: form.dataset.bynnasConfirmOk || 'OK',
                cancelLabel: form.dataset.bynnasConfirmCancel || 'Cancel',
                tone: form.dataset.bynnasConfirmTone || 'emerald',
            }).then(function (ok) {
                if (!ok) return;
                form.dataset.bynnasConfirmBypass = '1';
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {
                    form.submit();
                }
            });
        }, true);
    })();

    function bynnasNiceConfirm() {
        return {
            open: false,
            title: 'Are you sure?',
            message: '',
            okLabel: 'OK',
            cancelLabel: 'Cancel',
            tone: 'emerald',
            init() {
                window.addEventListener('bynnas-confirm-open', (e) => {
                    const d = e.detail || {};
                    this.title = d.title || 'Are you sure?';
                    this.message = d.message || '';
                    this.okLabel = d.okLabel || 'OK';
                    this.cancelLabel = d.cancelLabel || 'Cancel';
                    this.tone = d.tone || 'emerald';
                    this.open = true;
                });
            },
            accept() {
                this.open = false;
                window.__bynnasConfirmResolve?.(true);
            },
            cancel() {
                if (!this.open) return;
                this.open = false;
                window.__bynnasConfirmResolve?.(false);
            },
        };
    }
</script>
