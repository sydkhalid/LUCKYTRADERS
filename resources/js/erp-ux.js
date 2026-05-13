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

function debounce(callback, delay = 250) {
    let timeout = null;

    return (...args) => {
        window.clearTimeout(timeout);
        timeout = window.setTimeout(() => callback(...args), delay);
    };
}

function iconMarkup(name, className = '') {
    const classes = ['erp-icon', className].filter(Boolean).join(' ');
    const attrs = `class="${classes}" width="16" height="16" aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"`;
    const icons = {
        refresh: '<path d="M21 12a9 9 0 0 1-15.3 6.4"/><path d="M3 12A9 9 0 0 1 18.3 5.6"/><path d="M18 2v4h-4"/><path d="M6 22v-4h4"/>',
        copy: '<rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>',
        csv: '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M8 13h8"/><path d="M8 17h5"/>',
        excel: '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="m8 13 4 5"/><path d="m12 13-4 5"/>',
        print: '<path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/>',
        filter: '<path d="M3 5h18"/><path d="M6 12h12"/><path d="M10 19h4"/>',
        reset: '<path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 3v6h6"/>',
        search: '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
        chevronDown: '<path d="m6 9 6 6 6-6"/>',
    };

    return `<svg ${attrs}>${icons[name] || icons.filter}</svg>`;
}

function buttonText(icon, label) {
    return `${iconMarkup(icon)}<span>${label}</span>`;
}

function decorateFilterButton(button, icon, loadingText = null) {
    if (!button || button.querySelector('svg')) {
        return;
    }

    const label = button.textContent.trim() || 'Apply';
    button.innerHTML = `${iconMarkup(icon)}<span>${escapeHtml(label)}</span>`;

    if (loadingText) {
        button.dataset.loadingText = loadingText;
    }
}

function ensureCommonDateFilters(form) {
    if (!form || form.dataset.commonDateFilters === 'false') {
        return;
    }

    if (form.querySelector('[name="from_date"]') || form.querySelector('[name="to_date"]')) {
        return;
    }

    form.insertAdjacentHTML('afterbegin', `
        <div class="erp-filter-field" data-common-date-filter>
            <label>From Date</label>
            <input type="date" name="from_date" class="w-full">
        </div>
        <div class="erp-filter-field" data-common-date-filter>
            <label>To Date</label>
            <input type="date" name="to_date" class="w-full">
        </div>
    `);
}

function filterControls(form) {
    return Array.from(form.querySelectorAll('input, select, textarea')).filter((control) => {
        const type = (control.type || '').toLowerCase();

        return !['button', 'submit', 'reset', 'hidden'].includes(type) && !control.disabled;
    });
}

function activeFilterCount(form) {
    return filterControls(form).filter((control) => {
        const type = (control.type || '').toLowerCase();

        if (type === 'checkbox' || type === 'radio') {
            return control.checked;
        }

        return String(control.value || '').trim() !== '';
    }).length;
}

function setAdvancedSearchOpen(form, open) {
    const shell = form?.closest('[data-advanced-search]');
    const toggle = shell?.querySelector('[data-advanced-search-toggle]');

    if (!shell || !toggle) {
        return;
    }

    shell.classList.toggle('is-open', open);
    form.hidden = !open;
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
}

function updateAdvancedSearchState(form) {
    const shell = form?.closest('[data-advanced-search]');
    const badge = shell?.querySelector('[data-advanced-search-count]');
    const count = form ? activeFilterCount(form) : 0;

    if (!shell || !badge) {
        return;
    }

    shell.classList.toggle('has-active-filters', count > 0);
    badge.hidden = count === 0;
    badge.textContent = `${count} active`;
}

function ensureAdvancedSearchShell(form) {
    let shell = form.closest('[data-advanced-search]');

    if (shell) {
        return shell;
    }

    const id = form.id || `erpFilter${Math.random().toString(36).slice(2, 9)}`;
    form.id = id;

    shell = document.createElement('div');
    shell.className = 'erp-advanced-search';
    shell.dataset.advancedSearch = 'true';

    const toggle = document.createElement('button');
    toggle.type = 'button';
    toggle.className = 'erp-advanced-search-toggle';
    toggle.dataset.advancedSearchToggle = 'true';
    toggle.setAttribute('aria-controls', id);
    toggle.setAttribute('aria-expanded', 'false');
    toggle.innerHTML = `
        <span class="erp-advanced-search-copy">
            <span class="erp-advanced-search-title">${iconMarkup('filter')}<span>Advanced Search</span></span>
            <span class="erp-advanced-search-subtitle">Click to search, filter dates, status and table fields</span>
        </span>
        <span class="erp-advanced-search-meta">
            <span class="erp-advanced-search-badge" data-advanced-search-count hidden>0 active</span>
            <span class="erp-advanced-search-chevron">${iconMarkup('chevronDown')}</span>
        </span>
    `;

    form.parentNode.insertBefore(shell, form);
    shell.appendChild(toggle);
    shell.appendChild(form);

    toggle.addEventListener('click', () => {
        setAdvancedSearchOpen(form, toggle.getAttribute('aria-expanded') !== 'true');
    });

    return shell;
}

function prepareFilterForm(form, options = {}) {
    if (!form) {
        return;
    }

    if (options.ensureDates) {
        ensureCommonDateFilters(form);
    }

    const shell = ensureAdvancedSearchShell(form);
    const shouldOpen = form.dataset.advancedOpen === 'true' || options.open === true || activeFilterCount(form) > 0;

    form.classList.add('erp-filter-form');
    form.querySelectorAll(':scope > div, :scope > .grid > div').forEach((field) => {
        field.classList.add('erp-filter-field');
    });

    if (form.dataset.erpFilterPrepared !== 'true') {
        decorateFilterButton(form.querySelector('button[type="submit"], button:not([type])'), 'filter', 'Filtering...');
        form.querySelectorAll('[data-reset-filters]').forEach((button) => decorateFilterButton(button, 'reset'));

        form.addEventListener('input', () => updateAdvancedSearchState(form));
        form.addEventListener('change', () => updateAdvancedSearchState(form));

        form.dataset.erpFilterPrepared = 'true';
    }

    setAdvancedSearchOpen(form, shell.classList.contains('is-open') || shouldOpen);
    updateAdvancedSearchState(form);
}

function attachAdvancedTableSearch(form, instance, placeholder = 'Search records...') {
    if (!form || form.querySelector('[data-table-search]')) {
        return;
    }

    const field = document.createElement('div');
    field.className = 'erp-filter-field erp-filter-field-wide';
    field.dataset.tableSearchField = 'true';
    field.innerHTML = `
        <label>Search</label>
        <div class="erp-filter-search-input">
            ${iconMarkup('search')}
            <input type="search" data-table-search placeholder="${escapeHtml(placeholder)}">
        </div>
    `;

    form.insertBefore(field, form.firstElementChild);

    const input = field.querySelector('[data-table-search]');
    const runSearch = debounce(() => instance.search(input.value).draw(), 300);

    input.addEventListener('input', () => {
        updateAdvancedSearchState(form);
        runSearch();
    });

    updateAdvancedSearchState(form);
}

function initializeFilterForms() {
    document.querySelectorAll('.erp-content form').forEach((form) => {
        if (form.matches('[data-dashboard-filter], [data-no-advanced-search]')) {
            return;
        }

        const id = (form.id || '').toLowerCase();
        const method = (form.getAttribute('method') || form.method || '').toLowerCase();
        const hasFilterInput = form.querySelector('input[name="from_date"], input[name="to_date"], input[type="date"], input[type="month"], select[name*="status"], select[name*="type"]');
        const hasFilterButton = Array.from(form.querySelectorAll('button')).some((button) => /filter|apply/i.test(button.textContent || ''));
        const isAjaxFilter = id.includes('filter') || form.querySelector('[data-reset-filters]');
        const isGetFilter = method === 'get' && (hasFilterInput || hasFilterButton);

        if (isAjaxFilter || isGetFilter) {
            prepareFilterForm(form);
        }
    });
}

function initializeButtonIcons() {
    document.querySelectorAll('.erp-page-header .erp-primary-button, .erp-page-header .erp-secondary-button').forEach((button) => {
        if (button.querySelector('svg')) {
            return;
        }

        const label = button.textContent.trim();
        const normalized = label.toLowerCase();
        let icon = null;

        if (/^(add|create|new)\b/.test(normalized)) {
            icon = '<path d="M12 5v14"/><path d="M5 12h14"/>';
        } else if (normalized.includes('pdf') || normalized.includes('export')) {
            icon = '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/>';
        }

        if (!icon) {
            return;
        }

        button.innerHTML = `<svg class="erp-icon" width="16" height="16" aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">${icon}</svg><span>${escapeHtml(label)}</span>`;
    });
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

        const exportButtons = table.data('export') === false ? [] : [
            { extend: 'copy', text: buttonText('copy', 'Copy'), className: 'erp-dt-export-button' },
            { extend: 'csv', text: buttonText('csv', 'CSV'), className: 'erp-dt-export-button' },
            { extend: 'excel', text: buttonText('excel', 'Excel'), className: 'erp-dt-export-button' },
            { extend: 'print', text: buttonText('print', 'Print'), className: 'erp-dt-export-button' },
        ];

        let dataTableContainer = null;
        let refreshButton = null;
        table.on('processing.dt', (event, settings, processing) => {
            dataTableContainer?.toggleClass('erp-datatable-processing', processing);

            if (!processing) {
                refreshButton?.prop('disabled', false).removeClass('is-refreshing');
            }
        });

        const options = {
            responsive: true,
            autoWidth: false,
            deferRender: true,
            pageLength: Number(table.data('pageLength') || 15),
            lengthMenu: [10, 15, 25, 50, 100],
            order: [],
            language: {
                search: '',
                searchPlaceholder: table.data('searchPlaceholder') || 'Search records...',
                emptyTable: table.data('empty') || 'No records found.',
                zeroRecords: 'No matching records found.',
                lengthMenu: '_MENU_ rows',
                info: 'Showing _START_ to _END_ of _TOTAL_ records',
                infoEmpty: 'No records to show',
                infoFiltered: '(filtered from _MAX_ total)',
                processing: '<div class="erp-table-loading"><span class="erp-spinner"></span><span>Loading records...</span></div>',
                paginate: {
                    first: '&laquo;',
                    previous: '&lsaquo;',
                    next: '&rsaquo;',
                    last: '&raquo;',
                },
            },
            dom: "<'erp-dt-toolbar'<'erp-dt-tools-left'lB><'erp-dt-tools-right'f<'erp-dt-refresh-slot'>>>rt<'erp-dt-footer'ip>",
            buttons: exportButtons,
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
        dataTableContainer = window.$(instance.table().container()).addClass('erp-datatable-container');
        dataTableContainer.append(`
            <div class="erp-datatable-loader" aria-hidden="true">
                <div class="erp-datatable-loader-card">
                    <span class="erp-spinner"></span>
                    <span>Loading records...</span>
                </div>
            </div>
        `);

        const refreshSlot = dataTableContainer.find('.erp-dt-refresh-slot');
        if (refreshSlot.length) {
            refreshButton = window.$(`
                <button type="button" class="erp-dt-refresh-button" title="Refresh table" aria-label="Refresh table">
                    ${iconMarkup('refresh')}<span>Refresh</span>
                </button>
            `);

            refreshButton.on('click', () => {
                refreshButton.prop('disabled', true).addClass('is-refreshing');

                const done = () => {
                    refreshButton.prop('disabled', false).removeClass('is-refreshing');
                };

                if (instance.ajax?.reload) {
                    instance.ajax.reload(done, false);
                } else {
                    instance.draw(false);
                    done();
                }
            });

            refreshSlot.append(refreshButton);
        }

        if (filterSelector) {
            const filterForm = document.querySelector(filterSelector);

            if (!filterForm) {
                return;
            }

            prepareFilterForm(filterForm, { ensureDates: true });
            attachAdvancedTableSearch(filterForm, instance, table.data('searchPlaceholder') || 'Search records...');
            dataTableContainer.addClass('erp-has-advanced-search');
            filterForm.addEventListener('submit', (event) => {
                event.preventDefault();
                updateAdvancedSearchState(filterForm);
                instance.ajax.reload();
            });
            filterForm.querySelectorAll('select, input[type="date"], input[type="month"]').forEach((input) => {
                input.addEventListener('change', () => {
                    updateAdvancedSearchState(filterForm);
                    instance.ajax.reload();
                });
            });
            filterForm.querySelector('[data-reset-filters]')?.addEventListener('click', () => {
                filterForm.reset();
                window.$?.(filterForm).find('select[data-searchable], select.js-searchable').trigger('change.select2');
                const tableSearch = filterForm.querySelector('[data-table-search]');

                if (tableSearch) {
                    tableSearch.value = '';
                    instance.search('');
                }

                updateAdvancedSearchState(filterForm);
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
    initializeFilterForms();
    initializeButtonIcons();
    initializeDataTables();
    initializeDashboardCharts();
});

document.addEventListener('erp:refresh-selects', initializeSelects);
