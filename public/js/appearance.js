// Runs before styles so a saved dark theme never flashes a light page.
(() => {
    const themeKey = 'ton.appearance';
    const favoritesKey = 'ton.favorites';
    const system = matchMedia('(prefers-color-scheme: dark)');
    const validTheme = value => ['light', 'dark', 'system'].includes(value) ? value : 'system';
    const read = key => { try { return localStorage.getItem(key); } catch { return null; } };
    const write = (key, value) => { try { localStorage.setItem(key, value); return true; } catch { return false; } };
    const parseFavorites = value => {
        try {
            const ids = JSON.parse(value);
            return Array.isArray(ids) ? [...new Set(ids.filter(id => Number.isSafeInteger(id) && id > 0))].slice(0, 200) : [];
        } catch { return []; }
    };
    let preference = validTheme(read(themeKey));
    let appearance;
    let favorites;
    const apply = () => {
        const resolved = preference === 'system' ? (system.matches ? 'dark' : 'light') : preference;
        document.documentElement.dataset.theme = resolved;
        document.querySelector('meta[name="theme-color"]')?.setAttribute('content', resolved === 'dark' ? '#1b1817' : '#f7f6f1');
        if (appearance) { appearance.preference = preference; appearance.resolved = resolved; }
    };
    const favoritesChanged = () => window.dispatchEvent(new CustomEvent('studio-favorites'));
    apply();
    system.addEventListener('change', () => { if (preference === 'system') apply(); });

    document.addEventListener('alpine:init', () => {
        Alpine.store('appearance', {
            preference,
            resolved: document.documentElement.dataset.theme,
            persistent: true,
            set(value) { preference = validTheme(value); this.persistent = write(themeKey, preference); apply(); },
        });
        appearance = Alpine.store('appearance');
        Alpine.store('favorites', {
            ids: parseFavorites(read(favoritesKey)),
            message: '',
            has(id) { return this.ids.includes(id); },
            toggle(id) {
                if (!Number.isSafeInteger(id) || id < 1) return;
                if (!this.has(id) && this.ids.length >= 200) {
                    this.message = 'Можно сохранить до 200 студий. Уберите одну, чтобы добавить новую.';
                    return;
                }
                this.ids = this.has(id) ? this.ids.filter(saved => saved !== id) : [...this.ids, id];
                this.message = write(favoritesKey, JSON.stringify(this.ids)) ? '' : 'Браузер не разрешил сохранение. Избранное доступно до закрытия страницы.';
                favoritesChanged();
            },
        });
        favorites = Alpine.store('favorites');
    });

    window.addEventListener('storage', event => {
        if (event.key === themeKey || event.key === null) { preference = validTheme(read(themeKey)); apply(); }
        if (favorites && (event.key === favoritesKey || event.key === null)) {
            favorites.ids = parseFavorites(read(favoritesKey));
            favorites.message = '';
            favoritesChanged();
        }
    });
})();
