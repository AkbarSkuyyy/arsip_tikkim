<script>
(function () {
    function initDrawer() {
        const sidebar = document.getElementById('appSidebar');
        const toggle = document.getElementById('navToggle');
        const backdrop = document.getElementById('sidebarBackdrop');
        const media = window.matchMedia('(max-width: 900px)');
        const content = document.querySelector('.main-content, .content');
        let opened = false;
        function setOpen(value, restoreFocus = false) {
            opened = media.matches && value;
            sidebar.classList.toggle('open', opened);
            backdrop.classList.toggle('show', opened);
            toggle.setAttribute('aria-expanded', String(opened));
            toggle.setAttribute('aria-label', opened ? 'Tutup menu' : 'Buka menu');
            sidebar.inert = media.matches && !opened;
            sidebar.setAttribute('aria-hidden', String(media.matches && !opened));
            if (content) content.inert = opened;
            document.body.classList.toggle('drawer-open', opened);
            if (opened) sidebar.querySelector('a[href]')?.focus();
            else if (restoreFocus) toggle.focus();
        }
        toggle.addEventListener('click', () => setOpen(!opened, opened));
        backdrop.addEventListener('click', () => setOpen(false, true));
        sidebar.querySelectorAll('a[href]').forEach(a => a.addEventListener('click', () => setOpen(false, true)));
        document.addEventListener('keydown', event => {
            if (!opened) return;
            if (event.key === 'Escape') { event.preventDefault(); setOpen(false, true); }
            if (event.key === 'Tab') {
                const items = [...sidebar.querySelectorAll('a[href], button:not([disabled])'), toggle].filter(el => el.getClientRects().length);
                const index = items.indexOf(document.activeElement);
                if (event.shiftKey && index <= 0) { event.preventDefault(); items[items.length - 1].focus(); }
                else if (!event.shiftKey && (index === items.length - 1 || index < 0)) { event.preventDefault(); items[0].focus(); }
            }
        });
        media.addEventListener('change', () => setOpen(false));
        setOpen(false);
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initDrawer);
    else initDrawer();
})();
</script>
