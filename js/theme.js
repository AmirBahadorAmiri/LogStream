(function () {
    const storageKey = 'logstream-theme';

    function preferredTheme() {
        const savedTheme = localStorage.getItem(storageKey);
        if (savedTheme === 'dark' || savedTheme === 'light') {
            return savedTheme;
        }
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    function applyTheme(theme) {
        document.documentElement.dataset.theme = theme;
        document.documentElement.setAttribute('data-bs-theme', theme);

        document.querySelectorAll('.theme-toggle').forEach((button) => {
            const isDark = theme === 'dark';
            button.innerHTML = '<i class="fas fa-' + (isDark ? 'sun' : 'moon') + '" aria-hidden="true"></i>'
                + '<span class="visually-hidden">' + (isDark ? 'Switch to light mode' : 'Switch to dark mode') + '</span>';
            button.title = isDark ? 'Switch to light mode' : 'Switch to dark mode';
            button.setAttribute('aria-label', button.title);
        });

        window.dispatchEvent(new CustomEvent('logstream-theme-change', { detail: { theme: theme } }));
    }

    applyTheme(preferredTheme());

    document.addEventListener('DOMContentLoaded', function () {
        applyTheme(preferredTheme());
        document.querySelectorAll('.theme-toggle').forEach((button) => {
            button.addEventListener('click', function () {
                const nextTheme = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
                localStorage.setItem(storageKey, nextTheme);
                applyTheme(nextTheme);
            });
        });
    });
})();
