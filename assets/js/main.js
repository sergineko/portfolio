(() => {
    'use strict';

    const root = document.documentElement;
    const header = document.querySelector('[data-header]');
    const nav = document.querySelector('[data-nav]');
    const menuToggle = document.querySelector('[data-menu-toggle]');
    const themeToggle = document.querySelector('[data-theme-toggle]');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const interfaceText = {
        themeDark: document.body.dataset.themeDark || 'Enable dark theme',
        themeLight: document.body.dataset.themeLight || 'Enable light theme',
        menuOpen: document.body.dataset.menuOpen || 'Open menu',
        menuClose: document.body.dataset.menuClose || 'Close menu',
    };

    const calculateAge = (birthDateValue) => {
        const parts = birthDateValue.split('-').map(Number);
        if (parts.length !== 3 || parts.some(Number.isNaN)) return null;

        const [year, month, day] = parts;
        const today = new Date();
        let age = today.getFullYear() - year;
        const birthdayPending =
            today.getMonth() + 1 < month ||
            (today.getMonth() + 1 === month && today.getDate() < day);

        if (birthdayPending) age -= 1;
        return age;
    };

    document.querySelectorAll('[data-age]').forEach((element) => {
        const age = calculateAge(element.dataset.birthdate || '');
        if (age !== null) element.textContent = String(age);
    });

    document.querySelectorAll('[data-current-year]').forEach((element) => {
        element.textContent = String(new Date().getFullYear());
    });

    const storedTheme = localStorage.getItem('portfolio-theme');
    const preferredTheme = window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
    const currentTheme = storedTheme === 'light' || storedTheme === 'dark' ? storedTheme : preferredTheme;
    root.dataset.theme = currentTheme;

    const updateThemeLabel = () => {
        themeToggle?.setAttribute(
            'aria-label',
            root.dataset.theme === 'light' ? interfaceText.themeDark : interfaceText.themeLight
        );
    };
    updateThemeLabel();

    themeToggle?.addEventListener('click', () => {
        const nextTheme = root.dataset.theme === 'light' ? 'dark' : 'light';
        root.dataset.theme = nextTheme;
        localStorage.setItem('portfolio-theme', nextTheme);
        updateThemeLabel();
    });

    const setMenuState = (open) => {
        nav?.classList.toggle('is-open', open);
        menuToggle?.setAttribute('aria-expanded', String(open));
        menuToggle?.setAttribute('aria-label', open ? interfaceText.menuClose : interfaceText.menuOpen);
    };

    menuToggle?.addEventListener('click', () => {
        setMenuState(menuToggle.getAttribute('aria-expanded') !== 'true');
    });

    nav?.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => setMenuState(false));
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') setMenuState(false);
    });

    const updateHeader = () => header?.classList.toggle('is-scrolled', window.scrollY > 12);
    updateHeader();
    window.addEventListener('scroll', updateHeader, { passive: true });

    const sections = [...document.querySelectorAll('main section[id]')];
    const navLinks = [...document.querySelectorAll('.main-nav a[href^="#"]')];

    if ('IntersectionObserver' in window) {
        const navObserver = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;
                    navLinks.forEach((link) => {
                        const active = link.getAttribute('href') === `#${entry.target.id}`;
                        link.classList.toggle('is-active', active);
                        if (active) link.setAttribute('aria-current', 'location');
                        else link.removeAttribute('aria-current');
                    });
                });
            },
            { rootMargin: '-30% 0px -60% 0px', threshold: 0 }
        );

        sections.forEach((section) => navObserver.observe(section));
    }

    const revealElements = document.querySelectorAll('[data-reveal]');
    if (reducedMotion || !('IntersectionObserver' in window)) {
        revealElements.forEach((element) => element.classList.add('is-visible'));
    } else {
        const revealObserver = new IntersectionObserver(
            (entries, observer) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                });
            },
            { rootMargin: '0px 0px -8% 0px', threshold: 0.12 }
        );

        revealElements.forEach((element) => revealObserver.observe(element));
    }

    const messageField = document.querySelector('#message');
    const characterCount = document.querySelector('[data-character-count]');
    const updateCharacterCount = () => {
        if (messageField && characterCount) characterCount.textContent = String(messageField.value.length);
    };
    messageField?.addEventListener('input', updateCharacterCount);
    updateCharacterCount();

    const form = document.querySelector('[data-contact-form]');
    const formStatus = document.querySelector('[data-form-status]');
    const submitButton = form?.querySelector('button[type="submit"]');
    const submitLabel = form?.querySelector('[data-submit-label]');
    const formText = {
        sending: form?.dataset.sending || 'Sending…',
        submit: form?.dataset.submitLabel || 'Send message',
        genericError: form?.dataset.genericError || 'The message could not be sent.',
        networkError: form?.dataset.networkError || 'The server could not be reached.',
    };

    const showFormStatus = (message, success) => {
        if (!formStatus) return;
        formStatus.textContent = message;
        formStatus.classList.add('is-visible');
        formStatus.classList.toggle('is-success', success);
        formStatus.classList.toggle('is-error', !success);
    };

    form?.addEventListener('submit', async (event) => {
        if (!window.fetch || !form.reportValidity()) return;

        event.preventDefault();
        submitButton.disabled = true;
        submitLabel.textContent = formText.sending;
        formStatus?.classList.remove('is-visible', 'is-success', 'is-error');

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            const result = await response.json();

            showFormStatus(result.message || formText.genericError, response.ok && result.success);

            if (response.ok && result.success) {
                const csrfField = form.querySelector('input[name="csrf_token"]');
                if (csrfField && typeof result.csrfToken === 'string') {
                    csrfField.value = result.csrfToken;
                }
                form.reset();
                updateCharacterCount();
            }
        } catch {
            showFormStatus(formText.networkError, false);
        } finally {
            submitButton.disabled = false;
            submitLabel.textContent = formText.submit;
        }
    });
})();
