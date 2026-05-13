function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

function swal() {
    return window.Swal;
}

function toast(icon, title) {
    if (!swal() || !title) {
        return;
    }

    swal().fire({
        toast: true,
        position: 'top-end',
        icon,
        title,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
    });
}

function modal(icon, title, text = '') {
    if (!swal()) {
        return;
    }

    swal().fire({
        icon,
        title,
        text,
        confirmButtonColor: '#0f172a',
    });
}

function escapeHtml(value) {
    const element = document.createElement('div');
    element.textContent = value || '';

    return element.innerHTML;
}

function escapeSelector(value) {
    if (window.CSS?.escape) {
        return window.CSS.escape(value);
    }

    return String(value).replace(/"/g, '\\"');
}

function inputNameFromErrorKey(key) {
    const parts = String(key).split('.');

    if (parts.length === 1) {
        return key;
    }

    return parts.shift() + parts.map((part) => `[${part}]`).join('');
}

function setStatus(root, message, type = 'success') {
    const status = root.querySelector('[data-auth-status]');

    if (!status || !message) {
        return;
    }

    status.textContent = message;
    status.classList.remove(
        'hidden',
        'border-emerald-200',
        'bg-emerald-50',
        'text-emerald-800',
        'border-rose-200',
        'bg-rose-50',
        'text-rose-700',
    );

    if (type === 'error') {
        status.classList.add('border-rose-200', 'bg-rose-50', 'text-rose-700');
        return;
    }

    status.classList.add('border-emerald-200', 'bg-emerald-50', 'text-emerald-800');
}

function clearErrors(form) {
    form.querySelectorAll('[data-auth-errors]').forEach((element) => {
        element.innerHTML = '';
        element.classList.add('hidden');
    });
    form.querySelectorAll('[data-auth-error-for]').forEach((element) => {
        element.textContent = '';
        element.classList.add('hidden');
    });
    form.querySelectorAll('[aria-invalid="true"]').forEach((element) => {
        element.removeAttribute('aria-invalid');
    });
}

function renderErrors(form, errors) {
    const summary = form.querySelector('[data-auth-errors]');
    const messages = Object.values(errors || {}).flat();

    if (summary && messages.length) {
        summary.innerHTML = `<ul class="list-disc space-y-1 pl-5">${messages.map((message) => `<li>${escapeHtml(message)}</li>`).join('')}</ul>`;
        summary.classList.remove('hidden');
    }

    Object.entries(errors || {}).forEach(([key, fieldErrors]) => {
        const fieldName = inputNameFromErrorKey(key);
        const field = form.querySelector(`[name="${escapeSelector(fieldName)}"]`);
        const holder = form.querySelector(`[data-auth-error-for="${escapeSelector(key)}"]`);
        const message = Array.isArray(fieldErrors) ? fieldErrors[0] : fieldErrors;

        if (field) {
            field.setAttribute('aria-invalid', 'true');
        }

        if (holder) {
            holder.textContent = message;
            holder.classList.remove('hidden');
        }
    });
}

function setLoading(button, loading) {
    if (!button) {
        return;
    }

    if (loading) {
        button.dataset.originalText = button.dataset.originalText || button.textContent.trim();
        button.disabled = true;
        button.innerHTML = `<span class="auth-spinner"></span><span>${button.dataset.loadingText || 'Please wait...'}</span>`;
        return;
    }

    button.disabled = false;
    if (button.dataset.originalText) {
        button.textContent = button.dataset.originalText;
    }
}

async function parseResponse(response) {
    const contentType = response.headers.get('content-type') || '';

    if (contentType.includes('application/json')) {
        return response.json();
    }

    return {
        message: response.ok ? 'Request completed.' : 'Request failed.',
        redirect: response.redirected ? response.url : null,
    };
}

function showPanel(root, panelName) {
    const target = root.querySelector(`[data-auth-panel="${escapeSelector(panelName)}"]`);

    if (!target) {
        return;
    }

    root.querySelectorAll('[data-auth-panel]').forEach((panel) => {
        panel.hidden = panel !== target;
    });

    root.querySelectorAll('[data-auth-switch]').forEach((button) => {
        button.classList.toggle('is-active', button.dataset.authSwitch === panelName);
    });

    const heading = root.querySelector('[data-auth-heading]');
    if (heading && target.dataset.title) {
        heading.textContent = target.dataset.title;
    }

    const focusable = target.querySelector('input:not([type="hidden"]), button, select, textarea');
    if (focusable && window.innerWidth >= 768) {
        window.setTimeout(() => focusable.focus(), 60);
    }
}

function initializeAuthAccess(root) {
    showPanel(root, root.dataset.initialPanel || 'login');

    root.querySelectorAll('[data-auth-switch]').forEach((button) => {
        button.addEventListener('click', () => {
            showPanel(root, button.dataset.authSwitch);
        });
    });

    root.querySelectorAll('form[data-auth-ajax]').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            clearErrors(form);

            const submitter = event.submitter || form.querySelector('button[type="submit"], button:not([type])');
            const formData = new FormData(form);

            setLoading(submitter, true);

            try {
                const response = await fetch(form.action, {
                    method: form.method || 'POST',
                    body: formData,
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const payload = await parseResponse(response);

                if (response.status === 422 && payload.errors) {
                    renderErrors(form, payload.errors);
                    setStatus(root, payload.message || 'Please correct the highlighted fields.', 'error');
                    return;
                }

                if (!response.ok || payload.success === false) {
                    setStatus(root, payload.message || 'Request failed.', 'error');
                    modal('error', 'Request failed', payload.message || 'Please try again.');
                    return;
                }

                const message = payload.message || 'Request completed.';
                setStatus(root, message);
                toast('success', message);

                const successPanel = payload.panel || form.dataset.successPanel;
                if (successPanel) {
                    showPanel(root, successPanel);
                    form.reset();
                }

                if (form.dataset.authRedirect !== 'false' && payload.redirect) {
                    window.setTimeout(() => {
                        window.location.href = payload.redirect;
                    }, 450);
                }
            } catch {
                setStatus(root, 'Unable to reach the server. Please try again.', 'error');
                modal('error', 'Request failed', 'Please check your connection and try again.');
            } finally {
                setLoading(submitter, false);
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-auth-access]').forEach(initializeAuthAccess);
});
