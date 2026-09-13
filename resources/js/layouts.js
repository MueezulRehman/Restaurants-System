/**
 * Small, layout-specific behavior shared by compiled application assets.
 * Each initializer is guarded so public pages do not pay for dashboard logic.
 */
function initCeoLayout() {
    const sidebar = document.getElementById('ceo-sidebar');
    const toggle = document.getElementById('ceo-sidebar-toggle');
    const close = document.getElementById('ceo-sidebar-close');
    const backdrop = document.getElementById('ceo-sidebar-backdrop');

    if (!sidebar || !toggle || !close || !backdrop) return;

    const setOpen = (open) => {
        sidebar.classList.toggle('is-open', open);
        backdrop.classList.toggle('hidden', !open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        document.body.classList.toggle('overflow-hidden', open);
    };

    toggle.addEventListener('click', () => setOpen(true));
    close.addEventListener('click', () => setOpen(false));
    backdrop.addEventListener('click', () => setOpen(false));
    sidebar.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => setOpen(false)));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') setOpen(false);
    });
    sidebar.querySelector('a.active')?.scrollIntoView({ block: 'nearest' });

    const shell = document.getElementById('ceo-nav-search');
    const input = document.getElementById('ceo-nav-search-input');
    const results = document.getElementById('ceo-nav-search-results');
    if (!shell || !input || !results) return;

    const links = [...sidebar.querySelectorAll('nav a')].map((link) => ({
        href: link.href,
        label: link.textContent.trim().replace(/\s+/g, ' '),
    }));

    input.addEventListener('input', () => {
        const query = input.value.trim().toLowerCase();
        results.replaceChildren();
        if (!query) {
            results.classList.add('hidden');
            return;
        }

        const matches = links.filter((item) => item.label.toLowerCase().includes(query));
        if (!matches.length) {
            const empty = document.createElement('p');
            empty.className = 'px-3 py-3 text-xs text-white/60';
            empty.textContent = 'No matching page found.';
            results.appendChild(empty);
        } else {
            matches.slice(0, 8).forEach((item) => {
                const link = document.createElement('a');
                link.href = item.href;
                link.textContent = item.label;
                results.appendChild(link);
            });
        }
        results.classList.remove('hidden');
    });

    document.addEventListener('click', (event) => {
        if (!shell.contains(event.target)) results.classList.add('hidden');
    });
}

function initPagePrimitives() {
    document.querySelectorAll('[data-file-input]').forEach((input) => {
        input.addEventListener('change', () => {
            const name = input.closest('.page-file')?.querySelector('.page-file__name');
            if (!name) return;
            const files = [...input.files];
            name.textContent = files.length
                ? files.map((file) => file.name).join(', ')
                : 'No file selected';
        });
    });
}

function initPasswordToggles() {
    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.passwordToggle);
            if (!input) return;
            const visible = input.type === 'text';
            input.type = visible ? 'password' : 'text';
            button.textContent = visible ? 'Show' : 'Hide';
            button.setAttribute('aria-label', visible ? 'Show password' : 'Hide password');
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initCeoLayout();
    initPagePrimitives();
    initPasswordToggles();
});
