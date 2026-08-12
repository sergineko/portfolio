(() => {
    'use strict';

    const root = document.documentElement;
    const themeToggle = document.querySelector('[data-theme-toggle]');
    const header = document.querySelector('[data-header]');
    const languageLinks = document.querySelectorAll('[data-language]');
    const query = new URLSearchParams(window.location.search);

    const storedLanguage = window.localStorage.getItem('savetempo-language');
    if (!query.has('lang') && ['en', 'es'].includes(storedLanguage) && storedLanguage !== root.lang) {
        query.set('lang', storedLanguage);
        const nextQuery = query.toString();
        window.location.replace(`${window.location.pathname}${nextQuery ? `?${nextQuery}` : ''}${window.location.hash}`);
        return;
    }

    languageLinks.forEach((link) => {
        link.addEventListener('click', () => {
            const language = link.getAttribute('data-language');
            if (language === 'en' || language === 'es') {
                window.localStorage.setItem('savetempo-language', language);
            }
        });
    });

    const systemTheme = window.matchMedia('(prefers-color-scheme: dark)');
    const storedTheme = window.localStorage.getItem('savetempo-theme');
    const initialTheme = storedTheme === 'light' || storedTheme === 'dark'
        ? storedTheme
        : (systemTheme.matches ? 'dark' : 'light');

    const applyTheme = (theme) => {
        root.dataset.theme = theme;
        if (themeToggle) {
            const baseLabel = document.body.dataset.themeLabel || 'Change color theme';
            themeToggle.setAttribute('aria-label', `${baseLabel}: ${theme}`);
            themeToggle.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');
            const icon = themeToggle.querySelector('span');
            if (icon) icon.textContent = theme === 'dark' ? '☀' : '☾';
        }
    };

    applyTheme(initialTheme);

    themeToggle?.addEventListener('click', () => {
        const nextTheme = root.dataset.theme === 'dark' ? 'light' : 'dark';
        window.localStorage.setItem('savetempo-theme', nextTheme);
        applyTheme(nextTheme);
    });

    const updateHeader = () => header?.classList.toggle('is-scrolled', window.scrollY > 12);
    updateHeader();
    window.addEventListener('scroll', updateHeader, { passive: true });
})();
