document.addEventListener('livewire:init', () => {
    const notify = (failed) => window.dispatchEvent(new CustomEvent('studio-connection', { detail: { failed } }));

    Livewire.interceptRequest(({ onFailure, onSuccess }) => {
        onFailure(() => notify(true));
        onSuccess(() => notify(false));
    });
});

// Delegation keeps newly filtered Livewire cards interactive without rebinding.
(() => {
    const finePointer = matchMedia('(hover: hover) and (pointer: fine)');
    const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)');
    const reducedTransparency = matchMedia('(prefers-reduced-transparency: reduce)');
    let surface = null;
    let frame = null;
    let x = 50;
    let y = 35;

    const reset = () => {
        if (frame !== null) cancelAnimationFrame(frame);
        frame = null;
        if (surface) {
            surface.style.removeProperty('--glint-x');
            surface.style.removeProperty('--glint-y');
            surface.removeAttribute('data-glass-active');
        }
        surface = null;
    };

    document.addEventListener('pointermove', (event) => {
        if (!finePointer.matches || reducedMotion.matches || reducedTransparency.matches || event.pointerType === 'touch') return;
        const target = event.target instanceof Element ? event.target.closest('[data-glass-reactive]') : null;
        if (target !== surface) {
            reset();
            surface = target;
        }
        if (!surface) return;
        const bounds = surface.getBoundingClientRect();
        x = Math.max(0, Math.min(100, (event.clientX - bounds.left) / bounds.width * 100));
        y = Math.max(0, Math.min(100, (event.clientY - bounds.top) / bounds.height * 100));
        if (frame !== null) return;
        frame = requestAnimationFrame(() => {
            frame = null;
            if (!surface?.isConnected) return reset();
            surface.style.setProperty('--glint-x', `${x}%`);
            surface.style.setProperty('--glint-y', `${y}%`);
            surface.setAttribute('data-glass-active', '');
        });
    }, { passive: true });

    document.addEventListener('pointerout', (event) => {
        if (surface && (!(event.relatedTarget instanceof Node) || !surface.contains(event.relatedTarget))) reset();
    }, { passive: true });
    window.addEventListener('blur', reset);
    document.addEventListener('visibilitychange', () => { if (document.hidden) reset(); });
    [finePointer, reducedMotion, reducedTransparency].forEach((preference) => preference.addEventListener('change', reset));
})();
