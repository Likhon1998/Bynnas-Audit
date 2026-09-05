import './bootstrap';

// Alpine is provided/started by Livewire (@livewireScripts in the layout).
// Do not call Alpine.start() here — a second start breaks Livewire pages.

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
        if (methods.length === 1 && methods[0] === 'autoSaveDraft') {
            return true;
        }
        if (methods.length > 0 && methods.every((m) => m === 'autoSaveDraft')) {
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
