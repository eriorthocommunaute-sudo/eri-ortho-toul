(() => {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const toggle = document.getElementById('menuToggle');

    if (!sidebar || !toggle) {
        return;
    }

    const close = () => {
        sidebar.classList.remove('open');
        if (overlay) {
            overlay.classList.remove('show');
        }
        toggle.setAttribute('aria-expanded', 'false');
    };

    toggle.addEventListener('click', () => {
        const open = sidebar.classList.toggle('open');
        if (overlay) {
            overlay.classList.toggle('show', open);
        }
        toggle.setAttribute('aria-expanded', String(open));
    });

    if (overlay) {
        overlay.addEventListener('click', close);
    }

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            close();
        }
    });

    sidebar.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 980) {
                close();
            }
        });
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth > 980) {
            close();
        }
    });
})();
