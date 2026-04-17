@extends('layouts.main')

@push('head')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3"></script>
@endpush

@section('content')
<div class="container-fixed">

    {{-- ── Header ── --}}
    <div class="flex flex-wrap items-center justify-between gap-3 pb-5">
        <div>
            <h1 class="text-lg font-bold text-gray-900">{{ $data['pageTitle'] }}</h1>
            <p class="text-xs text-gray-500 mt-0.5">Ringkasan data Rencana Anggaran Biaya</p>
        </div>
        <div class="flex items-center gap-2">
            <a id="export-btn" href="#" class="btn btn-sm btn-success">
                <i class="ki-filled ki-file-down text-xs"></i>Export Excel
            </a>
            <a href="{{ route('rab.index') }}" class="btn btn-sm btn-light">
                <i class="ki-filled ki-document text-xs"></i>Daftar RAB
            </a>
        </div>
    </div>

    {{-- ── Filter Bar ── --}}
    <div class="card mb-5">
        <div class="card-body py-3 px-4">
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2">
                    <i class="ki-filled ki-filter text-gray-400 text-sm"></i>
                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Filter</span>
                </div>
                <div class="h-4 w-px bg-gray-200"></div>

                <div class="flex items-center gap-1.5">
                    <label class="text-xs text-gray-500 whitespace-nowrap">Tahun</label>
                    <select id="filter-tahun" class="select select-sm !h-7 !text-xs !py-0 w-24">
                        @foreach($data['availableYears'] as $year)
                            <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-1.5">
                    <label class="text-xs text-gray-500 whitespace-nowrap">Bulan</label>
                    <select id="filter-bulan" class="select select-sm !h-7 !text-xs !py-0 w-32">
                        <option value="">Semua</option>
                        @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $bln)
                            <option value="{{ $i + 1 }}">{{ $bln }}</option>
                        @endforeach
                    </select>
                </div>

                @auth
                    @hasanyrole('bendahara_umum|ketua_yayasan|super_admin')
                        <div class="flex items-center gap-1.5">
                            <label class="text-xs text-gray-500 whitespace-nowrap">Divisi</label>
                            <select id="filter-divisi" class="select select-sm !h-7 !text-xs !py-0 w-40">
                                <option value="">Semua Divisi</option>
                                @foreach($data['divisiList'] as $divisi)
                                    <option value="{{ $divisi }}">{{ $divisi }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endhasanyrole
                @endauth

                <div class="ms-auto flex items-center gap-1.5 text-xs text-gray-400" id="last-updated-wrap">
                    <i class="ki-filled ki-time text-xs"></i>
                    <span id="last-updated">—</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── KPI Strip ── --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-5">

        {{-- Total RAB --}}
        <div class="card border-t-2 border-t-primary">
            <div class="card-body p-4">
                <div class="flex items-start justify-between mb-2">
                    <span class="text-xs text-gray-500 font-medium">Total RAB</span>
                    <span class="size-7 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                        <i class="ki-filled ki-document text-primary text-xs"></i>
                    </span>
                </div>
                <div id="card-total-rab" class="text-2xl font-bold text-gray-900 leading-none">
                    <i class="ki-filled ki-arrows-circle animate-spin text-gray-300 text-base"></i>
                </div>
                <div class="text-xs text-gray-400 mt-1">dokumen</div>
            </div>
        </div>

        {{-- Total Kegiatan --}}
        <div class="card border-t-2 border-t-info">
            <div class="card-body p-4">
                <div class="flex items-start justify-between mb-2">
                    <span class="text-xs text-gray-500 font-medium">Kegiatan</span>
                    <span class="size-7 rounded-lg bg-info/10 flex items-center justify-center shrink-0">
                        <i class="ki-filled ki-calendar text-info text-xs"></i>
                    </span>
                </div>
                <div id="card-total-kegiatan" class="text-2xl font-bold text-gray-900 leading-none">
                    <i class="ki-filled ki-arrows-circle animate-spin text-gray-300 text-base"></i>
                </div>
                <div class="text-xs text-gray-400 mt-1">total item</div>
            </div>
        </div>

        {{-- Total Anggaran --}}
        <div class="card border-t-2 border-t-warning col-span-2 sm:col-span-1">
            <div class="card-body p-4">
                <div class="flex items-start justify-between mb-2">
                    <span class="text-xs text-gray-500 font-medium">Total Anggaran</span>
                    <span class="size-7 rounded-lg bg-warning/10 flex items-center justify-center shrink-0">
                        <i class="ki-filled ki-dollar text-warning text-xs"></i>
                    </span>
                </div>
                <div id="card-total-anggaran" class="text-base font-bold text-gray-900 leading-none">
                    <i class="ki-filled ki-arrows-circle animate-spin text-gray-300 text-base"></i>
                </div>
                <div class="text-xs text-gray-400 mt-1">rupiah</div>
            </div>
        </div>

        {{-- Disetujui --}}
        <div class="card border-t-2 border-t-success">
            <div class="card-body p-4">
                <div class="flex items-start justify-between mb-2">
                    <span class="text-xs text-gray-500 font-medium">Disetujui</span>
                    <span class="size-7 rounded-lg bg-success/10 flex items-center justify-center shrink-0">
                        <i class="ki-filled ki-check-circle text-success text-xs"></i>
                    </span>
                </div>
                <div id="card-total-approved" class="text-2xl font-bold text-gray-900 leading-none">
                    <i class="ki-filled ki-arrows-circle animate-spin text-gray-300 text-base"></i>
                </div>
                <div id="card-approved-pct" class="text-xs text-gray-400 mt-1">dari total</div>
            </div>
        </div>

        {{-- Pending --}}
        <div class="card border-t-2 border-t-danger">
            <div class="card-body p-4">
                <div class="flex items-start justify-between mb-2">
                    <span class="text-xs text-gray-500 font-medium">Pending</span>
                    <span class="size-7 rounded-lg bg-danger/10 flex items-center justify-center shrink-0">
                        <i class="ki-filled ki-time text-danger text-xs"></i>
                    </span>
                </div>
                <div id="card-total-pending" class="text-2xl font-bold text-gray-900 leading-none">
                    <i class="ki-filled ki-arrows-circle animate-spin text-gray-300 text-base"></i>
                </div>
                <div id="card-pending-pct" class="text-xs text-gray-400 mt-1">menunggu approval</div>
            </div>
        </div>

    </div>

    {{-- ── No data notice ── --}}
    <div id="cards-no-data" class="hidden mb-5">
        <div class="flex items-center gap-2 px-4 py-2.5 rounded-lg bg-gray-50 border border-dashed border-gray-200 text-xs text-gray-400">
            <i class="ki-filled ki-information-2"></i>
            Tidak ada data RAB untuk periode yang dipilih.
        </div>
    </div>

    {{-- ── Charts Row 1: Status + Monthly ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4 mb-4">

        {{-- Status Donut --}}
        <div class="card lg:col-span-2">
            <div class="card-header py-3 px-4 min-h-0">
                <h3 class="card-title text-sm">Distribusi Status</h3>
            </div>
            <div class="card-body px-4 pb-4 pt-2">
                <div id="chart-status-loading" class="flex justify-center items-center h-52">
                    <i class="ki-filled ki-arrows-circle animate-spin text-gray-300 text-2xl"></i>
                </div>
                <div id="chart-status-no-data" class="hidden flex justify-center items-center h-52 text-xs text-gray-400">
                    Tidak ada data untuk periode ini.
                </div>
                <div id="chart-status" class="hidden"></div>
            </div>
        </div>

        {{-- Monthly Trend --}}
        <div class="card lg:col-span-3">
            <div class="card-header py-3 px-4 min-h-0">
                <h3 class="card-title text-sm">Tren Anggaran per Bulan</h3>
            </div>
            <div class="card-body px-4 pb-4 pt-2">
                <div id="chart-monthly-loading" class="flex justify-center items-center h-52">
                    <i class="ki-filled ki-arrows-circle animate-spin text-gray-300 text-2xl"></i>
                </div>
                <div id="chart-monthly-no-data" class="hidden flex justify-center items-center h-52 text-xs text-gray-400">
                    Tidak ada data untuk periode ini.
                </div>
                <div id="chart-monthly" class="hidden"></div>
            </div>
        </div>

    </div>

    {{-- ── Chart Row 2: Divisi (Privileged only) ── --}}
    @auth
        @hasanyrole('bendahara_umum|ketua_yayasan|super_admin')
        <div class="card mb-5">
            <div class="card-header py-3 px-4 min-h-0">
                <h3 class="card-title text-sm">Perbandingan Anggaran per Divisi</h3>
            </div>
            <div class="card-body px-4 pb-4 pt-2">
                <div id="chart-divisi-loading" class="flex justify-center items-center h-52">
                    <i class="ki-filled ki-arrows-circle animate-spin text-gray-300 text-2xl"></i>
                </div>
                <div id="chart-divisi-no-data" class="hidden flex justify-center items-center h-52 text-xs text-gray-400">
                    Tidak ada data untuk periode ini.
                </div>
                <div id="chart-divisi" class="hidden"></div>
            </div>
        </div>
        @endhasanyrole
    @endauth

</div>
@endsection

@push('javascript')
<script>
    const summaryApiUrl = '{{ route('api.v1.rab.dashboard.summary') }}';
    const chartApiUrl   = '{{ route('api.v1.rab.dashboard.chart') }}';
    const exportBaseUrl = '{{ route('rab.export') }}';

    @auth
    const isPrivileged = {{ auth()->user()->hasAnyRole(['bendahara_umum', 'ketua_yayasan', 'super_admin']) ? 'true' : 'false' }};
    @else
    const isPrivileged = false;
    @endauth

    let statusChart  = null;
    let monthlyChart = null;
    let divisiChart  = null;

    // ── Helpers ──────────────────────────────────────────────────────────────

    function getFilters() {
        const tahun    = document.getElementById('filter-tahun')?.value;
        const bulan    = document.getElementById('filter-bulan')?.value;
        const divisiEl = document.getElementById('filter-divisi');
        const divisi   = divisiEl ? divisiEl.value : '';
        const params   = new URLSearchParams({ tahun });
        if (bulan)  params.set('bulan', bulan);
        if (divisi) params.set('divisi_id', divisi);
        return params;
    }

    function updateExportLink() {
        document.getElementById('export-btn').href = exportBaseUrl + '?' + getFilters().toString();
    }

    function fmtRupiah(val) {
        if (val >= 1_000_000_000) return 'Rp ' + (val / 1_000_000_000).toFixed(1).replace('.', ',') + ' M';
        if (val >= 1_000_000)     return 'Rp ' + (val / 1_000_000).toFixed(1).replace('.', ',') + ' Jt';
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(val);
    }

    function fmtFull(val) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(val);
    }

    function setLoading(id) {
        const el = document.getElementById(id);
        if (el) el.innerHTML = '<i class="ki-filled ki-arrows-circle animate-spin text-gray-300 text-base"></i>';
    }

    function showLoading() {
        ['card-total-rab','card-total-kegiatan','card-total-anggaran','card-total-approved','card-total-pending'].forEach(setLoading);
        document.getElementById('card-approved-pct').textContent = 'dari total';
        document.getElementById('card-pending-pct').textContent  = 'menunggu approval';
        document.getElementById('cards-no-data').classList.add('hidden');

        ['status','monthly','divisi'].forEach(key => {
            document.getElementById(`chart-${key}-loading`)?.classList.remove('hidden');
            document.getElementById(`chart-${key}-no-data`)?.classList.add('hidden');
            document.getElementById(`chart-${key}`)?.classList.add('hidden');
        });
    }

    function updateSummaryCards(data) {
        const total = data.total_rab || 0;

        document.getElementById('card-total-rab').textContent      = total;
        document.getElementById('card-total-kegiatan').textContent = data.total_kegiatan;
        document.getElementById('card-total-anggaran').textContent = fmtRupiah(data.total_anggaran);
        document.getElementById('card-total-approved').textContent = data.total_approved;
        document.getElementById('card-total-pending').textContent  = data.total_pending;

        if (total > 0) {
            const approvedPct = Math.round((data.total_approved / total) * 100);
            const pendingPct  = Math.round((data.total_pending  / total) * 100);
            document.getElementById('card-approved-pct').textContent = approvedPct + '% dari total';
            document.getElementById('card-pending-pct').textContent  = pendingPct  + '% dari total';
        }

        if (total === 0 && data.total_kegiatan === 0) {
            document.getElementById('cards-no-data').classList.remove('hidden');
        }

        // timestamp
        const now = new Date();
        document.getElementById('last-updated').textContent =
            'Update ' + now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    }

    // ── Chart helpers ─────────────────────────────────────────────────────────

    // Warna per label status — tiap status warna unik, tidak ada yang nabrak
    const STATUS_COLOR_MAP = {
        'Draft':               '#A1A5B7', // abu netral
        'Menunggu Bendahara':  '#FFA800', // oranye-kuning (warning)
        'Ditolak Bendahara':   '#F1416C', // merah terang (danger)
        'Menunggu Ketua':      '#009EF7', // biru (info) — beda dari ungu
        'Ditolak Ketua':       '#D9214E', // merah tua — beda dari Ditolak Bendahara
        'Disetujui':           '#50CD89', // hijau (success)
        'Dibatalkan':          '#7E8299', // abu gelap — beda dari Draft
    };

    function statusColor(label) {
        return STATUS_COLOR_MAP[label] ?? '#99A1B7';
    }

    function hideChartLoading(key, hasData) {
        document.getElementById(`chart-${key}-loading`).classList.add('hidden');
        if (!hasData) {
            document.getElementById(`chart-${key}-no-data`).classList.remove('hidden');
        } else {
            document.getElementById(`chart-${key}`).classList.remove('hidden');
        }
    }

    function renderStatusChart(distribution) {
        const hasData = distribution && distribution.length > 0;
        hideChartLoading('status', hasData);
        if (!hasData) return;

        if (statusChart) { statusChart.destroy(); statusChart = null; }

        const labels = distribution.map(d => d.status);
        const colors = labels.map(statusColor);

        statusChart = new ApexCharts(document.getElementById('chart-status'), {
            chart: { type: 'donut', height: 220, sparkline: { enabled: false } },
            series: distribution.map(d => d.count),
            labels,
            colors,
            legend: { position: 'bottom', fontSize: '11px', itemMargin: { horizontal: 6 } },
            dataLabels: { enabled: false },
            plotOptions: { pie: { donut: { size: '60%', labels: {
                show: true,
                total: { show: true, label: 'Total', fontSize: '12px', fontWeight: 600,
                    formatter: w => w.globals.seriesTotals.reduce((a, b) => a + b, 0) }
            } } } },
            tooltip: { y: { formatter: val => val + ' RAB' } },
        });
        statusChart.render();
    }

    function renderMonthlyChart(trend) {
        const hasData = trend && trend.some(t => t.total > 0);
        hideChartLoading('monthly', hasData);
        if (!hasData) return;

        if (monthlyChart) { monthlyChart.destroy(); monthlyChart = null; }

        monthlyChart = new ApexCharts(document.getElementById('chart-monthly'), {
            chart: { type: 'area', height: 220, toolbar: { show: false }, zoom: { enabled: false } },
            series: [{ name: 'Anggaran', data: trend.map(t => t.total) }],
            xaxis: { categories: trend.map(t => t.label), labels: { style: { fontSize: '10px' } } },
            yaxis: { labels: { style: { fontSize: '10px' }, formatter: val => {
                if (val >= 1_000_000) return (val / 1_000_000).toFixed(0) + 'Jt';
                return new Intl.NumberFormat('id-ID').format(val);
            } } },
            tooltip: { y: { formatter: val => fmtFull(val) } },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2 },
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.05 } },
            colors: ['#3E97FF'],
            grid: { strokeDashArray: 4, borderColor: '#f1f1f4' },
        });
        monthlyChart.render();
    }

    function renderDivisiChart(comparison) {
        const el = document.getElementById('chart-divisi-loading');
        if (!el) return;

        const hasData = comparison && comparison.length > 0;
        hideChartLoading('divisi', hasData);
        if (!hasData) return;

        if (divisiChart) { divisiChart.destroy(); divisiChart = null; }

        divisiChart = new ApexCharts(document.getElementById('chart-divisi'), {
            chart: { type: 'bar', height: 220, toolbar: { show: false } },
            series: [{ name: 'Total Anggaran', data: comparison.map(d => d.total) }],
            xaxis: { categories: comparison.map(d => d.divisi_id), labels: { style: { fontSize: '10px' } } },
            yaxis: { labels: { style: { fontSize: '10px' }, formatter: val => {
                if (val >= 1_000_000) return (val / 1_000_000).toFixed(0) + 'Jt';
                return new Intl.NumberFormat('id-ID').format(val);
            } } },
            tooltip: { y: { formatter: val => fmtFull(val) } },
            dataLabels: { enabled: false },
            colors: ['#50CD89'],
            plotOptions: { bar: { borderRadius: 4, columnWidth: '50%' } },
            grid: { strokeDashArray: 4, borderColor: '#f1f1f4' },
        });
        divisiChart.render();
    }

    // ── Main ──────────────────────────────────────────────────────────────────

    async function loadDashboardData() {
        showLoading();
        updateExportLink();

        const params = getFilters();
        const headers = {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        };

        try {
            const [summaryRes, chartRes] = await Promise.all([
                fetch(summaryApiUrl + '?' + params, { headers, credentials: 'same-origin' }),
                fetch(chartApiUrl   + '?' + params, { headers, credentials: 'same-origin' }),
            ]);

            const [summaryJson, chartJson] = await Promise.all([summaryRes.json(), chartRes.json()]);

            if (!summaryJson.error && summaryJson.data) updateSummaryCards(summaryJson.data);

            if (!chartJson.error && chartJson.data) {
                renderStatusChart(chartJson.data.status_distribution);
                renderMonthlyChart(chartJson.data.monthly_trend);
                if (isPrivileged) renderDivisiChart(chartJson.data.divisi_comparison);
            }
        } catch (err) {
            console.error('Dashboard load error:', err);
        }
    }

    document.getElementById('filter-tahun')?.addEventListener('change', loadDashboardData);
    document.getElementById('filter-bulan')?.addEventListener('change', loadDashboardData);
    document.getElementById('filter-divisi')?.addEventListener('change', loadDashboardData);

    document.addEventListener('DOMContentLoaded', loadDashboardData);
</script>
@endpush
