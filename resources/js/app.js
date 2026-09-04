import './bootstrap';

// Alpine is provided/started by Livewire (@livewireScripts in the layout).
// Do not call Alpine.start() here — a second start breaks Livewire pages.

(() => {
    const DELAY_MS = 180;
    let pending = 0;
    let showTimer = null;

    const el = () => document.getElementById('app-loader');

    const setActive = (on) => {
        const node = el();
        if (!node) return;
        node.classList.toggle('is-active', on);
        node.setAttribute('aria-busy', on ? 'true' : 'false');
        node.setAttribute('aria-hidden', on ? 'false' : 'true');
    };

    const start = () => {
        pending += 1;
        if (pending === 1) {
            showTimer = window.setTimeout(() => {
                if (pending > 0) setActive(true);
            }, DELAY_MS);
        }
    };

    const stop = () => {
        pending = Math.max(0, pending - 1);
        if (pending === 0) {
            if (showTimer) {
                clearTimeout(showTimer);
                showTimer = null;
            }
            setActive(false);
        }
    };

    // Full page navigation / form submit
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
        setActive(false);
    });

    // Livewire requests (skip ultra-fast ones via DELAY_MS;
    // skip entirely while Custom Table editor is open — avoid loader flash)
    document.addEventListener('livewire:init', () => {
        Livewire.hook('request', ({ respond, succeed, fail }) => {
            if (document.body?.dataset?.ctEditor === '1') {
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
