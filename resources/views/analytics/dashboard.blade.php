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
        <p class="analytics-subtitle">Year-to-date performance overview &middot; FY 2025 &middot; Fortezza HOA</p>
    </div>

    {{-- ── KPI Cards ────────────────────────────────────────── --}}
    <div class="row g-3 analytics-section">

        <div class="col-sm-6 col-xl-3 kpi-card-col" style="animation-delay: 0.05s">
            <div class="kpi-card">
                <div class="kpi-card-label">Total Collections YTD</div>
                <div class="kpi-card-value">&#8369;1,284,500</div>
                <div class="kpi-card-delta positive">
                    <i class="bi bi-arrow-up-short"></i> 12.4% vs last year
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3 kpi-card-col" style="animation-delay: 0.15s">
            <div class="kpi-card" style="border-left-color: #C0392B;">
                <div class="kpi-card-label">Outstanding Arrears</div>
                <div class="kpi-card-value">&#8369;387,250</div>
                <div class="kpi-card-delta negative">
                    <i class="bi bi-people"></i> 75 members affected
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3 kpi-card-col" style="animation-delay: 0.25s">
            <div class="kpi-card" style="border-left-color: #2E7D52;">
                <div class="kpi-card-label">Collection Rate (This Month)</div>
                <div class="kpi-card-value">87%</div>
                <div class="kpi-card-delta positive">
                    <i class="bi bi-arrow-up-short"></i> 4% vs last month
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3 kpi-card-col" style="animation-delay: 0.35s">
            <div class="kpi-card" style="border-left-color: #1E4D8C;">
                <div class="kpi-card-label">Active Members</div>
                <div class="kpi-card-value">412</div>
                <div class="kpi-card-delta neutral">
                    <i class="bi bi-people"></i> of 487 total members
                </div>
            </div>
        </div>

    </div>

    {{-- ── Row 1: Monthly Collections + Member Status ───────── --}}
    <div class="row g-3 analytics-section">

        <div class="col-lg-8 analytics-card-wrap" style="animation-delay: 0.1s">
            <div class="analytics-card">
                <div class="analytics-card-header">
                    <span class="analytics-card-title">Monthly Collections</span>
                    <span class="analytics-card-subtitle">Jan &ndash; Dec 2025 &middot; Philippine Peso (&#8369;)</span>
                </div>
                <div id="monthlyCollectionsChart"></div>
            </div>
        </div>

        <div class="col-lg-4 analytics-card-wrap" style="animation-delay: 0.2s">
            <div class="analytics-card">
                <div class="analytics-card-header">
                    <span class="analytics-card-title">Member Status</span>
                    <span class="analytics-card-subtitle">Current standing &middot; 487 total</span>
                </div>
                <div id="memberStatusChart"></div>
            </div>
        </div>

    </div>

    {{-- ── Row 2: Collection Rate Trend + Payment Method ────── --}}
    <div class="row g-3 analytics-section">

        <div class="col-lg-8 analytics-card-wrap" style="animation-delay: 0.15s">
            <div class="analytics-card">
                <div class="analytics-card-header">
                    <span class="analytics-card-title">Collection Rate Trend</span>
                    <span class="analytics-card-subtitle">Jan &ndash; Dec 2025 &middot; Percentage (%)</span>
                </div>
                <div id="collectionRateTrendChart"></div>
            </div>
        </div>

        <div class="col-lg-4 analytics-card-wrap" style="animation-delay: 0.25s">
            <div class="analytics-card">
                <div class="analytics-card-header">
                    <span class="analytics-card-title">Payment Method Split</span>
                    <span class="analytics-card-subtitle">YTD breakdown</span>
                </div>
                <div id="paymentMethodChart"></div>
            </div>
        </div>

    </div>

    {{-- ── Row 3: Arrear Aging + Permit Info ────────────────── --}}
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
                        <div class="aging-label">1 – 3 months</div>
                        <div class="aging-bar-track">
                            <div class="aging-bar" style="width: 100%; background: #E8A87C;">
                                <span class="aging-bar-text">38 members</span>
                            </div>
                        </div>
                        <div class="aging-count-badge">38</div>
                    </div>

                    <div class="aging-row">
                        <div class="aging-label">4 – 6 months</div>
                        <div class="aging-bar-track">
                            <div class="aging-bar" style="width: 58%; background: #C9923A;">
                                <span class="aging-bar-text">22 members</span>
                            </div>
                        </div>
                        <div class="aging-count-badge">22</div>
                    </div>

                    <div class="aging-row">
                        <div class="aging-label">7 – 12 months</div>
                        <div class="aging-bar-track">
                            <div class="aging-bar" style="width: 29%; background: #C0582B;">
                                <span class="aging-bar-text">11</span>
                            </div>
                        </div>
                        <div class="aging-count-badge">11</div>
                    </div>

                    <div class="aging-row">
                        <div class="aging-label">12+ months</div>
                        <div class="aging-bar-track">
                            <div class="aging-bar" style="width: 11%; background: #C0392B; min-width: 32px;">
                                <span class="aging-bar-text">4</span>
                            </div>
                        </div>
                        <div class="aging-count-badge">4</div>
                    </div>

                    <div style="margin-top: 0.75rem; padding-top: 0.6rem; border-top: 1px solid #E8E3DA; display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 0.78rem; color: #7A7368; font-weight: 500;">Total delinquent</span>
                        <span style="font-family: 'Cormorant Garamond', Georgia, serif; font-size: 1.1rem; font-weight: 700; color: #C0392B;">75 members</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Construction Permit Info Panel --}}
        <div class="col-lg-6 analytics-card-wrap" style="animation-delay: 0.3s">
            <div class="analytics-card">
                <div class="analytics-card-header">
                    <span class="analytics-card-title">Construction Permits</span>
                    <span class="analytics-card-subtitle">Current status &middot; YTD summary</span>
                </div>
                <ul class="permit-stat-list">
                    <li>
                        <span class="permit-stat-label">Active Permits</span>
                        <span class="badge-cobalt">12</span>
                    </li>
                    <li>
                        <span class="permit-stat-label">Pending Approval</span>
                        <span class="badge-gold">5</span>
                    </li>
                    <li>
                        <span class="permit-stat-label">Completed (YTD)</span>
                        <span class="badge-green">28</span>
                    </li>
                    <li>
                        <span class="permit-stat-label">Bonds Currently Held</span>
                        <span class="permit-stat-value">&#8369;340,000</span>
                    </li>
                    <li>
                        <span class="permit-stat-label">Bonds Released (YTD)</span>
                        <span class="permit-stat-value">&#8369;185,000</span>
                    </li>
                    <li>
                        <span class="permit-stat-label">Permit Fee Revenue (YTD)</span>
                        <span class="permit-stat-value">&#8369;62,500</span>
                    </li>
                </ul>
            </div>
        </div>

    </div>

</div>{{-- /.analytics-page --}}
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.54.0/dist/apexcharts.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const PALETTE = {
        cobalt:      '#1E4D8C',
        cobaltLight: '#4A7DC4',
        gold:        '#C9923A',
        green:       '#2E7D52',
        red:         '#C0392B',
        amber:       '#E8A87C',
        muted:       '#7A7368',
        gridLine:    '#EAE6DF'
    };

    const MONTHS = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

    function baseOptions() {
        return {
            chart: {
                fontFamily: "'IBM Plex Sans', 'Segoe UI', system-ui, sans-serif",
                toolbar:    { show: false },
                animations: { enabled: true, easing: 'easeinout', speed: 700 }
            },
            grid: {
                borderColor:    PALETTE.gridLine,
                strokeDashArray: 4,
                padding:        { left: 4, right: 4 }
            },
            tooltip: { theme: 'light' }
        };
    }

    // ── Chart 1: Monthly Collections Bar ──────────────────────
    (function () {
        const opts = Object.assign({}, baseOptions(), {
            chart: Object.assign({}, baseOptions().chart, {
                type:   'bar',
                height: 270
            }),
            series: [{
                name: 'Collections (₱)',
                data: [98000, 92000, 88000, 105000, 110000, 107000, 115000, 103000, 108000, 122000, 130000, 106500]
            }],
            xaxis: {
                categories: MONTHS,
                labels: {
                    style: { fontSize: '11px', colors: PALETTE.muted }
                },
                axisBorder: { show: false },
                axisTicks:  { show: false }
            },
            yaxis: {
                labels: {
                    formatter: v => '₱' + (v / 1000).toFixed(0) + 'k',
                    style: { fontSize: '11px', colors: PALETTE.muted }
                }
            },
            plotOptions: {
                bar: {
                    columnWidth: '52%',
                    borderRadius: 4,
                    dataLabels: { position: 'top' }
                }
            },
            dataLabels: { enabled: false },
            fill: { colors: [PALETTE.cobalt] },
            colors: [PALETTE.cobalt],
            states: {
                hover: { filter: { type: 'lighten', value: 0.1 } }
            },
            tooltip: {
                theme: 'light',
                y: { formatter: v => '₱' + v.toLocaleString() }
            }
        });

        new ApexCharts(document.querySelector('#monthlyCollectionsChart'), opts).render();
    })();

    // ── Chart 2: Member Status Donut ───────────────────────────
    (function () {
        const total  = 487;
        const active = 412;
        const delinq = 75;

        const opts = Object.assign({}, baseOptions(), {
            chart: Object.assign({}, baseOptions().chart, {
                type:   'donut',
                height: 270
            }),
            series: [active, delinq],
            labels: ['Active', 'Delinquent'],
            colors: [PALETTE.green, PALETTE.red],
            plotOptions: {
                pie: {
                    donut: {
                        size: '66%',
                        labels: {
                            show: true,
                            total: {
                                show:      true,
                                label:     'Total',
                                formatter: () => total
                            }
                        }
                    }
                }
            },
            dataLabels: { enabled: false },
            legend: {
                position: 'bottom',
                fontSize:  '12px',
                formatter: (label, opts) => {
                    const val = opts.w.globals.series[opts.seriesIndex];
                    const pct = ((val / total) * 100).toFixed(1);
                    return `${label}: ${val} (${pct}%)`;
                }
            },
            tooltip: {
                y: {
                    formatter: v => v + ' members (' + ((v / total) * 100).toFixed(1) + '%)'
                }
            }
        });

        new ApexCharts(document.querySelector('#memberStatusChart'), opts).render();
    })();

    // ── Chart 3: Collection Rate Trend Area ────────────────────
    (function () {
        const opts = Object.assign({}, baseOptions(), {
            chart: Object.assign({}, baseOptions().chart, {
                type:   'area',
                height: 270
            }),
            series: [{
                name: 'Collection Rate',
                data: [82, 78, 71, 80, 85, 83, 88, 84, 86, 90, 91, 87]
            }],
            xaxis: {
                categories: MONTHS,
                labels: {
                    style: { fontSize: '11px', colors: PALETTE.muted }
                },
                axisBorder: { show: false },
                axisTicks:  { show: false }
            },
            yaxis: {
                min: 60,
                max: 100,
                labels: {
                    formatter: v => v + '%',
                    style: { fontSize: '11px', colors: PALETTE.muted }
                }
            },
            stroke: {
                curve: 'smooth',
                width: 2.5,
                colors: [PALETTE.cobalt]
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom:    0.35,
                    opacityTo:      0.02,
                    stops:          [0, 95, 100],
                    colorStops: [{
                        offset: 0,
                        color:  PALETTE.cobalt,
                        opacity: 0.35
                    }, {
                        offset: 100,
                        color:  PALETTE.cobalt,
                        opacity: 0.02
                    }]
                }
            },
            colors: [PALETTE.cobalt],
            markers: {
                size: 3,
                colors: [PALETTE.cobalt],
                strokeWidth: 2,
                strokeColors: '#fff',
                hover: { size: 5 }
            },
            dataLabels: { enabled: false },
            tooltip: {
                theme: 'light',
                y: { formatter: v => v + '%' }
            }
        });

        new ApexCharts(document.querySelector('#collectionRateTrendChart'), opts).render();
    })();

    // ── Chart 4: Payment Method Donut ──────────────────────────
    (function () {
        const opts = Object.assign({}, baseOptions(), {
            chart: Object.assign({}, baseOptions().chart, {
                type:   'donut',
                height: 270
            }),
            series: [44, 28, 18, 10],
            labels: ['GCash', 'Cash', 'Bank Transfer', 'Check'],
            colors: [PALETTE.cobalt, PALETTE.gold, PALETTE.green, PALETTE.muted],
            plotOptions: {
                pie: {
                    donut: {
                        size: '66%',
                        labels: {
                            show: true,
                            total: {
                                show:      true,
                                label:     'Methods',
                                formatter: () => '4'
                            }
                        }
                    }
                }
            },
            dataLabels: { enabled: false },
            legend: {
                position: 'bottom',
                fontSize:  '12px',
                formatter: (label, opts) => {
                    return label + ': ' + opts.w.globals.series[opts.seriesIndex] + '%';
                }
            },
            tooltip: {
                y: { formatter: v => v + '% of collections' }
            }
        });

        new ApexCharts(document.querySelector('#paymentMethodChart'), opts).render();
    })();

});
</script>
@endpush
