<div id="toast-container" style="position:fixed;top:1.25rem;right:1.25rem;z-index:9999;display:flex;flex-direction:column;gap:0.5rem;max-width:320px;width:calc(100vw - 2.5rem);pointer-events:none;"></div>

<script>
(function() {
    const colors = {
        success: { bg: '#1E3A5F', border: '#2563EB' },
        error:   { bg: '#7F1D1D', border: '#DC2626' },
        warning: { bg: '#78350F', border: '#D97706' },
        info:    { bg: '#1C1A2E', border: '#6C3CE1' },
    };
    const durations = { success: 4000, error: 6000, warning: 0, info: 4000 };

    window.showToast = function({ message, type = 'info', duration }) {
        const c = colors[type] || colors.info;
        const ms = duration !== undefined ? duration : durations[type];
        const el = document.createElement('div');
        el.style.cssText = `
            background:${c.bg};
            border-left:3px solid ${c.border};
            border-radius:8px;
            padding:0.75rem 1rem;
            color:#fff;
            font-size:0.85rem;
            font-weight:500;
            line-height:1.4;
            box-shadow:0 4px 16px rgba(0,0,0,0.25);
            display:flex;
            align-items:flex-start;
            gap:0.5rem;
            pointer-events:all;
            cursor:pointer;
            opacity:0;
            transition:opacity 0.2s ease;
        `;
        el.innerHTML = '<span style="flex:1;">' + message + '</span><span style="opacity:0.5;font-size:1rem;line-height:1;flex-shrink:0;">✕</span>';
        el.addEventListener('click', () => dismiss(el));
        document.getElementById('toast-container').appendChild(el);
        requestAnimationFrame(() => { el.style.opacity = '1'; });
        if (ms > 0) setTimeout(() => dismiss(el), ms);
    };

    function dismiss(el) {
        el.style.opacity = '0';
        setTimeout(() => el.remove(), 200);
    }

    window.addEventListener('toast', e => window.showToast(e.detail));
})();
</script>
