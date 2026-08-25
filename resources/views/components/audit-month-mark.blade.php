@props([
    'active' => false,
    'manual' => false,
    'editable' => false,
    'category' => null,
    'schedulableType' => null,
    'schedulableId' => null,
    'monthIndex' => null,
    'tab' => null,
    'fy' => null,
])

@if ($editable)
    <button
        type="button"
        x-data="{
            active: {{ $active ? 'true' : 'false' }},
            busy: false,
            url: @js(route('annual-audit.toggle-month')),
            payload: {
                category: @js($category),
                schedulable_type: @js($schedulableType),
                schedulable_id: @js($schedulableId),
                month_index: @js($monthIndex),
                tab: @js($tab),
                fy: @js($fy),
            },
            async toggle() {
                if (this.busy) return;
                this.busy = true;
                const previous = this.active;
                this.active = !this.active;
                this.$dispatch('audit-tick', { delta: this.active ? 1 : -1, month_index: this.payload.month_index });
                try {
                    const token = document.querySelector('meta[name=csrf-token]')?.getAttribute('content');
                    const response = await fetch(this.url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': token,
                        },
                        body: JSON.stringify(this.payload),
                    });
                    if (! response.ok) throw new Error('toggle failed');
                    const data = await response.json();
                    if (Boolean(data.active) !== this.active) {
                        this.$dispatch('audit-tick', { delta: data.active ? 1 : -1, month_index: this.payload.month_index });
                        this.active = Boolean(data.active);
                    }
                } catch (e) {
                    this.active = previous;
                    this.$dispatch('audit-tick', { delta: previous ? 1 : -1, month_index: this.payload.month_index });
                } finally {
                    this.busy = false;
                }
            },
        }"
        @click="toggle()"
        :disabled="busy"
        :title="active ? 'Remove audit this month' : 'Schedule audit this month'"
        :class="active ? 'bg-emerald-50 hover:bg-emerald-100' : 'hover:bg-slate-50'"
        class="inline-flex h-7 w-7 items-center justify-center rounded-md transition disabled:opacity-50"
    >
        <svg x-show="active" x-cloak class="h-4 w-4 text-emerald-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
        </svg>
        <span x-show="!active" class="h-1.5 w-1.5 rounded-full bg-slate-200"></span>
    </button>
@else
    <span class="inline-flex h-7 w-7 items-center justify-center">
        @if ($active)
            <svg class="h-4 w-4 text-emerald-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
            </svg>
        @else
            <span class="h-1.5 w-1.5 rounded-full bg-slate-200"></span>
        @endif
    </span>
@endif
