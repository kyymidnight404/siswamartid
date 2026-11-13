<script>
    (() => {
        const sidebar = document.getElementById('dashboardSidebar');
        const toggle = document.querySelector('[data-sidebar-toggle]');
        const closeButton = document.querySelector('[data-sidebar-close]');
        if (!sidebar || !toggle || !closeButton) return;

        const focusableSelector = 'a[href], button:not([disabled]), input:not([disabled]), [tabindex]:not([tabindex="-1"])';
        const setOpen = (isOpen) => {
            document.body.classList.toggle('mobile-sidebar-open', isOpen);
            document.body.style.overflow = isOpen ? 'hidden' : '';
            toggle.setAttribute('aria-expanded', String(isOpen));

            if (isOpen) {
                (sidebar.querySelector(focusableSelector) || sidebar).focus();
            } else if (document.activeElement !== toggle) {
                toggle.focus();
            }
        };

        sidebar.setAttribute('tabindex', '-1');
        toggle.addEventListener('click', () => setOpen(toggle.getAttribute('aria-expanded') !== 'true'));
        closeButton.addEventListener('click', () => setOpen(false));
        sidebar.addEventListener('click', (event) => {
            if (event.target.closest('a[href]')) setOpen(false);
        });
        document.addEventListener('keydown', (event) => {
            if (!document.body.classList.contains('mobile-sidebar-open')) return;
            if (event.key === 'Escape') {
                setOpen(false);
                return;
            }
            if (event.key !== 'Tab') return;

            const focusable = [...sidebar.querySelectorAll(focusableSelector)];
            if (focusable.length === 0) {
                event.preventDefault();
                sidebar.focus();
                return;
            }

            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        });
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 900 && document.body.classList.contains('mobile-sidebar-open')) {
                setOpen(false);
            }
        });
    })();
</script>
