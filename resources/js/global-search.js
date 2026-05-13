function debounce(callback, delay = 250) {
    let timer;

    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => callback(...args), delay);
    };
}

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function renderGroups(panel, groups) {
    const content = groups.map((group) => {
        const items = group.items.map((item) => `
            <a href="${escapeHtml(item.url)}" class="global-search-result block px-4 py-3 hover:bg-slate-50 focus:bg-slate-50 focus:outline-none">
                <div class="font-semibold text-slate-900">${escapeHtml(item.title)}</div>
                <div class="mt-1 text-xs text-slate-500">${escapeHtml(item.subtitle)}</div>
            </a>
        `).join('');

        return `
            <div class="border-b border-slate-100 last:border-b-0">
                <div class="bg-slate-50 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-slate-500">${escapeHtml(group.module)}</div>
                ${items}
            </div>
        `;
    }).join('');

    panel.innerHTML = content || '<div class="px-4 py-5 text-sm text-slate-500">No results found.</div>';
    panel.classList.remove('hidden');
}

function initializeGlobalSearch(root) {
    const toggle = root.querySelector('[data-global-search-toggle]');
    const box = root.querySelector('[data-global-search-box]');
    const input = root.querySelector('[data-global-search-input]');
    const panel = root.querySelector('[data-global-search-panel]');
    const url = root.dataset.searchUrl;
    let activeRequest = null;

    const hidePanel = () => {
        panel.classList.add('hidden');
        panel.innerHTML = '';
    };

    const isToggleSearch = Boolean(toggle && box);

    const openSearch = () => {
        if (!isToggleSearch) {
            return;
        }

        root.classList.add('is-open');
        box.setAttribute('aria-hidden', 'false');
        toggle.setAttribute('aria-expanded', 'true');
        input.removeAttribute('tabindex');
        window.requestAnimationFrame(() => input.focus());
    };

    const closeSearch = () => {
        if (!isToggleSearch) {
            hidePanel();
            return;
        }

        hidePanel();
        root.classList.remove('is-open');
        box.setAttribute('aria-hidden', 'true');
        toggle.setAttribute('aria-expanded', 'false');
        input.setAttribute('tabindex', '-1');
    };

    const search = debounce(async () => {
        const query = input.value.trim();

        if (query.length < 2) {
            if (activeRequest) {
                activeRequest.abort();
            }

            hidePanel();
            return;
        }

        if (activeRequest) {
            activeRequest.abort();
        }

        activeRequest = new AbortController();
        panel.innerHTML = '<div class="px-4 py-5 text-sm text-slate-500">Searching...</div>';
        panel.classList.remove('hidden');

        try {
            const response = await fetch(`${url}?q=${encodeURIComponent(query)}`, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: activeRequest.signal,
            });

            if (!response.ok) {
                throw new Error('Search failed');
            }

            const payload = await response.json();
            renderGroups(panel, payload.groups || []);
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }

            panel.innerHTML = '<div class="px-4 py-5 text-sm text-red-600">Search unavailable.</div>';
            panel.classList.remove('hidden');
        }
    }, 250);

    if (isToggleSearch) {
        toggle.addEventListener('click', () => {
            if (root.classList.contains('is-open')) {
                closeSearch();
                return;
            }

            openSearch();
        });
    }

    input.addEventListener('input', search);
    input.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeSearch();
            input.blur();
        }

        if (event.key === 'Enter') {
            const firstResult = panel.querySelector('.global-search-result');

            if (firstResult) {
                event.preventDefault();
                window.location.href = firstResult.href;
            }
        }
    });

    document.addEventListener('click', (event) => {
        if (!root.contains(event.target)) {
            closeSearch();
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-global-search]').forEach(initializeGlobalSearch);
});
