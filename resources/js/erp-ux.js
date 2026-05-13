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
        timer: 3200,
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

function setButtonLoading(button, loading) {
    if (!button) {
        return;
    }

    if (loading) {
        button.dataset.originalText = button.dataset.originalText || button.textContent.trim();
        button.disabled = true;
        button.classList.add('is-loading');
        button.innerHTML = `<span class="erp-spinner"></span><span>${button.dataset.loadingText || 'Saving...'}</span>`;
        return;
    }

    button.disabled = false;
    button.classList.remove('is-loading');
    if (button.dataset.originalText) {
        button.textContent = button.dataset.originalText;
    }
}

function escapeSelector(value) {
    if (window.CSS?.escape) {
        return window.CSS.escape(value);
    }

    return String(value).replace(/"/g, '\\"');
}

function escapeHtml(value) {
    const element = document.createElement('div');
    element.textContent = value || '';

    return element.innerHTML;
}

function inputNameFromErrorKey(key) {
    const parts = String(key).split('.');

    if (parts.length === 1) {
        return key;
    }

    return parts.shift() + parts.map((part) => `[${part}]`).join('');
}

function clearAjaxErrors(form) {
    form.querySelectorAll('.erp-field-error').forEach((element) => element.remove());
    form.querySelectorAll('[aria-invalid="true"]').forEach((element) => {
        element.removeAttribute('aria-invalid');
        element.classList.remove('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');
    });
    form.querySelectorAll('[data-ajax-errors]').forEach((element) => {
        element.innerHTML = '';
        element.classList.add('hidden');
    });
    form.querySelectorAll('[data-error-for]').forEach((element) => {
        element.textContent = '';
        element.classList.add('hidden');
    });
}

function renderAjaxErrors(form, errors) {
    const summary = form.querySelector('[data-ajax-errors]');
    const messages = Object.values(errors || {}).flat();

    if (summary && messages.length) {
        summary.innerHTML = `<ul class="list-disc space-y-1 pl-5">${messages.map((message) => `<li>${escapeHtml(message)}</li>`).join('')}</ul>`;
        summary.classList.remove('hidden');
    }

    Object.entries(errors || {}).forEach(([key, fieldErrors]) => {
        const fieldName = inputNameFromErrorKey(key);
        const field = form.querySelector(`[name="${escapeSelector(fieldName)}"]`);
        const holder = form.querySelector(`[data-error-for="${escapeSelector(key)}"]`);
        const message = Array.isArray(fieldErrors) ? fieldErrors[0] : fieldErrors;

        if (holder) {
            holder.textContent = message;
            holder.classList.remove('hidden');
        }

        if (!field) {
            return;
        }

        field.setAttribute('aria-invalid', 'true');
        field.classList.add('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');

        if (!holder) {
            field.insertAdjacentHTML('afterend', `<p class="erp-field-error mt-1 text-xs font-semibold text-red-600">${message}</p>`);
        }
    });
}

async function parseResponse(response) {
    const contentType = response.headers.get('content-type') || '';

    if (contentType.includes('application/json')) {
        return response.json();
    }

    return {
        success: response.ok,
        message: response.ok ? 'Saved successfully.' : 'Request failed.',
        redirect: response.redirected ? response.url : null,
    };
}

function initializeAjaxForms() {
    document.addEventListener('submit', async (event) => {
        const form = event.target.closest('form[data-ajax-form]');

        if (!form) {
            return;
        }

        event.preventDefault();
        clearAjaxErrors(form);

        const submitter = event.submitter || form.querySelector('button[type="submit"], button:not([type])');
        const formData = new FormData(form);

        setButtonLoading(submitter, true);

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
                renderAjaxErrors(form, payload.errors);
                modal('error', payload.message || 'Please correct the highlighted fields.');
                return;
            }

            if (!response.ok || payload.success === false) {
                modal('error', payload.message || 'Unable to save this record.');
                return;
            }

            toast('success', payload.message || 'Saved successfully.');

            if (payload.redirect) {
                window.setTimeout(() => {
                    window.location.href = payload.redirect;
                }, 550);
            }
        } catch {
            modal('error', 'Request failed', 'Please check your connection and try again.');
        } finally {
            setButtonLoading(submitter, false);
        }
    });
}

function initializeConfirmations() {
    document.addEventListener('submit', async (event) => {
        const form = event.target.closest('form[data-confirm-delete], form[data-confirm-action], form[onsubmit*="confirm"]');

        if (!form || form.dataset.confirmed === 'true') {
            return;
        }

        event.preventDefault();

        if (!swal()) {
            form.dataset.confirmed = 'true';
            form.removeAttribute('onsubmit');
            form.onsubmit = null;
            form.submit();
            return;
        }

        const inlineConfirm = form.getAttribute('onsubmit') || '';
        const inlineTitle = inlineConfirm.match(/confirm\(['"](.+?)['"]\)/)?.[1];
        const result = await swal().fire({
            title: form.dataset.confirmTitle || inlineTitle || 'Are you sure?',
            text: form.dataset.confirmText || 'This action cannot be undone.',
            icon: form.dataset.confirmIcon || 'warning',
            showCancelButton: true,
            confirmButtonText: form.dataset.confirmButton || 'Yes, continue',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#475569',
            reverseButtons: true,
        });

        if (result.isConfirmed) {
            form.dataset.confirmed = 'true';
            form.removeAttribute('onsubmit');
            form.onsubmit = null;
            form.requestSubmit();
        }
    }, true);
}

function initializeFlashMessages() {
    const flash = window.erpFlash || {};

    if (flash.success) {
        toast('success', flash.success);
    }

    if (flash.error) {
        modal('error', 'Error', flash.error);
    }

    if (flash.warning) {
        modal('warning', 'Warning', flash.warning);
    }
}

function initializeSelects() {
    if (!window.$?.fn?.select2) {
        return;
    }

    window.$('select[data-searchable], select.js-searchable').each(function () {
        const element = window.$(this);

        if (element.data('select2')) {
            return;
        }

        element.select2({
            width: '100%',
            allowClear: element.attr('required') === undefined,
            placeholder: element.data('placeholder') || element.find('option:first').text() || 'Select',
        });
    });
}

function initializeDataTables() {
    if (!window.$?.fn?.DataTable) {
        return;
    }

    window.$('table[data-erp-datatable]').each(function () {
        const table = window.$(this);

        if (window.$.fn.DataTable.isDataTable(this)) {
            return;
        }

        const ajaxUrl = table.data('ajaxUrl');
        const filterSelector = table.data('filterForm');
        const columns = table.find('thead th[data-column]').map(function () {
            const header = window.$(this);

            return {
                data: header.data('column'),
                name: header.data('name') || header.data('column'),
                orderable: header.data('orderable') !== false,
                searchable: header.data('searchable') !== false,
                className: header.attr('class') || '',
            };
        }).get();

        const options = {
            responsive: true,
            pageLength: Number(table.data('pageLength') || 15),
            order: [],
            language: {
                search: '',
                searchPlaceholder: table.data('searchPlaceholder') || 'Search records...',
                emptyTable: table.data('empty') || 'No records found.',
                processing: '<div class="erp-table-loading">Loading records...</div>',
            },
            dom: "<'erp-dt-top'lfB>rt<'erp-dt-bottom'ip>",
            buttons: table.data('export') === false ? [] : ['copy', 'csv', 'excel', 'print'],
        };

        if (ajaxUrl && columns.length) {
            options.processing = true;
            options.serverSide = true;
            options.ajax = {
                url: ajaxUrl,
                data(data) {
                    new URLSearchParams(window.location.search).forEach((value, key) => {
                        data[key] = value;
                    });

                    if (!filterSelector) {
                        return data;
                    }

                    const filterForm = document.querySelector(filterSelector);
                    if (!filterForm) {
                        return data;
                    }

                    new FormData(filterForm).forEach((value, key) => {
                        data[key] = value;
                    });

                    return data;
                },
                error(xhr) {
                    const message = xhr.status === 403
                        ? 'You do not have permission to view this table.'
                        : 'Unable to load table records.';

                    modal('error', 'Table loading failed', message);
                },
            };
            options.columns = columns;
        }

        const instance = table.DataTable(options);

        if (filterSelector) {
            const filterForm = document.querySelector(filterSelector);
            filterForm?.addEventListener('submit', (event) => {
                event.preventDefault();
                instance.ajax.reload();
            });
            filterForm?.querySelectorAll('select, input[type="date"]').forEach((input) => {
                input.addEventListener('change', () => instance.ajax.reload());
            });
            filterForm?.querySelector('[data-reset-filters]')?.addEventListener('click', () => {
                filterForm.reset();
                window.$?.(filterForm).find('select[data-searchable], select.js-searchable').trigger('change.select2');
                instance.ajax.reload();
            });
        }
    });
}

function initializeDashboardCharts() {
    const root = document.querySelector('[data-dashboard-charts]');

    if (!root || !window.Chart) {
        return;
    }

    const url = new URL(root.dataset.dashboardCharts, window.location.origin);
    const currentParams = new URLSearchParams(window.location.search);

    currentParams.forEach((value, key) => url.searchParams.set(key, value));

    fetch(url.toString(), {
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    })
        .then((response) => response.json())
        .then((charts) => {
            window.dispatchEvent(new CustomEvent('erp:dashboard-charts', { detail: charts }));
        })
        .catch(() => {
            root.querySelectorAll('[data-chart-loading]').forEach((element) => {
                element.textContent = 'Unable to load chart data.';
            });
        });
}

window.ErpToast = { toast, modal };

function initializePageLoadingOverlay() {
    if (!document.body.classList.contains('lt-app')) {
        return;
    }

    if (!document.querySelector('[data-page-loading-overlay]')) {
        const overlay = document.createElement('div');
        overlay.dataset.pageLoadingOverlay = 'true';
        overlay.className = 'erp-page-loading-overlay';
        overlay.innerHTML = '<div class="erp-page-loading-card"><span class="erp-spinner"></span><span>Loading...</span></div>';
        document.body.appendChild(overlay);
    }

    window.addEventListener('beforeunload', () => {
        document.body.classList.add('erp-page-loading');
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initializePageLoadingOverlay();
    initializeFlashMessages();
    initializeAjaxForms();
    initializeConfirmations();
    initializeSelects();
    initializeDataTables();
    initializeDashboardCharts();
});

document.addEventListener('erp:refresh-selects', initializeSelects);
