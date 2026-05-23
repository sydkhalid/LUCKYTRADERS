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

function collectNumericValues(value) {
    if (Array.isArray(value)) {
        return value.flatMap((item) => collectNumericValues(item));
    }

    if (value && typeof value === 'object') {
        return Object.entries(value)
            .filter(([key]) => key !== 'labels')
            .flatMap(([, item]) => collectNumericValues(item));
    }

    const number = Number(value);

    return Number.isFinite(number) ? [number] : [];
}

function chartIsEmpty(charts, key) {
    return collectNumericValues(charts[key] || {}).reduce((total, value) => total + Math.abs(value), 0) <= 0;
}

function cssVariable(name, fallback = '') {
    return getComputedStyle(document.documentElement).getPropertyValue(name).trim() || fallback;
}

function chartColors() {
    const primary = window.erpSettings?.theme?.color || cssVariable('--lt-primary', '#696cff');

    return {
        primary,
        success: '#71dd37',
        info: '#03c3ec',
        warning: '#ffab00',
        danger: '#ff3e1d',
        secondary: '#8592a3',
        purple: '#7367f0',
        violet: '#8c57ff',
        teal: '#28dac6',
        cyan: '#00cfe8',
        blue: '#2f8be6',
        yellow: '#ffdd00',
        orange: '#ff9f43',
        dark: '#566a7f',
        text: cssVariable('--lt-text', '#697a8d'),
        heading: cssVariable('--lt-heading', '#384551'),
        surface: '#ffffff',
        grid: 'rgba(67, 89, 113, 0.12)',
        softGrid: 'rgba(67, 89, 113, 0.08)',
    };
}

function withAlpha(color, alpha = 0.18) {
    const hex = String(color || '').replace('#', '');

    if (/^[0-9a-f]{3}$/i.test(hex)) {
        const expanded = hex.split('').map((char) => char + char).join('');
        return withAlpha(`#${expanded}`, alpha);
    }

    if (/^[0-9a-f]{6}$/i.test(hex)) {
        const value = parseInt(hex, 16);
        const red = (value >> 16) & 255;
        const green = (value >> 8) & 255;
        const blue = value & 255;

        return `rgba(${red}, ${green}, ${blue}, ${alpha})`;
    }

    return color;
}

function moneyFormatter(value) {
    const symbol = window.erpSettings?.currency?.symbol || '₹';

    return `${symbol} ${Number(value || 0).toLocaleString('en-IN', {
        maximumFractionDigits: 2,
    })}`;
}

function quantityFormatter(value) {
    return Number(value || 0).toLocaleString('en-IN', {
        maximumFractionDigits: 3,
    });
}

function apexBaseOptions(height = 285) {
    const colors = chartColors();
    const isDark = document.documentElement.dataset.bsTheme === 'dark';

    return {
        chart: {
            height,
            fontFamily: 'Public Sans, Inter, sans-serif',
            foreColor: colors.text,
            toolbar: { show: false },
            zoom: { enabled: false },
            parentHeightOffset: 0,
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 650,
                animateGradually: { enabled: true, delay: 85 },
                dynamicAnimation: { enabled: true, speed: 320 },
            },
        },
        colors: [colors.primary, colors.teal, colors.yellow, colors.orange, colors.info, colors.purple, colors.success, colors.danger],
        dataLabels: { enabled: false },
        grid: {
            borderColor: colors.grid,
            strokeDashArray: 0,
            padding: { left: 8, right: 12, top: -6, bottom: 0 },
        },
        legend: {
            position: 'top',
            horizontalAlign: 'left',
            fontSize: '12px',
            fontWeight: 400,
            labels: { colors: colors.text },
            markers: { width: 8, height: 8, radius: 8, offsetX: -2 },
            itemMargin: { horizontal: 12, vertical: 6 },
        },
        stroke: {
            width: 3,
            curve: 'smooth',
            lineCap: 'round',
        },
        states: {
            hover: { filter: { type: 'lighten', value: 0.04 } },
            active: { filter: { type: 'none' } },
        },
        tooltip: {
            theme: isDark ? 'dark' : 'light',
            style: { fontSize: '12px', fontFamily: 'Public Sans, Inter, sans-serif' },
            y: {
                formatter: (value) => moneyFormatter(value),
            },
        },
        xaxis: {
            labels: {
                style: { colors: colors.secondary, fontSize: '11px', fontWeight: 400 },
                trim: true,
            },
            axisBorder: { show: false },
            axisTicks: { show: false },
            tooltip: { enabled: false },
        },
        yaxis: {
            min: 0,
            labels: {
                style: { colors: colors.secondary, fontSize: '11px', fontWeight: 400 },
                formatter: (value) => Number(value || 0).toLocaleString('en-IN', { notation: 'compact', maximumFractionDigits: 1 }),
            },
        },
        responsive: [{
            breakpoint: 768,
            options: {
                chart: { height: Math.max(height - 30, 230) },
                legend: { position: 'bottom', horizontalAlign: 'center' },
            },
        }],
    };
}

function apexChartConfig(key, charts, element) {
    const colors = chartColors();
    const dataset = charts[key] || {};
    const chartSize = element.closest('[data-chart-size]')?.dataset.chartSize || 'compact';
    const height = chartSize === 'showcase' ? 312 : chartSize === 'wide' ? 296 : 250;
    const base = apexBaseOptions(height);

    const configs = {
        stacked_business_flow: {
            ...base,
            chart: { ...base.chart, type: 'area', stacked: true },
            colors: [colors.teal, colors.info, colors.success, colors.purple],
            series: [
                { name: 'Sales', data: dataset.sales || [] },
                { name: 'Collection', data: dataset.collections || [] },
                { name: 'Purchases', data: dataset.purchases || [] },
                { name: 'Expenses', data: dataset.expenses || [] },
            ],
            xaxis: { ...base.xaxis, categories: dataset.labels || [] },
            stroke: { ...base.stroke, width: 2.5 },
            markers: { size: 0, hover: { size: 5 } },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 0.35,
                    opacityFrom: 0.82,
                    opacityTo: 0.28,
                    stops: [0, 70, 100],
                },
            },
        },
        sales_vs_purchases: {
            ...base,
            chart: { ...base.chart, type: 'bar' },
            colors: [colors.purple, '#efb9ff'],
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    borderRadiusApplication: 'end',
                    columnWidth: '24%',
                },
            },
            series: [
                { name: 'Sales', data: dataset.sales || [] },
                { name: 'Purchases', data: dataset.purchases || [] },
            ],
            xaxis: { ...base.xaxis, categories: dataset.labels || [] },
            fill: {
                type: 'gradient',
                gradient: { shade: 'light', type: 'vertical', opacityFrom: 0.95, opacityTo: 0.72 },
            },
        },
        monthly_sales: {
            ...base,
            chart: { ...base.chart, type: 'bar', height },
            colors: [colors.teal],
            plotOptions: {
                bar: {
                    borderRadius: 6,
                    borderRadiusApplication: 'end',
                    columnWidth: '36%',
                },
            },
            series: [{ name: 'Sales', data: dataset.data || [] }],
            xaxis: { ...base.xaxis, categories: dataset.labels || [] },
            fill: {
                type: 'gradient',
                gradient: { shade: 'light', type: 'vertical', opacityFrom: 0.95, opacityTo: 0.72 },
            },
        },
        monthly_purchases: {
            ...base,
            chart: { ...base.chart, type: 'line', height },
            colors: [colors.warning],
            series: [{ name: 'Purchases', data: dataset.data || [] }],
            xaxis: { ...base.xaxis, categories: dataset.labels || [] },
            stroke: { ...base.stroke, width: 4 },
            markers: {
                size: 0,
                strokeWidth: 4,
                strokeColors: '#fff',
                hover: { size: 6 },
            },
        },
        gst_split: {
            ...base,
            chart: { ...base.chart, type: 'donut', height },
            colors: [colors.yellow, colors.info, colors.purple, colors.teal],
            labels: dataset.labels || [],
            series: dataset.data || [],
            stroke: { width: 0 },
            plotOptions: {
                pie: {
                    donut: {
                        size: '72%',
                        labels: {
                            show: true,
                            name: { show: true, fontSize: '12px', fontWeight: 400, color: colors.secondary },
                            value: { show: true, fontSize: '15px', fontWeight: 600, color: colors.heading, formatter: (value) => moneyFormatter(value) },
                            total: {
                                show: true,
                                label: 'Total',
                                color: colors.secondary,
                                formatter: (context) => moneyFormatter(context.globals.seriesTotals.reduce((total, value) => total + value, 0)),
                            },
                        },
                    },
                },
            },
            tooltip: { ...base.tooltip, y: { formatter: (value) => moneyFormatter(value) } },
        },
        top_products: {
            ...base,
            chart: { ...base.chart, type: 'bar' },
            colors: [colors.cyan],
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 6,
                    borderRadiusApplication: 'end',
                    barHeight: '42%',
                },
            },
            series: [{ name: 'Sold Quantity', data: dataset.data || [] }],
            xaxis: { ...base.xaxis, categories: dataset.labels || [] },
            yaxis: {
                ...base.yaxis,
                labels: { ...base.yaxis.labels, maxWidth: 148 },
            },
            tooltip: { ...base.tooltip, y: { formatter: (value) => quantityFormatter(value) } },
        },
        stock_value: {
            ...base,
            chart: { ...base.chart, type: 'bar', height },
            colors: [colors.success],
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 6,
                    borderRadiusApplication: 'end',
                    barHeight: '42%',
                },
            },
            series: [{ name: 'Stock Value', data: dataset.data || [] }],
            xaxis: { ...base.xaxis, categories: dataset.labels || [] },
            yaxis: {
                ...base.yaxis,
                labels: { ...base.yaxis.labels, maxWidth: 148 },
            },
        },
        expense_categories: {
            ...base,
            chart: { ...base.chart, type: 'bar', height },
            colors: [colors.orange],
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 6,
                    borderRadiusApplication: 'end',
                    barHeight: '42%',
                },
            },
            series: [{ name: 'Expenses', data: dataset.data || [] }],
            xaxis: { ...base.xaxis, categories: dataset.labels || [] },
            yaxis: {
                ...base.yaxis,
                labels: { ...base.yaxis.labels, maxWidth: 132 },
            },
        },
    };

    return configs[key];
}

const centerTextPlugin = {
    id: 'ltCenterText',
    afterDraw(chart, _args, options) {
        if (!options?.text) {
            return;
        }

        const { ctx, chartArea } = chart;
        if (!chartArea) {
            return;
        }

        const centerX = (chartArea.left + chartArea.right) / 2;
        const centerY = (chartArea.top + chartArea.bottom) / 2;
        ctx.save();
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillStyle = options.color || chartColors().heading;
        ctx.font = '500 15px "Public Sans", Inter, sans-serif';
        ctx.fillText(options.text, centerX, centerY - 7);

        if (options.subtext) {
            ctx.fillStyle = options.subColor || chartColors().text;
            ctx.font = '400 12px "Public Sans", Inter, sans-serif';
            ctx.fillText(options.subtext, centerX, centerY + 14);
        }

        ctx.restore();
    },
};

function chartJsBaseOptions({ circular = false, legendPosition = 'bottom' } = {}) {
    const colors = chartColors();

    return {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
            duration: 780,
            easing: 'easeOutQuart',
        },
        interaction: {
            intersect: false,
            mode: 'nearest',
        },
        plugins: {
            legend: {
                position: legendPosition,
                labels: {
                    boxWidth: 9,
                    boxHeight: 9,
                    usePointStyle: true,
                    pointStyle: 'circle',
                    color: colors.text,
                    font: { weight: '400', size: 11 },
                    padding: 16,
                },
            },
            tooltip: {
                backgroundColor: document.documentElement.dataset.bsTheme === 'dark' ? '#2b2c40' : '#fff',
                bodyColor: colors.heading,
                borderColor: colors.softGrid,
                borderWidth: 1,
                titleColor: colors.text,
                padding: 12,
                cornerRadius: 8,
                displayColors: true,
                callbacks: {
                    label: (context) => {
                        const label = context.label || context.dataset.label || '';
                        const value = context.parsed?.r ?? context.parsed ?? context.raw ?? 0;

                        return `${label}: ${moneyFormatter(value)}`;
                    },
                },
            },
        },
        scales: circular
            ? {
                r: {
                    beginAtZero: true,
                    grid: { color: colors.grid },
                    angleLines: { color: colors.softGrid },
                    pointLabels: { color: colors.text, font: { weight: '400', size: 11 } },
                    ticks: {
                        color: colors.secondary,
                        backdropColor: 'transparent',
                        callback: (value) => Number(value || 0).toLocaleString('en-IN', { notation: 'compact' }),
                    },
                },
            }
            : {},
    };
}

function chartJsConfig(key, charts) {
    const colors = chartColors();
    const dataset = charts[key] || {};
    const palette = [colors.purple, colors.yellow, colors.orange, colors.blue, colors.teal, colors.success, colors.info, colors.danger];
    const cashTotal = (charts.cash_flow?.data || []).reduce((total, value) => total + Number(value || 0), 0);

    const configs = {
        cash_flow: {
            type: 'doughnut',
            data: {
                labels: dataset.labels || [],
                datasets: [{
                    data: dataset.data || [],
                    backgroundColor: [colors.purple, colors.info, colors.yellow, colors.orange],
                    borderColor: '#fff',
                    borderWidth: 4,
                    hoverOffset: 10,
                    spacing: 2,
                }],
            },
            plugins: [centerTextPlugin],
            options: {
                ...chartJsBaseOptions(),
                cutout: '68%',
                plugins: {
                    ...chartJsBaseOptions().plugins,
                    ltCenterText: {
                        text: 'Cash Flow',
                        subtext: moneyFormatter(cashTotal),
                    },
                },
            },
        },
        period_business_mix: {
            type: 'pie',
            data: {
                labels: dataset.labels || [],
                datasets: [{
                    data: dataset.data || [],
                    backgroundColor: [colors.yellow, colors.purple, colors.blue, colors.teal],
                    borderColor: '#fff',
                    borderWidth: 4,
                    hoverOffset: 10,
                    spacing: 1,
                }],
            },
            options: chartJsBaseOptions(),
        },
        pending_payments: {
            type: 'polarArea',
            data: {
                labels: dataset.labels || [],
                datasets: [{
                    data: dataset.data || [],
                    backgroundColor: palette.map((color) => withAlpha(color, 0.82)),
                    borderColor: '#fff',
                    borderWidth: 3,
                }],
            },
            options: {
                ...chartJsBaseOptions({ circular: true }),
                scales: {
                    r: {
                        beginAtZero: true,
                        grid: { color: colors.grid },
                        angleLines: { color: colors.softGrid },
                        ticks: { display: false },
                    },
                },
            },
        },
        profit_vs_expense: {
            type: 'radar',
            data: {
                labels: dataset.labels || [],
                datasets: [{
                    label: 'Amount',
                    data: dataset.data || [],
                    borderColor: colors.purple,
                    backgroundColor: withAlpha(colors.purple, 0.32),
                    pointBackgroundColor: [colors.success, colors.danger, colors.purple],
                    pointBorderColor: '#fff',
                    pointHoverRadius: 7,
                    borderWidth: 3,
                    tension: 0.24,
                }],
            },
            options: chartJsBaseOptions({ circular: true }),
        },
    };

    return configs[key];
}

function destroyChart(instance) {
    if (!instance) {
        return;
    }

    if (instance.engine === 'apex') {
        instance.chart.destroy();
        return;
    }

    instance.chart.destroy();
}

function initializeDashboard(root) {
    if (!window.Chart && !window.ApexCharts) {
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

        root.querySelectorAll('[data-dashboard-chart]').forEach((element) => {
            const key = element.dataset.dashboardChart;
            const engine = element.dataset.dashboardChartEngine || 'chartjs';
            const empty = chartIsEmpty(charts, key);
            const emptyState = root.querySelector(`[data-chart-empty="${key}"]`);

            element.classList.toggle('hidden', empty);
            emptyState?.classList.toggle('hidden', !empty);
            destroyChart(chartInstances[key]);
            delete chartInstances[key];

            if (empty) {
                return;
            }

            if (engine === 'apex') {
                if (!window.ApexCharts) {
                    return;
                }

                element.innerHTML = '';
                const config = apexChartConfig(key, charts, element);
                if (!config) {
                    return;
                }

                const chart = new window.ApexCharts(element, config);
                chart.render();
                chartInstances[key] = { engine, chart };
                return;
            }

            if (!window.Chart) {
                return;
            }

            const config = chartJsConfig(key, charts);
            if (!config) {
                return;
            }

            chartInstances[key] = {
                engine,
                chart: new window.Chart(element, config),
            };
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
