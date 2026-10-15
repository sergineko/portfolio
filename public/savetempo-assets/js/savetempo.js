(() => {
    'use strict';

    const root = document.documentElement;
    const themeToggle = document.querySelector('[data-theme-toggle]');
    const header = document.querySelector('[data-header]');
    const showcaseTrack = document.querySelector('[data-showcase-track]');
    const languageLinks = document.querySelectorAll('[data-language]');
    const storedLanguage = window.localStorage.getItem('savetempo-language');
    if (['en', 'es'].includes(storedLanguage) && storedLanguage !== root.lang) {
        const storedLanguageLink = document.querySelector(`[data-language="${storedLanguage}"]`);
        if (storedLanguageLink instanceof HTMLAnchorElement) {
            window.location.replace(`${storedLanguageLink.pathname}${window.location.hash}`);
        }
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

    document.querySelectorAll('[data-challenge-calculator]').forEach((calculator) => {
        const input = calculator.querySelector('[data-challenge-step]');
        const total = calculator.querySelector('[data-challenge-total]');
        if (!(input instanceof HTMLInputElement) || !(total instanceof HTMLElement)) return;

        const locale = calculator.dataset.locale === 'es' ? 'es-ES' : 'en-IE';
        const formatter = new Intl.NumberFormat(locale, {
            style: 'currency',
            currency: calculator.dataset.currency || 'EUR',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
            useGrouping: 'always',
        });

        const updateChallenge = () => {
            const step = Math.max(0.01, Number.parseFloat(input.value) || 0.01);
            const factor = Number.parseFloat(total.dataset.factor || '0');
            total.textContent = formatter.format(step * factor);

            calculator.querySelectorAll('[data-challenge-amount]').forEach((cell) => {
                const index = Number.parseFloat(cell.dataset.index || '0');
                cell.textContent = formatter.format(step * index);
            });
            calculator.querySelectorAll('[data-challenge-running]').forEach((cell) => {
                const index = Number.parseFloat(cell.dataset.index || '0');
                cell.textContent = formatter.format(step * index);
            });
        };

        input.addEventListener('input', updateChallenge);
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

    showcaseTrack?.addEventListener('keydown', (event) => {
        if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;

        const items = Array.from(showcaseTrack.querySelectorAll('.showcase-item'));
        if (items.length === 0) return;

        event.preventDefault();
        const centeredLeft = (item) => item.offsetLeft - ((showcaseTrack.clientWidth - item.offsetWidth) / 2);
        const currentIndex = items.reduce((nearest, item, index) => (
            Math.abs(centeredLeft(item) - showcaseTrack.scrollLeft)
                < Math.abs(centeredLeft(items[nearest]) - showcaseTrack.scrollLeft)
                ? index
                : nearest
        ), 0);
        const nextIndex = event.key === 'Home'
            ? 0
            : event.key === 'End'
                ? items.length - 1
                : Math.max(0, Math.min(items.length - 1, currentIndex + (event.key === 'ArrowRight' ? 1 : -1)));

        showcaseTrack.scrollTo({
            left: centeredLeft(items[nextIndex]),
            behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
        });
    });

    const updateHeader = () => header?.classList.toggle('is-scrolled', window.scrollY > 12);
    updateHeader();
    window.addEventListener('scroll', updateHeader, { passive: true });
})();
