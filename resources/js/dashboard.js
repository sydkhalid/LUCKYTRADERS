function parseJsonScript(root, selector, fallback = {}) {
    const element = root.querySelector(selector);

    if (!element) {
        return fallback;
    }

    try {
        return JSON.parse(element.textContent || '{}');
    } catch {
        return fallback;
    }
}

function sumValues(values = []) {
    return values.reduce((total, value) => total + Math.abs(Number(value || 0)), 0);
}

function datasetValues(charts, key) {
    const dataset = charts[key] || {};

    if (key === 'sales_vs_purchases') {
        return [...(dataset.sales || []), ...(dataset.purchases || [])];
    }

    return dataset.data || [];
}

function chartIsEmpty(charts, key) {
    return sumValues(datasetValues(charts, key)) <= 0;
}

function chartColors() {
    return {
        primary: '#696cff',
        success: '#71dd37',
        info: '#03c3ec',
        warning: '#ffab00',
        danger: '#ff3e1d',
        secondary: '#8592a3',
        purple: '#8c57ff',
        teal: '#00b8a9',
    };
}

function baseChartOptions() {
    return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                labels: {
                    boxWidth: 12,
                    color: '#697a8d',
                    font: { weight: '600' },
                },
            },
        },
        scales: {
            x: {
                grid: { color: 'rgba(67, 89, 113, 0.08)' },
                ticks: { color: '#697a8d' },
            },
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(67, 89, 113, 0.08)' },
                ticks: { color: '#697a8d' },
            },
        },
    };
}

function chartConfig(key, charts) {
    const colors = chartColors();
    const common = baseChartOptions();

    const configs = {
        monthly_sales: {
            type: 'line',
            data: {
                labels: charts.monthly_sales?.labels || [],
                datasets: [{
                    label: 'Sales',
                    data: charts.monthly_sales?.data || [],
                    borderColor: colors.success,
                    backgroundColor: 'rgba(113, 221, 55, 0.14)',
                    tension: 0.35,
                    fill: true,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                }],
            },
            options: common,
        },
        monthly_purchases: {
            type: 'line',
            data: {
                labels: charts.monthly_purchases?.labels || [],
                datasets: [{
                    label: 'Purchases',
                    data: charts.monthly_purchases?.data || [],
                    borderColor: colors.danger,
                    backgroundColor: 'rgba(255, 62, 29, 0.14)',
                    tension: 0.35,
                    fill: true,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                }],
            },
            options: common,
        },
        sales_vs_purchases: {
            type: 'bar',
            data: {
                labels: charts.sales_vs_purchases?.labels || [],
                datasets: [
                    {
                        label: 'Sales',
                        data: charts.sales_vs_purchases?.sales || [],
                        backgroundColor: colors.primary,
                        borderRadius: 10,
                    },
                    {
                        label: 'Purchases',
                        data: charts.sales_vs_purchases?.purchases || [],
                        backgroundColor: colors.warning,
                        borderRadius: 10,
                    },
                ],
            },
            options: common,
        },
        gst_split: {
            type: 'doughnut',
            data: {
                labels: charts.gst_split?.labels || [],
                datasets: [{
                    data: charts.gst_split?.data || [],
                    backgroundColor: [colors.info, colors.secondary],
                    borderWidth: 0,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, color: '#697a8d', font: { weight: '600' } } } },
            },
        },
        cash_flow: {
            type: 'bar',
            data: {
                labels: charts.cash_flow?.labels || [],
                datasets: [{
                    label: 'Amount',
                    data: charts.cash_flow?.data || [],
                    backgroundColor: [colors.success, colors.danger, colors.info, colors.warning],
                    borderRadius: 10,
                }],
            },
            options: common,
        },
        top_products: {
            type: 'bar',
            data: {
                labels: charts.top_products?.labels || [],
                datasets: [{
                    label: 'Sold Quantity',
                    data: charts.top_products?.data || [],
                    backgroundColor: colors.info,
                    borderRadius: 10,
                }],
            },
            options: { ...common, indexAxis: 'y' },
        },
        expense_categories: {
            type: 'doughnut',
            data: {
                labels: charts.expense_categories?.labels || [],
                datasets: [{
                    data: charts.expense_categories?.data || [],
                    backgroundColor: [colors.primary, colors.info, colors.success, colors.warning, colors.danger, colors.purple, colors.teal, colors.secondary],
                    borderWidth: 0,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, color: '#697a8d', font: { weight: '600' } } } },
            },
        },
        stock_value: {
            type: 'bar',
            data: {
                labels: charts.stock_value?.labels || [],
                datasets: [{
                    label: 'Stock Value',
                    data: charts.stock_value?.data || [],
                    backgroundColor: colors.primary,
                    borderRadius: 10,
                }],
            },
            options: { ...common, indexAxis: 'y' },
        },
        pending_payments: {
            type: 'bar',
            data: {
                labels: charts.pending_payments?.labels || [],
                datasets: [{
                    label: 'Pending',
                    data: charts.pending_payments?.data || [],
                    backgroundColor: [colors.warning, colors.danger, colors.primary],
                    borderRadius: 10,
                }],
            },
            options: common,
        },
        period_business_mix: {
            type: 'bar',
            data: {
                labels: charts.period_business_mix?.labels || [],
                datasets: [{
                    label: 'Amount',
                    data: charts.period_business_mix?.data || [],
                    backgroundColor: [colors.primary, colors.warning, colors.info, colors.danger],
                    borderRadius: 10,
                }],
            },
            options: common,
        },
        profit_vs_expense: {
            type: 'bar',
            data: {
                labels: charts.profit_vs_expense?.labels || [],
                datasets: [{
                    label: 'Amount',
                    data: charts.profit_vs_expense?.data || [],
                    backgroundColor: [colors.success, colors.danger, colors.primary],
                    borderRadius: 10,
                }],
            },
            options: common,
        },
        stock_units_by_category: {
            type: 'bar',
            data: {
                labels: charts.stock_units_by_category?.labels || [],
                datasets: [{
                    label: 'Stock Units',
                    data: charts.stock_units_by_category?.data || [],
                    backgroundColor: colors.success,
                    borderRadius: 10,
                }],
            },
            options: { ...common, indexAxis: 'y' },
        },
    };

    return configs[key];
}

function initializeDashboard(root) {
    if (!window.Chart) {
        return;
    }

    const form = root.querySelector('[data-dashboard-filter]');
    const status = root.querySelector('[data-dashboard-status]');
    const submitButton = root.querySelector('[data-dashboard-submit]');
    const resetButton = root.querySelector('[data-dashboard-reset]');
    const periodInput = root.querySelector('[data-dashboard-period]');
    const dateInputs = root.querySelectorAll('[data-dashboard-date]');
    const chartInstances = {};
    let charts = parseJsonScript(root, '[data-dashboard-initial-charts]');
    let activeRequest = null;
    let lastQuery = new URLSearchParams(new FormData(form)).toString();
    let debounceTimer = null;

    const setStatus = (message) => {
        if (status) {
            status.textContent = message;
        }
    };

    const setLoading = (loading) => {
        root.classList.toggle('is-refreshing', loading);
        submitButton?.toggleAttribute('disabled', loading);
    };

    const renderCharts = (nextCharts) => {
        charts = nextCharts || charts;

        root.querySelectorAll('[data-dashboard-chart]').forEach((canvas) => {
            const key = canvas.dataset.dashboardChart;
            const empty = chartIsEmpty(charts, key);
            const emptyState = root.querySelector(`[data-chart-empty="${key}"]`);

            canvas.classList.toggle('hidden', empty);
            emptyState?.classList.toggle('hidden', !empty);

            if (chartInstances[key]) {
                chartInstances[key].destroy();
                delete chartInstances[key];
            }

            if (empty) {
                return;
            }

            chartInstances[key] = new window.Chart(canvas, chartConfig(key, charts));
        });
    };

    const syncFilterInputs = (filters) => {
        if (!filters) {
            return;
        }

        if (periodInput) {
            periodInput.value = filters.period;
        }

        const fromDate = form.querySelector('[name="from_date"]');
        const toDate = form.querySelector('[name="to_date"]');
        if (fromDate) {
            fromDate.value = filters.from_date;
        }
        if (toDate) {
            toDate.value = filters.to_date;
        }

        root.querySelectorAll('[data-dashboard-range-label]').forEach((element) => {
            element.textContent = filters.label;
        });
    };

    const replaceSection = (selector, html) => {
        const section = root.querySelector(selector);
        if (section && typeof html === 'string') {
            section.innerHTML = html;
        }
    };

    const queryFromFilters = (filters) => {
        const params = new URLSearchParams();
        params.set('period', filters?.period || 'this_month');
        params.set('from_date', filters?.from_date || '');
        params.set('to_date', filters?.to_date || '');

        return params.toString();
    };

    const updateUrl = (filters) => {
        const pageUrl = new URL(root.dataset.dashboardPageUrl, window.location.origin);
        const params = new URLSearchParams(queryFromFilters(filters));

        params.forEach((value, key) => {
            if (value !== '') {
                pageUrl.searchParams.set(key, value);
            }
        });

        window.history.replaceState(null, '', pageUrl.toString());
    };

    const loadDashboard = async ({ force = false } = {}) => {
        const query = new URLSearchParams(new FormData(form)).toString();

        if (!force && query === lastQuery) {
            return;
        }

        lastQuery = query;

        if (activeRequest) {
            activeRequest.abort();
        }

        activeRequest = new AbortController();
        setLoading(true);
        setStatus('Updating dashboard...');

        const url = new URL(root.dataset.dashboardDataUrl, window.location.origin);
        new URLSearchParams(query).forEach((value, key) => url.searchParams.set(key, value));

        try {
            const response = await fetch(url.toString(), {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: activeRequest.signal,
            });

            if (!response.ok) {
                throw new Error('Dashboard refresh failed');
            }

            const payload = await response.json();
            replaceSection('[data-dashboard-cards]', payload.cards_html);
            replaceSection('[data-dashboard-widgets]', payload.widgets_html);
            replaceSection('[data-dashboard-tables]', payload.tables_html);
            syncFilterInputs(payload.filters);
            renderCharts(payload.charts);
            lastQuery = queryFromFilters(payload.filters);
            updateUrl(payload.filters);
            setStatus('Updated without page reload');
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }

            setStatus('Unable to update dashboard');
            window.ErpToast?.modal?.('error', 'Dashboard filter failed', 'Unable to refresh dashboard data. Please check the selected date range.');
        } finally {
            setLoading(false);
            activeRequest = null;
        }
    };

    const queueLoad = () => {
        window.clearTimeout(debounceTimer);
        debounceTimer = window.setTimeout(() => loadDashboard(), 320);
    };

    form?.addEventListener('submit', (event) => {
        event.preventDefault();
        loadDashboard({ force: true });
    });

    periodInput?.addEventListener('change', queueLoad);
    dateInputs.forEach((input) => {
        input.addEventListener('change', () => {
            if (periodInput) {
                periodInput.value = 'custom';
            }

            queueLoad();
        });
    });

    resetButton?.addEventListener('click', (event) => {
        event.preventDefault();
        form.reset();
        if (periodInput) {
            periodInput.value = 'this_month';
        }
        loadDashboard({ force: true });
    });

    renderCharts(charts);
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-dashboard]').forEach(initializeDashboard);
});
