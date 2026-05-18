(function() {
    const STORAGE_KEY = 'habitflow-theme';

    function getInitialTheme() {
        try {
            const saved = localStorage.getItem(STORAGE_KEY);
            if (saved === 'light' || saved === 'dark') {
                return saved;
            }
        } catch (e) {
            // localStorage may be blocked
        }

        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            return 'dark';
        }

        return 'light';
    }

    function applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        try {
            localStorage.setItem(STORAGE_KEY, theme);
        } catch (e) {
            // ignore
        }
    }

    function toggleTheme() {
        const current = document.documentElement.getAttribute('data-theme');
        const next = current === 'dark' ? 'light' : 'dark';
        applyTheme(next);
    }

    applyTheme(getInitialTheme());

    window.toggleTheme = toggleTheme;
    window.applyTheme = applyTheme;

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.theme-toggle').forEach(function(btn) {
            btn.addEventListener('click', toggleTheme);
        });
    });

    if (window.matchMedia) {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
            try {
                if (!localStorage.getItem(STORAGE_KEY)) {
                    applyTheme(e.matches ? 'dark' : 'light');
                }
            } catch (err) {
                // ignore
            }
        });
    }
})();