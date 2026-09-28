{{-- Any element with data-flash fades out after 5 s (hover pauses it). Optional data-flash="8000" sets a custom delay. --}}
<script>
    (function () {
        if (window.__bynnasFlash) return;
        window.__bynnasFlash = true;

        const DEFAULT_DELAY = 5000;

        function hide(el) {
            const style = el.style;
            style.maxHeight = el.scrollHeight + 'px';
            style.overflow = 'hidden';
            void el.offsetHeight;
            style.transition = 'opacity .35s ease, transform .35s ease, max-height .35s ease .1s, margin .35s ease .1s, padding .35s ease .1s, border-width .35s ease .1s';
            style.opacity = '0';
            style.transform = 'translateY(-4px)';
            style.maxHeight = '0';
            style.marginTop = '0';
            style.marginBottom = '0';
            style.paddingTop = '0';
            style.paddingBottom = '0';
            style.borderTopWidth = '0';
            style.borderBottomWidth = '0';
            setTimeout(() => el.remove(), 500);
        }

        function arm(el) {
            if (el.__flashArmed) return;
            el.__flashArmed = true;

            const delay = parseInt(el.getAttribute('data-flash'), 10) || DEFAULT_DELAY;
            if (getComputedStyle(el).position === 'static') el.style.position = 'relative';
            if (getComputedStyle(el).overflow === 'visible') el.style.overflow = 'hidden';

            const bar = document.createElement('span');
            bar.setAttribute('aria-hidden', 'true');
            bar.style.cssText = 'position:absolute;left:0;bottom:0;height:2px;width:100%;background:currentColor;opacity:.3;transform-origin:left;pointer-events:none;';
            el.appendChild(bar);

            const countdown = bar.animate ? bar.animate([{ transform: 'scaleX(1)' }, { transform: 'scaleX(0)' }], { duration: delay, fill: 'forwards' }) : null;
            let remaining = delay;
            let startedAt = 0;
            let timer = null;
            const run = () => {
                startedAt = Date.now();
                timer = setTimeout(() => hide(el), remaining);
                countdown?.play();
            };
            el.addEventListener('mouseenter', () => {
                clearTimeout(timer);
                remaining = Math.max(0, remaining - (Date.now() - startedAt));
                countdown?.pause();
            });
            el.addEventListener('mouseleave', run);
            run();
        }

        function scan(root) {
            if (root.nodeType !== 1) return;
            if (root.matches('[data-flash]')) arm(root);
            root.querySelectorAll('[data-flash]').forEach(arm);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => scan(document.documentElement));
        } else {
            scan(document.documentElement);
        }
        new MutationObserver((mutations) => {
            mutations.forEach((m) => m.addedNodes.forEach(scan));
        }).observe(document.documentElement, { childList: true, subtree: true });
    })();
</script>
