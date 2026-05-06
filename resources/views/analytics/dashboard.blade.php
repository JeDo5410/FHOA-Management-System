@extends('layouts.app')
@section('title', 'Analytics Dashboard — FHOA')

@section('content')
@php
    $isNgrok = str_contains(request()->getHost(), 'ngrok');
@endphp

{{-- Google Fonts: Sora (headings) + IBM Plex Sans (body) --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">

{{-- Analytics CSS --}}
<link rel="stylesheet" href="{{ $isNgrok ? secure_asset('assets/css/analytics.css') : asset('assets/css/analytics.css') }}">

<div class="analytics-page">

    {{-- ── Page Header ──────────────────────────────────────── --}}
    <div class="analytics-page-header">
        <h2 class="analytics-title">Financial Analytics</h2>
        <p class="analytics-subtitle">Current month performance overview &middot; Fortezza HOA</p>
        <p class="analytics-subtitle" style="margin-top: 0.25rem; font-size: 0.6rem; color: #7A7368;">
            <i class="bi bi-info-circle me-1"></i>
            Developer lots are excluded from all figures in this dashboard:
            {{ implode(', ', $developerLots) }}.
        </p>
    </div>

    {{-- ── KPI Filter Bar ───────────────────────────────────── --}}
    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap" id="kpi-filter-bar">
        <span style="font-size:0.82rem;font-weight:600;color:#7A7368;letter-spacing:0.03em;">FILTER PERIOD</span>
        <select id="filter-month" class="form-select form-select-sm" style="width:auto;">
            <option value="1">January</option><option value="2">February</option>
            <option value="3">March</option><option value="4">April</option>
            <option value="5">May</option><option value="6">June</option>
            <option value="7">July</option><option value="8">August</option>
            <option value="9">September</option><option value="10">October</option>
            <option value="11">November</option><option value="12">December</option>
        </select>
        <select id="filter-year" class="form-select form-select-sm" style="width:auto;"></select>
        <button id="filter-reset" class="btn btn-sm btn-outline-secondary">This Month</button>
        <small class="text-muted fst-italic">Active Members is always current and unaffected.</small>
    </div>

    {{-- ── KPI Cards ────────────────────────────────────────── --}}
    <div class="row g-3 analytics-section">

        <div class="col-sm-6 col-xl-3 kpi-card-col" style="animation-delay: 0.05s">
            <div class="kpi-card">
                <div class="kpi-card-label" id="label-collection">Total Collection (This Month)</div>
                <div class="kpi-card-value" id="kpi-collection">—</div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3 kpi-card-col" style="animation-delay: 0.15s">
            <div class="kpi-card" style="border-left-color: #C0392B;">
                <div class="kpi-card-label" id="label-expenses">Total Expenses (This Month)</div>
                <div class="kpi-card-value" id="kpi-expenses">—</div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3 kpi-card-col" style="animation-delay: 0.25s">
            <div class="kpi-card" style="border-left-color: #2E7D52;">
                <div class="kpi-card-label" id="label-collection-rate">Monthly Dues Collection Rate (This Month)</div>
                <div class="kpi-card-value" id="kpi-collection-rate">—</div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3 kpi-card-col" style="animation-delay: 0.35s">
            <div class="kpi-card" style="border-left-color: #1E4D8C;">
                <div class="kpi-card-label">Active Members</div>
                <div class="kpi-card-value" id="kpi-active-members">—</div>
            </div>
        </div>

    </div>

    {{-- ── Row 1: Monthly Trend Chart ──────────────────────── --}}
    <div class="row g-3 analytics-section">

        <div class="col-12 analytics-card-wrap" style="animation-delay: 0.1s">
            <div class="analytics-card">
                <div class="analytics-card-header">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <span class="analytics-card-title">Income vs. Expenses</span>
                        <div class="d-flex align-items-center gap-2">
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-primary active" id="mode-rolling">Last 12 Months</button>
                                <button type="button" class="btn btn-outline-primary" id="mode-year">Calendar Year</button>
                            </div>
                            <select id="chart-year-picker" class="form-select form-select-sm" style="width:auto; display:none;"></select>
                        </div>
                    </div>
                    <span class="analytics-card-subtitle" id="chart-subtitle">Last 12 months &middot; Philippine Peso (&#8369;)</span>
                </div>
                <div id="chart-container" style="position:relative;">
                    <canvas id="monthly-trend-chart"></canvas>
                </div>
            </div>
        </div>

    </div>

    {{-- ── Row 2: Arrear Aging + Permit Info ────────────────── --}}
    <div class="row g-3 analytics-section">

        {{-- Arrear Aging --}}
        <div class="col-lg-6 analytics-card-wrap" style="animation-delay: 0.2s">
            <div class="analytics-card">
                <div class="analytics-card-header">
                    <span class="analytics-card-title">Arrear Aging</span>
                    <span class="analytics-card-subtitle">Delinquent members by months overdue</span>
                </div>
                <div class="aging-section">

                    <div class="aging-row">
                        <div class="aging-label">3–6 months</div>
                        <div class="aging-count-badge" id="aging-count-1">—</div>
                        <div class="aging-bar-track">
                            <div class="aging-bar" id="aging-bar-1" style="width: 0%;">
                                <span class="aging-bar-text" id="aging-pct-1"></span>
                            </div>
                        </div>
                    </div>

                    <div class="aging-row">
                        <div class="aging-label">7–12 months</div>
                        <div class="aging-count-badge" id="aging-count-2">—</div>
                        <div class="aging-bar-track">
                            <div class="aging-bar" id="aging-bar-2" style="width: 0%;">
                                <span class="aging-bar-text" id="aging-pct-2"></span>
                            </div>
                        </div>
                    </div>

                    <div class="aging-row">
                        <div class="aging-label">13–24 months</div>
                        <div class="aging-count-badge" id="aging-count-3">—</div>
                        <div class="aging-bar-track">
                            <div class="aging-bar" id="aging-bar-3" style="width: 0%;">
                                <span class="aging-bar-text" id="aging-pct-3"></span>
                            </div>
                        </div>
                    </div>

                    <div class="aging-row">
                        <div class="aging-label">24+ months</div>
                        <div class="aging-count-badge" id="aging-count-4">—</div>
                        <div class="aging-bar-track">
                            <div class="aging-bar" id="aging-bar-4" style="width: 0%;">
                                <span class="aging-bar-text" id="aging-pct-4"></span>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 0.75rem; padding-top: 0.6rem; border-top: 1px solid #E8E3DA; display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 0.78rem; color: #7A7368; font-weight: 500;">Total delinquent</span>
                        <span id="aging-total" style="font-family: 'Cormorant Garamond', Georgia, serif; font-size: 1.1rem; font-weight: 700; color: #C0392B;">—</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Construction Permit Info Panel --}}
        <div class="col-lg-6 analytics-card-wrap" style="animation-delay: 0.3s">
            <div class="analytics-card">
                <div class="analytics-card-header">
                    <span class="analytics-card-title">Construction Permits</span>
                    <span class="analytics-card-subtitle">Current status breakdown</span>
                </div>
                <ul class="permit-stat-list" id="permit-stat-list">
                    <li><span class="permit-stat-label" style="color: #7A7368;">Loading…</span></li>
                </ul>
            </div>
        </div>

    </div>

</div>{{-- /.analytics-page --}}
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
<script>
Chart.register(ChartDataLabels);
document.addEventListener('DOMContentLoaded', function () {

    const fmt     = (n) => '₱' + parseFloat(n || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const setText = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
    const now     = new Date();
    const currentYear = now.getFullYear();

    // --- Populate year dropdowns (current year going back 5 years) ---
    const yearRange = Array.from({ length: 6 }, (_, i) => currentYear - i);
    ['filter-year', 'chart-year-picker'].forEach(id => {
        const sel = document.getElementById(id);
        if (!sel) return;
        yearRange.forEach(y => {
            const opt = document.createElement('option');
            opt.value = y;
            opt.textContent = y;
            if (y === currentYear) opt.selected = true;
            sel.appendChild(opt);
        });
    });

    // Set filter-month default to current month
    const filterMonth = document.getElementById('filter-month');
    const filterYear  = document.getElementById('filter-year');
    if (filterMonth) filterMonth.value = now.getMonth() + 1;

    // ── KPI Loader ────────────────────────────────────────────
    const MONTH_NAMES = ['January','February','March','April','May','June',
                         'July','August','September','October','November','December'];

    function loadKpis(month, year) {
        const period = MONTH_NAMES[month - 1] + ' ' + year;
        setText('label-collection',      'Total Collection (' + period + ')');
        setText('label-expenses',        'Total Expenses (' + period + ')');
        setText('label-collection-rate', 'Monthly Dues Collection Rate (' + period + ')');

        fetch('/analytics/data/kpis?month=' + month + '&year=' + year)
            .then(r => r.json())
            .then(data => {
                setText('kpi-collection',      fmt(data.total_collection));
                setText('kpi-expenses',        fmt(data.total_expenses));
                setText('kpi-active-members',  data.active_members);
                setText('kpi-collection-rate', data.collection_rate + '%');
            })
            .catch(() => console.error('KPI fetch failed'));
    }

    filterMonth?.addEventListener('change', () => loadKpis(filterMonth.value, filterYear.value));
    filterYear?.addEventListener('change',  () => loadKpis(filterMonth.value, filterYear.value));
    document.getElementById('filter-reset')?.addEventListener('click', () => {
        filterMonth.value = now.getMonth() + 1;
        filterYear.value  = currentYear;
        loadKpis(filterMonth.value, filterYear.value);
    });

    loadKpis(now.getMonth() + 1, currentYear); // initial load

    // ── Chart Loader ──────────────────────────────────────────
    let trendChart = null;

    function loadTrendChart(mode, year) {
        fetch('/analytics/data/monthly-trend?mode=' + mode + '&year=' + year)
            .then(r => r.json())
            .then(data => {
                if (trendChart) { trendChart.destroy(); trendChart = null; }
                const ctx = document.getElementById('monthly-trend-chart');
                if (!ctx) return;

                // In calendar-year mode for the current year, hide future months
                let labels   = data.labels;
                let income   = data.income;
                let expenses = data.expenses;
                if (mode === 'year' && parseInt(year) === currentYear) {
                    const upTo = now.getMonth() + 1; // 1-based, inclusive
                    labels   = labels.slice(0, upTo);
                    income   = income.slice(0, upTo);
                    expenses = expenses.slice(0, upTo);
                }

                const HEIGHT_PER_MONTH = 48; // px per month (covers 2 bars + gap)
                const CHART_OVERHEAD   =70; // legend + x-axis + padding
                document.getElementById('chart-container').style.height =
                    (labels.length * HEIGHT_PER_MONTH + CHART_OVERHEAD) + 'px';

                trendChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            { label: 'Income',   data: income,   backgroundColor: '#1E4D8C', borderRadius: 4 },
                            { label: 'Expenses', data: expenses, backgroundColor: '#C9923A', borderRadius: 4 }
                        ]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'top' },
                            datalabels: {
                                anchor: 'end',
                                align: 'end',
                                color: '#7A7368',
                                font: { size: 11 },
                                formatter: (value) => {
                                    if (!value) return '';
                                    if (value >= 1000000) return '₱' + (value / 1000000).toFixed(1) + 'M';
                                    return '₱' + Math.round(value / 1000) + 'k';
                                }
                            }
                        },
                        scales: {
                            x: { ticks: { callback: (v) => '₱' + v.toLocaleString('en-PH') } }
                        },
                        layout: { padding: { right: 48 } }
                    }
                });
                const subtitle = document.getElementById('chart-subtitle');
                if (subtitle) subtitle.textContent = mode === 'year'
                    ? 'Jan – Dec ' + year + ' · Philippine Peso (₱)'
                    : 'Last 12 months · Philippine Peso (₱)';
            })
            .catch(() => console.error('Monthly trend fetch failed'));
    }

    // Toggle button logic
    const modeRollingBtn  = document.getElementById('mode-rolling');
    const modeYearBtn     = document.getElementById('mode-year');
    const chartYearPicker = document.getElementById('chart-year-picker');

    function activateMode(mode) {
        const isYear = mode === 'year';
        modeYearBtn.classList.toggle('active',              isYear);
        modeYearBtn.classList.toggle('btn-primary',         isYear);
        modeYearBtn.classList.toggle('btn-outline-primary', !isYear);
        modeRollingBtn.classList.toggle('active',              !isYear);
        modeRollingBtn.classList.toggle('btn-primary',         !isYear);
        modeRollingBtn.classList.toggle('btn-outline-primary',  isYear);
        chartYearPicker.style.display = isYear ? '' : 'none';
        loadTrendChart(mode, chartYearPicker.value);
    }

    modeRollingBtn?.addEventListener('click',  () => activateMode('rolling'));
    modeYearBtn?.addEventListener('click',     () => activateMode('year'));
    chartYearPicker?.addEventListener('change', () => loadTrendChart('year', chartYearPicker.value));

    loadTrendChart('rolling', currentYear); // initial load

    // ── Arrear Aging (no filter — always current snapshot) ────
    const agingColors = ['#2E7D52', '#C9923A', '#E8A87C', '#C0392B'];

    fetch('/analytics/data/arrear-aging')
        .then(r => r.json())
        .then(data => {
            data.buckets.forEach((bucket, i) => {
                const n = i + 1;
                setText('aging-count-' + n, bucket.count + ' members');
                setText('aging-pct-'   + n, bucket.percent + '%');
                const bar = document.getElementById('aging-bar-' + n);
                if (bar) { bar.style.width = bucket.percent + '%'; bar.style.background = agingColors[i]; }
            });
            const totalEl = document.getElementById('aging-total');
            if (totalEl) totalEl.textContent = data.total_delinquent + ' members';
        })
        .catch(() => console.error('Arrear aging fetch failed'));

    // ── Permit Stats ──────────────────────────────────────────
    fetch('/analytics/data/permit-stats')
        .then(r => r.json())
        .then(data => {
            const ul = document.getElementById('permit-stat-list');
            if (!ul) return;
            ul.innerHTML = '';
            data.stats.forEach(s => {
                const li    = document.createElement('li');
                const label = document.createElement('span');
                const value = document.createElement('span');
                label.className   = 'permit-stat-label';
                value.className   = 'permit-stat-value';
                label.textContent = s.label;
                value.textContent = s.count;
                li.appendChild(label);
                li.appendChild(value);
                ul.appendChild(li);
            });
            const totalLi    = document.createElement('li');
            const totalLabel = document.createElement('span');
            const totalValue = document.createElement('span');
            const bold1      = document.createElement('strong');
            const bold2      = document.createElement('strong');
            totalLabel.className = 'permit-stat-label';
            totalValue.className = 'permit-stat-value';
            bold1.textContent    = 'Total';
            bold2.textContent    = data.total;
            totalLabel.appendChild(bold1);
            totalValue.appendChild(bold2);
            totalLi.appendChild(totalLabel);
            totalLi.appendChild(totalValue);
            ul.appendChild(totalLi);
        })
        .catch(() => console.error('Permit stats fetch failed'));

});
</script>
@endpush
