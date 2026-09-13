import './bootstrap';

// Alpine is provided/started by Livewire (@livewireScripts in the layout).
// Do not call Alpine.start() here — a second start breaks Livewire pages.

/** Bangladesh Standard Time helpers for the whole frontend (Asia/Dhaka). */
(() => {
    const ZONE = document.querySelector('meta[name="bynnas-timezone"]')?.getAttribute('content') || 'Asia/Dhaka';
    const metaToday = document.querySelector('meta[name="bynnas-today"]')?.getAttribute('content') || '';

    const partsFor = (date = new Date()) => {
        const parts = new Intl.DateTimeFormat('en-GB', {
            timeZone: ZONE,
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false,
        }).formatToParts(date);

        const get = (type) => parts.find((p) => p.type === type)?.value || '00';
        return {
            year: Number(get('year')),
            month: Number(get('month')),
            day: Number(get('day')),
            hour: Number(get('hour')),
            minute: Number(get('minute')),
            second: Number(get('second')),
        };
    };

    const pad = (n) => String(n).padStart(2, '0');

    const todayYmd = () => {
        if (metaToday && /^\d{4}-\d{2}-\d{2}$/.test(metaToday)) {
            return metaToday;
        }
        const p = partsFor(new Date());
        return `${p.year}-${pad(p.month)}-${pad(p.day)}`;
    };

    const formatDateTime = (value, options = {}) => {
        const date = value instanceof Date ? value : new Date(value);
        if (Number.isNaN(date.getTime())) return '—';
        return new Intl.DateTimeFormat('en-GB', {
            timeZone: ZONE,
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
            hour12: true,
            ...options,
        }).format(date);
    };

    window.bynnasTime = {
        zone: ZONE,
        parts: partsFor,
        todayYmd,
        nowParts: () => partsFor(new Date()),
        formatDateTime,
        /** Local calendar Date representing "now" in BD (for date pickers). */
        nowLocalDate() {
            const p = partsFor(new Date());
            return new Date(p.year, p.month - 1, p.day, p.hour, p.minute, p.second);
        },
    };
})();

/** Keep Laravel CSRF meta + form tokens fresh (avoids intermittent 419 Page Expired). */
(() => {
    const META = 'meta[name="csrf-token"]';
    let refreshing = null;
    let lastRefreshAt = 0;

    const applyToken = (token) => {
        if (!token || typeof token !== 'string') return;
        const meta = document.querySelector(META);
        if (meta) meta.setAttribute('content', token);
        document.querySelectorAll('input[name="_token"]').forEach((input) => {
            input.value = token;
        });
        window.__bynnasCsrf = token;
        lastRefreshAt = Date.now();
    };

    const currentToken = () =>
        document.querySelector(META)?.getAttribute('content')
        || window.__bynnasCsrf
        || '';

    const refreshCsrf = async ({ force = false } = {}) => {
        if (!force && Date.now() - lastRefreshAt < 30_000 && currentToken()) {
            applyToken(currentToken());
            return currentToken();
        }
        if (refreshing) return refreshing;

        refreshing = fetch('/csrf-token', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then(async (res) => {
                if (!res.ok) return currentToken();
                const data = await res.json().catch(() => ({}));
                if (data?.token) applyToken(data.token);
                return data?.token || currentToken();
            })
            .catch(() => currentToken())
            .finally(() => {
                refreshing = null;
            });

        return refreshing;
    };

    window.bynnasCsrf = {
        token: currentToken,
        apply: applyToken,
        refresh: refreshCsrf,
    };

    document.addEventListener('submit', (e) => {
        const form = e.target;
        if (!(form instanceof HTMLFormElement)) return;
        if (form.hasAttribute('wire:submit')) return;

        const token = currentToken();
        const input = form.querySelector('input[name="_token"]');
        if (token && input) input.value = token;
    }, true);

    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            refreshCsrf({ force: true });
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        applyToken(currentToken());
        lastRefreshAt = Date.now();
        refreshCsrf({ force: false });
    });

    // Every 10 minutes while the tab is open.
    window.setInterval(() => {
        if (document.visibilityState === 'visible') {
            refreshCsrf({ force: true });
        }
    }, 10 * 60 * 1000);

    document.addEventListener('livewire:init', () => {
        Livewire.hook('commit', ({ succeed }) => {
            succeed(() => {
                applyToken(currentToken());
            });
        });
    });
})();

(() => {
    const DELAY_MS = 280;
    const MAX_MS = 12000;
    let pending = 0;
    let showTimer = null;
    let safetyTimer = null;

    const el = () => document.getElementById('app-loader');

    const setActive = (on) => {
        const node = el();
        if (!node) return;
        node.classList.toggle('is-active', on);
        node.setAttribute('aria-busy', on ? 'true' : 'false');
        node.setAttribute('aria-hidden', on ? 'false' : 'true');
    };

    const clearSafety = () => {
        if (safetyTimer) {
            clearTimeout(safetyTimer);
            safetyTimer = null;
        }
    };

    const start = () => {
        pending += 1;
        if (pending === 1) {
            showTimer = window.setTimeout(() => {
                if (pending > 0) setActive(true);
            }, DELAY_MS);
            clearSafety();
            safetyTimer = window.setTimeout(() => {
                pending = 0;
                if (showTimer) {
                    clearTimeout(showTimer);
                    showTimer = null;
                }
                setActive(false);
            }, MAX_MS);
        }
    };

    const stop = () => {
        pending = Math.max(0, pending - 1);
        if (pending === 0) {
            if (showTimer) {
                clearTimeout(showTimer);
                showTimer = null;
            }
            clearSafety();
            setActive(false);
        }
    };

    const isQuietCommit = (commit) => {
        if (!commit || typeof commit !== 'object') return false;
        const calls = Array.isArray(commit.calls) ? commit.calls : [];
        const methods = calls.map((c) => c?.method).filter(Boolean);
        const updates = commit.updates && typeof commit.updates === 'object'
            ? Object.keys(commit.updates)
            : [];

        // Background poll / silent persist — never flash the top bar.
        if (methods.length === 1 && (methods[0] === 'autoSaveDraft' || methods[0] === 'refreshUndoWindow')) {
            return true;
        }
        if (methods.length > 0 && methods.every((m) => m === 'autoSaveDraft' || m === 'refreshUndoWindow')) {
            return true;
        }

        // Typing / wire:model.live — updates only, no method calls.
        if (methods.length === 0 && updates.length > 0) {
            return true;
        }

        return false;
    };

    document.addEventListener('click', (e) => {
        const a = e.target.closest('a[href]');
        if (!a) return;
        if (e.defaultPrevented || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        if (a.target && a.target !== '_self') return;
        if (a.hasAttribute('download')) return;
        const href = a.getAttribute('href') || '';
        if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;
        try {
            const url = new URL(a.href, window.location.origin);
            if (url.origin !== window.location.origin) return;
        } catch {
            return;
        }
        start();
    }, true);

    document.addEventListener('submit', (e) => {
        const form = e.target;
        if (!(form instanceof HTMLFormElement)) return;
        if (form.hasAttribute('wire:submit') || form.closest('[wire\\:id]')) return;
        start();
    }, true);

    window.addEventListener('pageshow', () => {
        pending = 0;
        if (showTimer) clearTimeout(showTimer);
        clearSafety();
        setActive(false);
    });

    // Only remove known orphaned insert-menu overlays (z-index 10040). Never touch layout roots.
    const scrubOrphanOverlays = () => {
        try {
            delete document.body.dataset.ctEditor;
        } catch (e) {}
        document.querySelectorAll('body > div').forEach((node) => {
            if (node.id === 'app-loader') return;
            const style = `${node.getAttribute('style') || ''} ${node.firstElementChild?.getAttribute?.('style') || ''}`;
            if (style.includes('z-index:10040') || style.includes('z-index: 10040')) {
                node.remove();
            }
        });
    };

    document.addEventListener('DOMContentLoaded', scrubOrphanOverlays);
    setTimeout(scrubOrphanOverlays, 50);

    document.addEventListener('livewire:init', () => {
        // Prefer commit hook: cancels call fail(), so the bar cannot stick forever.
        // Skip autosave poll + live model sync so the page does not "always load".
        Livewire.hook('commit', ({ commit, respond, succeed, fail }) => {
            if (document.body?.dataset?.ctEditor === '1') {
                return;
            }
            if (isQuietCommit(commit)) {
                return;
            }

            start();
            let settled = false;
            const done = () => {
                if (settled) return;
                settled = true;
                stop();
            };
            respond(done);
            succeed(done);
            fail(done);
        });
    });
})();

/** Auto-expand report textareas (observation / criteria / etc.) to fit full paragraph. */
(() => {
    const grow = (el) => {
        if (!(el instanceof HTMLTextAreaElement) || !el.classList.contains('audit-autogrow')) {
            return;
        }
        el.style.overflowY = 'hidden';
        el.style.resize = 'none';
        el.style.height = 'auto';
        el.style.height = `${Math.max(el.scrollHeight, 48)}px`;
    };

    const growAll = (root = document) => {
        root.querySelectorAll?.('textarea.audit-autogrow')?.forEach(grow);
    };

    document.addEventListener('input', (e) => {
        const t = e.target;
        if (t instanceof HTMLTextAreaElement && t.classList.contains('audit-autogrow')) {
            grow(t);
        }
    }, true);

    document.addEventListener('focusin', (e) => {
        const t = e.target;
        if (t instanceof HTMLTextAreaElement && t.classList.contains('audit-autogrow')) {
            grow(t);
        }
    }, true);

    const schedule = () => {
        requestAnimationFrame(() => growAll());
    };

    document.addEventListener('DOMContentLoaded', schedule);
    document.addEventListener('livewire:navigated', schedule);
    document.addEventListener('livewire:init', () => {
        schedule();
        Livewire.hook('morph.updated', ({ el }) => {
            if (el instanceof HTMLTextAreaElement) {
                grow(el);
            } else if (el?.querySelectorAll) {
                growAll(el);
            }
        });
        Livewire.hook('commit', ({ succeed }) => {
            succeed(() => schedule());
        });
    });

    // Catch late Livewire/Alpine paints after checklist insert.
    setTimeout(schedule, 100);
    setTimeout(schedule, 500);
})();
