@extends('layouts.main')

@section('content')
<div class="container-fixed">

    {{-- ── Header ── --}}
    <div class="flex flex-wrap items-center justify-between gap-3 pb-5">
        <div>
            <h1 class="text-lg font-bold text-gray-900">{{ $data['pageTitle'] }}</h1>
            <p class="text-xs text-gray-500 mt-0.5">Ringkasan data Pengajuan Pembelian Barang</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('purchase-requests.index') }}" class="btn btn-sm btn-light">
                <i class="ki-filled ki-document text-xs"></i>Daftar Pengajuan
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
                   <select id="filter-tahun" class="select select-sm !h-7 !text-xs !py-0" style="width: 80px; padding-right: 1.75rem;">
                        @foreach($data['availableYears'] as $year)
                            <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-1.5">
                    <label class="text-xs text-gray-500 whitespace-nowrap">Bulan</label>
                    <select id="filter-bulan" class="select select-sm !h-7 !text-xs !py-0 w-36">
                        <option value="">Semua</option>
                        @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $bln)
                            <option value="{{ $i + 1 }}">{{ $bln }}</option>
                        @endforeach
                    </select>
                </div>

                @if($data['isPrivilegedUser'])
                <div class="flex items-center gap-1.5">
                    <label class="text-xs text-gray-500 whitespace-nowrap">Divisi</label>
                    <select id="filter-divisi" class="select select-sm !h-7 !text-xs !py-0 w-40">
                        <option value="">Semua Divisi</option>
                        @foreach($data['divisiList'] as $divisi)
                            <option value="{{ $divisi }}">{{ $divisi }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="ms-auto flex items-center gap-1.5 text-xs text-gray-400" id="last-updated-wrap">
                    <i class="ki-filled ki-time text-xs"></i>
                    <span id="last-updated">—</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Summary Cards ── --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-5">

        {{-- Total Pengajuan --}}
        <div class="card border-t-2 border-t-primary">
            <div class="card-body p-4">
                <div class="flex items-start justify-between mb-2">
                    <span class="text-xs text-gray-500 font-medium">Total Pengajuan</span>
                    <span class="size-7 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                        <i class="ki-filled ki-document text-primary text-xs"></i>
                    </span>
                </div>
                <div id="card-total-pengajuan" class="text-2xl font-bold text-gray-900 leading-none">
                    <i class="ki-filled ki-arrows-circle animate-spin text-gray-300 text-base"></i>
                </div>
                <div class="text-xs text-gray-400 mt-1">pengajuan</div>
            </div>
        </div>

        {{-- Dalam Approval --}}
        <div class="card border-t-2 border-t-warning">
            <div class="card-body p-4">
                <div class="flex items-start justify-between mb-2">
                    <span class="text-xs text-gray-500 font-medium">Dalam Approval</span>
                    <span class="size-7 rounded-lg bg-warning/10 flex items-center justify-center shrink-0">
                        <i class="ki-filled ki-time text-warning text-xs"></i>
                    </span>
                </div>
                <div id="card-dalam-approval" class="text-2xl font-bold text-gray-900 leading-none">
                    <i class="ki-filled ki-arrows-circle animate-spin text-gray-300 text-base"></i>
                </div>
                <div class="text-xs text-gray-400 mt-1">menunggu approval</div>
            </div>
        </div>

        {{-- Dalam Pembelian --}}
        <div class="card border-t-2 border-t-info">
            <div class="card-body p-4">
                <div class="flex items-start justify-between mb-2">
                    <span class="text-xs text-gray-500 font-medium">Dalam Pembelian</span>
                    <span class="size-7 rounded-lg bg-info/10 flex items-center justify-center shrink-0">
                        <i class="ki-filled ki-basket text-info text-xs"></i>
                    </span>
                </div>
                <div id="card-dalam-pembelian" class="text-2xl font-bold text-gray-900 leading-none">
                    <i class="ki-filled ki-arrows-circle animate-spin text-gray-300 text-base"></i>
                </div>
                <div class="text-xs text-gray-400 mt-1">sedang diproses</div>
            </div>
        </div>

        {{-- Selesai --}}
        <div class="card border-t-2 border-t-success">
            <div class="card-body p-4">
                <div class="flex items-start justify-between mb-2">
                    <span class="text-xs text-gray-500 font-medium">Selesai</span>
                    <span class="size-7 rounded-lg bg-success/10 flex items-center justify-center shrink-0">
                        <i class="ki-filled ki-check-circle text-success text-xs"></i>
                    </span>
                </div>
                <div id="card-selesai" class="text-2xl font-bold text-gray-900 leading-none">
                    <i class="ki-filled ki-arrows-circle animate-spin text-gray-300 text-base"></i>
                </div>
                <div class="text-xs text-gray-400 mt-1">selesai</div>
            </div>
        </div>

        {{-- Total Biaya Estimasi --}}
        <div class="card border-t-2 border-t-primary col-span-2 sm:col-span-1">
            <div class="card-body p-4">
                <div class="flex items-start justify-between mb-2">
                    <span class="text-xs text-gray-500 font-medium">Biaya Estimasi</span>
                    <span class="size-7 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                        <i class="ki-filled ki-dollar text-primary text-xs"></i>
                    </span>
                </div>
                <div id="card-biaya-estimasi" class="text-base font-bold text-gray-900 leading-none">
                    <i class="ki-filled ki-arrows-circle animate-spin text-gray-300 text-base"></i>
                </div>
                <div class="text-xs text-gray-400 mt-1">rupiah</div>
            </div>
        </div>

        {{-- Total Biaya Aktual --}}
        <div class="card border-t-2 border-t-success col-span-2 sm:col-span-1">
            <div class="card-body p-4">
                <div class="flex items-start justify-between mb-2">
                    <span class="text-xs text-gray-500 font-medium">Biaya Aktual</span>
                    <span class="size-7 rounded-lg bg-success/10 flex items-center justify-center shrink-0">
                        <i class="ki-filled ki-dollar text-success text-xs"></i>
                    </span>
                </div>
                <div id="card-biaya-aktual" class="text-base font-bold text-gray-900 leading-none">
                    <i class="ki-filled ki-arrows-circle animate-spin text-gray-300 text-base"></i>
                </div>
                <div class="text-xs text-gray-400 mt-1">rupiah</div>
            </div>
        </div>

    </div>

    {{-- ── No data notice ── --}}
    <div id="cards-no-data" class="hidden mb-5">
        <div class="flex items-center gap-2 px-4 py-2.5 rounded-lg bg-gray-50 border border-dashed border-gray-200 text-xs text-gray-400">
            <i class="ki-filled ki-information-2"></i>
            Tidak ada data pengajuan untuk periode yang dipilih.
        </div>
    </div>

    {{-- ── Charts ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">

        {{-- Stacked Bar: Distribusi Status per Bulan --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title text-sm">Distribusi Status per Bulan</h3>
                <span class="text-xs text-gray-400" id="chart-bar-year"></span>
            </div>
            <div class="card-body p-4">
                <div id="chart-bar-loading" class="flex items-center justify-center h-48 text-gray-300">
                    <i class="ki-filled ki-arrows-circle animate-spin text-2xl"></i>
                </div>
                <canvas id="chart-status-bar" class="hidden" height="200"></canvas>
            </div>
        </div>

        {{-- Line: Estimasi vs Aktual per Bulan --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title text-sm">Biaya Estimasi vs Aktual per Bulan</h3>
                <span class="text-xs text-gray-400" id="chart-line-year"></span>
            </div>
            <div class="card-body p-4">
                <div id="chart-line-loading" class="flex items-center justify-center h-48 text-gray-300">
                    <i class="ki-filled ki-arrows-circle animate-spin text-2xl"></i>
                </div>
                <canvas id="chart-biaya-line" class="hidden" height="200"></canvas>
            </div>
        </div>

    </div>

</div>
@endsection

@push('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@push('javascript')
<script>
    const summaryApiUrl = '{{ route('api.v1.purchase-requests.dashboard.summary') }}';
    const chartApiUrl   = '{{ route('api.v1.purchase-requests.dashboard.chart') }}';
    const isPrivileged  = {{ $data['isPrivilegedUser'] ? 'true' : 'false' }};

    let barChartInstance  = null;
    let lineChartInstance = null;

    const STATUS_COLORS = {
        DRAFT:                '#94a3b8',
        PENDING_BENDAHARA:    '#f59e0b',
        REJECTED_BENDAHARA:   '#ef4444',
        PENDING_KETUA:        '#f59e0b',
        REJECTED_KETUA:       '#ef4444',
        APPROVED:             '#22c55e',
        CANCELLED:            '#374151',
        PURCHASING:           '#3b82f6',
        PARTIALLY_PURCHASED:  '#8b5cf6',
        COMPLETED:            '#10b981',
    };

    const STATUS_LABELS = {
        DRAFT:                'Draft',
        PENDING_BENDAHARA:    'Menunggu Bendahara',
        REJECTED_BENDAHARA:   'Ditolak Bendahara',
        PENDING_KETUA:        'Menunggu Ketua',
        REJECTED_KETUA:       'Ditolak Ketua',
        APPROVED:             'Disetujui',
        CANCELLED:            'Dibatalkan',
        PURCHASING:           'Dalam Pembelian',
        PARTIALLY_PURCHASED:  'Sebagian Dibeli',
        COMPLETED:            'Selesai',
    };

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

    function fmtRupiah(val) {
        if (val >= 1_000_000_000) return 'Rp ' + (val / 1_000_000_000).toFixed(1).replace('.', ',') + ' M';
        if (val >= 1_000_000)     return 'Rp ' + (val / 1_000_000).toFixed(1).replace('.', ',') + ' Jt';
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(val);
    }

    function spinnerHtml() {
        return '<i class="ki-filled ki-arrows-circle animate-spin text-gray-300 text-base"></i>';
    }

    function showLoading() {
        ['card-total-pengajuan','card-dalam-approval','card-dalam-pembelian','card-selesai'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.innerHTML = spinnerHtml();
        });
        ['card-biaya-estimasi','card-biaya-aktual'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.innerHTML = spinnerHtml();
        });
        document.getElementById('cards-no-data').classList.add('hidden');

        // Show chart loaders
        document.getElementById('chart-bar-loading').classList.remove('hidden');
        document.getElementById('chart-status-bar').classList.add('hidden');
        document.getElementById('chart-line-loading').classList.remove('hidden');
        document.getElementById('chart-biaya-line').classList.add('hidden');
    }

    function updateCards(data) {
        document.getElementById('card-total-pengajuan').textContent  = data.total_pengajuan;
        document.getElementById('card-dalam-approval').textContent   = data.total_dalam_approval;
        document.getElementById('card-dalam-pembelian').textContent  = data.total_dalam_pembelian;
        document.getElementById('card-selesai').textContent          = data.total_selesai;
        document.getElementById('card-biaya-estimasi').textContent   = fmtRupiah(data.total_biaya_estimasi);
        document.getElementById('card-biaya-aktual').textContent     = fmtRupiah(data.total_biaya_aktual);

        if (data.total_pengajuan === 0) {
            document.getElementById('cards-no-data').classList.remove('hidden');
        }

        const now = new Date();
        document.getElementById('last-updated').textContent =
            'Update ' + now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    }

    function renderBarChart(chartData) {
        const labels = chartData.map(m => m.label);

        // Collect all unique statuses
        const allStatuses = [...new Set(chartData.flatMap(m => Object.keys(m.data)))];

        const datasets = allStatuses.map(status => ({
            label:           STATUS_LABELS[status] ?? status,
            data:            chartData.map(m => m.data[status] ?? 0),
            backgroundColor: STATUS_COLORS[status] ?? '#94a3b8',
            borderRadius:    3,
            borderSkipped:   false,
        }));

        const canvas = document.getElementById('chart-status-bar');
        document.getElementById('chart-bar-loading').classList.add('hidden');
        canvas.classList.remove('hidden');

        if (barChartInstance) barChartInstance.destroy();

        barChartInstance = new Chart(canvas, {
            type: 'bar',
            data: { labels, datasets },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } },
                    tooltip: { mode: 'index', intersect: false },
                },
                scales: {
                    x: { stacked: true, grid: { display: false }, ticks: { font: { size: 10 } } },
                    y: { stacked: true, beginAtZero: true, ticks: { stepSize: 1, font: { size: 10 } } },
                },
            },
        });
    }

    function renderLineChart(chartData) {
        // For line chart we need biaya per month — fetch from summary per month
        // Use chart data total counts as proxy, but we need biaya data
        // We'll fetch summary for each month in parallel
        const tahun    = document.getElementById('filter-tahun')?.value;
        const divisiEl = document.getElementById('filter-divisi');
        const divisi   = divisiEl ? divisiEl.value : '';
        const headers  = {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        };

        const months = [1,2,3,4,5,6,7,8,9,10,11,12];
        const labels = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

        const promises = months.map(bulan => {
            const p = new URLSearchParams({ tahun, bulan });
            if (divisi) p.set('divisi_id', divisi);
            return fetch('{{ route('api.v1.purchase-requests.dashboard.summary') }}?' + p, { headers, credentials: 'same-origin' })
                .then(r => r.json())
                .then(j => j.data ?? { total_biaya_estimasi: 0, total_biaya_aktual: 0 });
        });

        Promise.all(promises).then(results => {
            const estimasi = results.map(r => r.total_biaya_estimasi ?? 0);
            const aktual   = results.map(r => r.total_biaya_aktual ?? 0);

            const canvas = document.getElementById('chart-biaya-line');
            document.getElementById('chart-line-loading').classList.add('hidden');
            canvas.classList.remove('hidden');

            if (lineChartInstance) lineChartInstance.destroy();

            lineChartInstance = new Chart(canvas, {
                type: 'line',
                data: {
                    labels,
                    datasets: [
                        {
                            label: 'Biaya Estimasi',
                            data: estimasi,
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59,130,246,0.08)',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                        },
                        {
                            label: 'Biaya Aktual',
                            data: aktual,
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16,185,129,0.08)',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                label: ctx => ctx.dataset.label + ': ' + fmtRupiah(ctx.parsed.y),
                            },
                        },
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                font: { size: 10 },
                                callback: v => fmtRupiah(v),
                            },
                        },
                    },
                },
            });
        });
    }

    async function loadDashboard() {
        showLoading();

        const params  = getFilters();
        const tahun   = document.getElementById('filter-tahun')?.value;
        const headers = {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        };

        // Update year labels on charts
        document.getElementById('chart-bar-year').textContent  = tahun;
        document.getElementById('chart-line-year').textContent = tahun;

        try {
            const [summaryRes, chartRes] = await Promise.all([
                fetch('{{ route('api.v1.purchase-requests.dashboard.summary') }}?' + params, { headers, credentials: 'same-origin' }),
                fetch(chartApiUrl + '?' + params, { headers, credentials: 'same-origin' }),
            ]);

            const summaryJson = await summaryRes.json();
            const chartJson   = await chartRes.json();

            if (!summaryJson.error && summaryJson.data) {
                updateCards(summaryJson.data);
            }

            if (!chartJson.error && chartJson.data) {
                renderBarChart(chartJson.data);
                renderLineChart(chartJson.data);
            }
        } catch (err) {
            console.error('Dashboard load error:', err);
        }
    }

    document.getElementById('filter-tahun')?.addEventListener('change', loadDashboard);
    document.getElementById('filter-bulan')?.addEventListener('change', loadDashboard);
    document.getElementById('filter-divisi')?.addEventListener('change', loadDashboard);

    document.addEventListener('DOMContentLoaded', loadDashboard);
</script>
@endpush
