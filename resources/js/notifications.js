function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

function updateCount(root, count) {
    const badge = root.querySelector('[data-notification-count]');
    badge.textContent = count;
    badge.classList.toggle('hidden', Number(count) <= 0);
}

function severityClass(severity) {
    if (severity === 'danger') {
        return 'border-red-200 bg-red-50';
    }

    if (severity === 'warning') {
        return 'border-amber-200 bg-amber-50';
    }

    return 'border-slate-200 bg-white';
}

function renderNotifications(root, payload) {
    const panel = root.querySelector('[data-notification-panel]');
    const items = payload.items || [];

    updateCount(root, payload.unread_count || 0);

    const body = items.length
        ? items.map((item) => `
            <div class="border-b border-slate-100 p-3 ${item.read ? 'bg-white' : severityClass(item.severity)}">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="font-semibold text-slate-900">${escapeHtml(item.title)}</div>
                        <div class="mt-1 text-xs text-slate-600">${escapeHtml(item.message)}</div>
                        <div class="mt-2 text-xs text-slate-400">${escapeHtml(item.created_at)} | ${escapeHtml(item.created_at_human)}</div>
                    </div>
                    <button
                        type="button"
                        data-notification-read-toggle
                        data-url="${escapeHtml(item.read ? item.unread_url : item.read_url)}"
                        class="shrink-0 text-xs font-semibold text-slate-600 hover:text-slate-900"
                    >${item.read ? 'Unread' : 'Read'}</button>
                </div>
                ${item.action_url ? `<a href="${escapeHtml(item.action_url)}" class="mt-3 inline-block rounded bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white">Open</a>` : ''}
            </div>
        `).join('')
        : '<div class="p-5 text-sm text-slate-500">No notifications found.</div>';

    panel.innerHTML = `
        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
            <div class="font-semibold text-slate-900">Notifications</div>
            <button type="button" data-notification-read-all class="text-xs font-semibold text-slate-600 hover:text-slate-900">Mark all read</button>
        </div>
        <div class="max-h-96 overflow-y-auto">${body}</div>
        <a href="${escapeHtml(payload.index_url)}" class="block border-t border-slate-200 px-4 py-3 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50">View all notifications</a>
    `;
    panel.dataset.markAllUrl = payload.mark_all_read_url;
    panel.classList.remove('hidden');
}

async function patchNotification(url) {
    const response = await fetch(url, {
        method: 'PATCH',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
        },
    });

    if (!response.ok) {
        throw new Error('Notification update failed');
    }

    return response.json();
}

function initializeNotificationBell(root) {
    const toggle = root.querySelector('[data-notification-toggle]');
    const panel = root.querySelector('[data-notification-panel]');
    const dropdownUrl = root.dataset.dropdownUrl;

    const load = async () => {
        panel.innerHTML = '<div class="p-5 text-sm text-slate-500">Loading alerts...</div>';
        panel.classList.remove('hidden');

        const response = await fetch(dropdownUrl, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            throw new Error('Unable to load notifications');
        }

        renderNotifications(root, await response.json());
    };

    toggle.addEventListener('click', async () => {
        if (!panel.classList.contains('hidden')) {
            panel.classList.add('hidden');
            return;
        }

        try {
            await load();
        } catch {
            panel.innerHTML = '<div class="p-5 text-sm text-red-600">Unable to load notifications.</div>';
            panel.classList.remove('hidden');
        }
    });

    panel.addEventListener('click', async (event) => {
        const toggleButton = event.target.closest('[data-notification-read-toggle]');
        const readAllButton = event.target.closest('[data-notification-read-all]');

        if (!toggleButton && !readAllButton) {
            return;
        }

        event.preventDefault();

        try {
            const payload = await patchNotification(toggleButton?.dataset.url || panel.dataset.markAllUrl);
            updateCount(root, payload.unread_count || 0);
            await load();
        } catch {
            panel.innerHTML = '<div class="p-5 text-sm text-red-600">Unable to update notification.</div>';
        }
    });

    document.addEventListener('click', (event) => {
        if (!root.contains(event.target)) {
            panel.classList.add('hidden');
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-notification-bell]').forEach(initializeNotificationBell);
});
