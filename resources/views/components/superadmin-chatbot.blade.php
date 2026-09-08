<div
    x-data="{
        open: false,
        loading: false,
        loaded: false,
        input: '',
        error: '',
        threadUuid: '',
        messages: [],
        suggestions: [
            'Give me a summary of current audit activities',
            'How many audit reports were completed this month?',
            'Which Shakhas currently require attention?',
            'Show the employee list for Mirpur Shakha',
        ],
        createUuid() {
            if (globalThis.crypto?.randomUUID) return globalThis.crypto.randomUUID();
            return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (character) => {
                const random = Math.floor(Math.random() * 16);
                const value = character === 'x' ? random : (random & 0x3) | 0x8;
                return value.toString(16);
            });
        },
        init() {
            try {
                this.threadUuid = localStorage.getItem('bynnasSuperAdminChatThread') || '';
                if (!this.threadUuid) {
                    this.threadUuid = this.createUuid();
                    localStorage.setItem('bynnasSuperAdminChatThread', this.threadUuid);
                }
            } catch (e) {
                this.threadUuid = this.createUuid();
            }
        },
        async openChat() {
            this.open = true;
            if (!this.loaded) await this.loadHistory();
            this.$nextTick(() => {
                this.$refs.chatInput?.focus();
                this.scrollBottom();
            });
        },
        async loadHistory() {
            this.error = '';
            try {
                const response = await fetch(`{{ route('superadmin.chat.history') }}?thread_uuid=${encodeURIComponent(this.threadUuid)}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });
                if (!response.ok) throw new Error('Could not load previous messages.');
                const data = await response.json();
                this.messages = (data.messages || []).flatMap((row) => [
                    { role: 'user', text: row.question, failed: false },
                    { role: 'assistant', text: row.answer || 'No answer was recorded.', failed: row.status === 'failed' },
                ]);
                this.loaded = true;
            } catch (error) {
                this.loaded = false;
                this.error = error.message || 'Could not load chat history.';
            }
        },
        useSuggestion(text) {
            this.input = text;
            this.$nextTick(() => this.$refs.chatInput?.focus());
        },
        startNewConversation() {
            if (this.loading) return;

            this.threadUuid = this.createUuid();
            this.messages = [];
            this.input = '';
            this.error = '';
            this.loaded = true;

            try {
                localStorage.setItem('bynnasSuperAdminChatThread', this.threadUuid);
            } catch (e) {
                // The new in-memory conversation still works when storage is unavailable.
            }

            this.$nextTick(() => this.$refs.chatInput?.focus());
        },
        async send() {
            const message = this.input.trim();
            if (message.length < 2 || this.loading) return;
            this.input = '';
            this.error = '';
            this.messages.push({ role: 'user', text: message, failed: false });
            this.loading = true;
            this.$nextTick(() => this.scrollBottom());

            try {
                const response = await fetch(`{{ route('superadmin.chat.ask') }}`, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    },
                    body: JSON.stringify({ thread_uuid: this.threadUuid, message }),
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) throw new Error(data.message || 'The assistant could not answer right now.');
                this.messages.push({ role: 'assistant', text: data.answer, failed: false });
            } catch (error) {
                const text = error.message || 'The assistant could not answer right now.';
                this.messages.push({ role: 'assistant', text, failed: true });
                this.error = text;
            } finally {
                this.loading = false;
                this.$nextTick(() => {
                    this.scrollBottom();
                    this.$refs.chatInput?.focus();
                });
            }
        },
        scrollBottom() {
            if (this.$refs.chatMessages) {
                this.$refs.chatMessages.scrollTop = this.$refs.chatMessages.scrollHeight;
            }
        },
        formatMessage(value) {
            const holder = document.createElement('div');
            holder.textContent = String(value || '');
            const escaped = holder.innerHTML;
            const inline = (text) => text
                .replace(/\*\*(.+?)\*\*/g, '<strong class=&quot;font-semibold text-slate-900&quot;>$1</strong>')
                .replace(/`([^`]+)`/g, '<code class=&quot;rounded bg-slate-100 px-1 py-0.5 text-[10px] text-slate-700&quot;>$1</code>');
            const lines = escaped.split(/\r?\n/);
            let html = '';
            let listType = null;

            lines.forEach((line) => {
                const bullet = line.match(/^\s*[-*]\s+(.+)$/);
                const numbered = line.match(/^\s*\d+[.)]\s+(.+)$/);
                if (bullet || numbered) {
                    const nextListType = numbered ? 'ol' : 'ul';
                    if (listType !== nextListType) {
                        if (listType) html += `</${listType}>`;
                        const style = nextListType === 'ol' ? 'list-decimal' : 'list-disc';
                        html += `<${nextListType} class=&quot;my-1.5 ${style} space-y-1 pl-4&quot;>`;
                        listType = nextListType;
                    }
                    html += `<li>${inline((bullet || numbered)[1])}</li>`;
                    return;
                }
                if (listType) {
                    html += `</${listType}>`;
                    listType = null;
                }
                const heading = line.match(/^#{1,4}\s+(.+)$/);
                if (heading) {
                    html += `<p class=&quot;mb-1.5 font-semibold text-slate-900&quot;>${inline(heading[1])}</p>`;
                    return;
                }
                const clean = line.replace(/^#{1,4}\s+/, '');
                if (clean.trim() !== '') {
                    html += `<p class=&quot;mb-1.5 last:mb-0&quot;>${inline(clean)}</p>`;
                }
            });
            if (listType) html += `</${listType}>`;

            return html || '<p>No response content.</p>';
        },
    }"
    @keydown.escape.window="open = false"
    class="fixed bottom-4 right-4 z-50 sm:bottom-6 sm:right-6"
>
    <div
        x-show="open"
        x-cloak
        x-transition.origin.bottom.right
        class="mb-3 flex h-[min(620px,calc(100vh-7rem))] w-[calc(100vw-2rem)] max-w-[390px] flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-[0_24px_70px_rgba(15,23,42,0.25)]"
        role="dialog"
        aria-label="Super Admin database assistant"
    >
        <div class="relative overflow-hidden bg-gradient-to-r from-[#0b2447] via-[#123d70] to-[#176b87] px-4 py-3.5 text-white">
            <span class="pointer-events-none absolute -right-8 -top-12 h-28 w-28 rounded-full bg-cyan-300/15 blur-xl"></span>
            <div class="relative flex items-center gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white/15 ring-1 ring-white/20">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-4 4v-4z"/></svg>
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-[13px] font-semibold">Bynnas Audit Assistant</p>
                    <p class="mt-0.5 flex items-center gap-1.5 text-[10px] text-cyan-100">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 ring-2 ring-emerald-300/20"></span>
                        Secure audit assistance
                    </p>
                </div>
                <button
                    type="button"
                    @click="startNewConversation()"
                    :disabled="loading"
                    class="flex h-7 w-7 items-center justify-center rounded-lg text-white/70 transition hover:bg-white/10 hover:text-white disabled:cursor-not-allowed disabled:opacity-40"
                    aria-label="Start a new conversation"
                    title="Start a new conversation"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 11a8.1 8.1 0 00-15.5-2M4 4v5h5m-5 4a8.1 8.1 0 0015.5 2M20 20v-5h-5"/>
                    </svg>
                </button>
                <button type="button" @click="open = false" class="flex h-7 w-7 items-center justify-center rounded-lg text-white/70 hover:bg-white/10 hover:text-white" aria-label="Close chatbot">×</button>
            </div>
        </div>

        <div x-ref="chatMessages" class="min-h-0 flex-1 space-y-3 overflow-y-auto bg-gradient-to-b from-slate-50 to-white px-3.5 py-4">
            <div x-show="error && !loaded" x-cloak class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2.5 text-[10px] text-rose-700">
                <p x-text="error"></p>
                <button type="button" @click="loadHistory()" class="mt-1 font-semibold underline underline-offset-2">Try again</button>
            </div>
            <div x-show="messages.length === 0 && !loading" class="py-2 text-center">
                <span class="mx-auto flex h-11 w-11 items-center justify-center rounded-2xl bg-cyan-50 text-cyan-700 ring-1 ring-cyan-100">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3a.75.75 0 01.75.75V5h3V3.75a.75.75 0 011.5 0V5h1.25A2.75 2.75 0 0119 7.75v8.5A2.75 2.75 0 0116.25 19h-8.5A2.75 2.75 0 015 16.25v-8.5A2.75 2.75 0 017.75 5H9V3.75A.75.75 0 019.75 3z"/><path stroke-linecap="round" d="M9 11h.01M15 11h.01M9 15h6"/></svg>
                </span>
                <p class="mt-3 text-[13px] font-semibold text-navy-900">How can I assist you?</p>
                <p class="mx-auto mt-1 max-w-[280px] text-[10px] leading-relaxed text-slate-500">I understand Bangla, English, mixed language, and common spelling mistakes.</p>
                <div class="mt-4 grid gap-1.5 text-left">
                    <template x-for="suggestion in suggestions" :key="suggestion">
                        <button type="button" @click="useSuggestion(suggestion)" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-left text-[10px] font-medium text-slate-600 shadow-sm hover:border-cyan-200 hover:bg-cyan-50/50 hover:text-cyan-800" x-text="suggestion"></button>
                    </template>
                </div>
            </div>

            <template x-for="(message, index) in messages" :key="index">
                <div :class="message.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div
                        :class="message.role === 'user'
                            ? 'max-w-[86%] rounded-2xl rounded-br-md bg-gradient-to-r from-[#164e63] to-[#0369a1] text-white'
                            : message.failed
                                ? 'max-w-[90%] rounded-2xl rounded-bl-md border border-rose-200 bg-rose-50 text-rose-800'
                                : 'max-w-[90%] rounded-2xl rounded-bl-md border border-slate-200 bg-white text-slate-700 shadow-sm'"
                        class="px-3 py-2.5 text-[11px] leading-relaxed"
                    >
                        <template x-if="message.role === 'user'">
                            <span class="whitespace-pre-wrap" x-text="message.text"></span>
                        </template>
                        <template x-if="message.role === 'assistant'">
                            <div class="break-words" x-html="formatMessage(message.text)"></div>
                        </template>
                    </div>
                </div>
            </template>

            <div x-show="loading" class="flex justify-start">
                <div class="flex items-center gap-1 rounded-2xl rounded-bl-md border border-slate-200 bg-white px-3 py-3 shadow-sm">
                    <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-cyan-500 [animation-delay:-0.3s]"></span>
                    <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-cyan-500 [animation-delay:-0.15s]"></span>
                    <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-cyan-500"></span>
                </div>
            </div>
        </div>

        <form @submit.prevent="send()" class="border-t border-slate-200 bg-white p-3">
            <div class="flex items-end gap-2 rounded-xl border border-slate-200 bg-slate-50 p-1.5 focus-within:border-cyan-400 focus-within:ring-2 focus-within:ring-cyan-100">
                <textarea
                    x-ref="chatInput"
                    x-model="input"
                    @keydown.enter.exact.prevent="send()"
                    rows="1"
                    maxlength="1500"
                    placeholder="Ask about audits, reports, risks, or employees..."
                    class="max-h-28 min-h-[34px] flex-1 resize-none border-0 bg-transparent px-2 py-2 text-[11px] leading-relaxed text-slate-700 placeholder:text-slate-400 focus:ring-0"
                ></textarea>
                <button
                    type="submit"
                    :disabled="loading || input.trim().length < 2"
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gradient-to-r from-cyan-600 to-blue-700 text-white shadow-sm hover:from-cyan-700 hover:to-blue-800 disabled:cursor-not-allowed disabled:opacity-40"
                    aria-label="Send message"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                </button>
            </div>
            <p class="mt-1.5 text-center text-[9px] text-slate-400">Super Admin only · Secure assistance · Conversation audited</p>
        </form>
    </div>

    <button
        x-show="!open"
        x-transition
        type="button"
        @click="openChat()"
        class="group relative flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-[#0e7490] via-[#0369a1] to-[#1e3a8a] text-white shadow-[0_12px_32px_rgba(3,105,161,0.38)] ring-1 ring-white/20 transition hover:-translate-y-0.5 hover:shadow-[0_16px_38px_rgba(3,105,161,0.48)]"
        aria-label="Open Bynnas Audit Assistant"
        title="Ask Bynnas Audit Assistant"
    >
        <span class="absolute -right-0.5 -top-0.5 h-3 w-3 rounded-full border-2 border-white bg-emerald-400"></span>
        <svg class="h-6 w-6 transition group-hover:scale-105" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a10.9 10.9 0 01-4-.75L3 20l1.25-3.5A7.23 7.23 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
    </button>
</div>
