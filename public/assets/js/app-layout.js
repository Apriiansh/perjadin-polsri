/**
 * App Layout — Sidebar drawer + User menu dropdown
 */
(() => {
    /* ── Sidebar drawer (mobile) ──────────────────────────── */
    const drawer = document.getElementById('sidebarDrawer');
    const backdrop = document.getElementById('sidebarBackdrop');
    const openBtn = document.getElementById('sidebarOpenBtn');
    const closeBtn = document.getElementById('sidebarCloseBtn');

    const openSidebar = () => {
        if (!drawer || !backdrop) return;
        drawer.classList.remove('-translate-x-full');
        backdrop.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    };

    const closeSidebar = () => {
        if (!drawer || !backdrop) return;
        drawer.classList.add('-translate-x-full');
        backdrop.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    };

    openBtn?.addEventListener('click', openSidebar);
    closeBtn?.addEventListener('click', closeSidebar);
    backdrop?.addEventListener('click', closeSidebar);

    // Close on Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeSidebar();
            closeUserMenu();
        }
    });

    // Auto-close drawer on desktop resize
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1024) {
            backdrop?.classList.add('hidden');
            drawer?.classList.remove('-translate-x-full');
            document.body.classList.remove('overflow-hidden');
        }
    });

    /* ── User menu dropdown (navbar) ──────────────────────── */
    const userBtn = document.getElementById('userMenuBtn');
    const userDropdown = document.getElementById('userMenuDropdown');

    const openUserMenu = () => {
        if (!userDropdown) return;
        userDropdown.classList.remove('hidden');
        userBtn?.setAttribute('aria-expanded', 'true');
    };

    const closeUserMenu = () => {
        if (!userDropdown) return;
        userDropdown.classList.add('hidden');
        userBtn?.removeAttribute('aria-expanded');
    };

    userBtn?.addEventListener('click', (e) => {
        e.stopPropagation();
        userDropdown?.classList.contains('hidden') ? openUserMenu() : closeUserMenu();
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (!userDropdown?.contains(e.target) && !userBtn?.contains(e.target)) {
            closeUserMenu();
        }
    });

    /* ── Flash messages auto-dismiss ──────────────────────── */
    document.querySelectorAll('.flash-msg').forEach((el) => {
        setTimeout(() => {
            el.style.transition = 'opacity 300ms, transform 300ms';
            el.style.opacity = '0';
            el.style.transform = 'translateY(-8px)';
            setTimeout(() => el.remove(), 300);
        }, 5000);
    });
    /* ── Icons initialization ────────────────────────────── */
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
})();
