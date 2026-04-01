@extends('layouts.main')

@section('content')
    <!-- Container -->
    {{-- @include('backoffice.config.company.partials.submenu') --}}
    <!-- Container -->
    <div class="container-fixed">
        <div class="flex flex-wrap items-center lg:items-end justify-between gap-5">
            <div class="flex flex-col justify-center gap-2">
                <h1 class="text-xl font-bold leading-none text-gray-900">
                    {{-- {{ $data['pageTitle'] }} --}}
                </h1>
                {{-- <div class="flex items-center gap-2 text-sm font-normal text-gray-700">
                    Central Hub for Personal Customization
                </div> --}}
            </div>
            <div class="flex items-center gap-2.5">
                {{-- <div class="relative">
                    <i
                        class="ki-filled ki-magnifier leading-none text-md text-gray-500 absolute top-1/2 left-0 -translate-y-1/2 ml-3">
                    </i>
                    <input class="input input-sm pl-8 text-center" data-datatable-search="#kt_remote_table"
                        placeholder="Cari Data" type="text">
                </div>
                <button id="refresh-btn" class="btn btn-sm text-center btn-info">
                    <i class="ki-filled ki-arrows-circle"></i>
                </button> --}}
            </div>
        </div>
    </div>

    @include('partials.attention')

    <div class="container-fixed">
        <div class="grid pb-7.5">
            <div class="card card-xl-stretch mb-xl-8">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-800">10 Data Terakhir Kesalahan Bacaan</span>
                    </h3>
                </div>
                <div class="card-body py-3">
                    <div class="chart-container" style="height: 300px;">
                        <canvas id="errorsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>


        <div class="grid pb-7.5">
            <div class="card card-grid min-w-full">
                <div class="card-body">
                    <div id="kt_remote_table">
                        <div class="scrollable-x-auto">
                            <table class="table table-auto table-border align-middle text-gray-700 font-medium text-sm"
                                data-datatable-table="true">
                                <thead>
                                    <tr>
                                        <th class=" text-center" data-datatable-column="Approver">
                                            <span class="sort">
                                                <span class="sort-label">
                                                    Musyrif
                                                </span>
                                            </span>
                                        </th>
                                        <th class="text-center" data-datatable-column="surah">
                                            <span class="sort">
                                                <span class="sort-label">
                                                    Informasi Bacaan
                                                </span>
                                            </span>
                                        </th>
                                        <th class="text-center" data-datatable-column="action">
                                            <span class="sort">
                                                <span class="sort-label">
                                                    Action
                                                </span>
                                            </span>
                                        </th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                        <div
                            class="card-footer justify-center md:justify-between flex-col md:flex-row gap-3 text-gray-600 text-2sm font-medium">
                            <div class="flex items-center gap-2">
                                Show
                                <select class="select select-sm w-16" data-datatable-size="true" name="perpage">
                                </select>
                                per page
                            </div>
                            <div class="flex items-center gap-4">
                                <span data-datatable-info="true">
                                </span>
                                <div class="pagination" data-datatable-pagination="true">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('javascript')
    <!-- Begin - Define Route -->
    <script type="text/javascript">
        const showRoute = "{{ route('summaries.show', [':formCode', ':entryId']) }}";
        const formCode = "{{$data['formCode']  }}"
    </script>
    <!-- End - Define Route -->

    <script type="text/javascript">
        const apiUrl = '{{ route('api.v1.summaries.datatable') }}';
        const deleteUrl = "#";
        const element = document.querySelector('#kt_remote_table');

        const dataTableOptions = {
            apiEndpoint: apiUrl,
            pageSize: 10,
            search: "",
            stateSave: false,
            infoEmpty: "Data Kosong",
            columns: {
                approver: {
                    title: 'Keterangan',
                    render: (item, row, context) => {
                        return `
                            <div class="flex flex-col sm:flex-row items-center justify-center gap-2 text-center">
                                <div class="font-semibold text-gray-800">${row.formatted_entry_date}</div>
                                <div class="text-xs text-gray-700">
                                    <i class="ki-filled ki-user-tick mr-1"></i>${row.approver.name}
                                </div>
                            </div>
                        `;
                    },
                    createdCell(cell) {
                        cell.classList.add('text-center');
                    },
                },
                surah: {
                    title: 'Surah',
                    render: (item, row, context) => {
                        return `
                        <div class="flex flex-col items-center space-y-1">
                            <!-- Badge Surah -->
                            <span class="inline-flex badge badge-lg badge-pill badge-outline badge-success">
                                <i class="fas fa-book mr-1.5"></i>
                               ${row.surah.name} - ${row.surah.name_latin}
                            </span>

                            <!-- Info Halaman dan Ayat -->
                            <div class="flex items-center space-x-2 text-sm text-gray-600">
                                <span class="inline-flex items-center">
                                    <i class="fas fa-file-alt mr-1"></i>
                                    Hal. ${row.page}
                                </span>
                                <span class="text-gray-400">|</span>
                                <span class="inline-flex items-center">
                                    <i class="fas fa-hashtag mr-1"></i>
                                    Ayat ${row.verse_start}-${row.verse_end}
                                </span>
                            </div>
                            <div class="flex items-center space-x-2 text-sm text-gray-600">
                                ${row.total_errors}  Kesalahan
                            </div>
                        </div>
                    `;
                    },
                    createdCell(cell) {
                        cell.classList.add('text-center');
                    },
                },
                // notes: {
                //     title: 'Catatan',
                //     render: (item, row, context) => {
                //         return row.notes;
                //     },
                //     createdCell(cell) {
                //         cell.classList.add('text-center');
                //     },
                // },
                action: {
                    title: 'Action',
                    render: (item, row, context) => {

                        const showUrl = showRoute
                            .replace(':formCode', encodeURIComponent(formCode))
                            .replace(':entryId', encodeURIComponent(row.id));


                        return `


                        <a href="${showUrl}" class="btn btn-icon btn-sm btn-clear btn-primary" data-tooltip="#show">
                            <i class="ki-filled ki-eye"></i>
                        </a>
                        <div class="tooltip transition-opacity duration-300" id="show">
                            Lihat {{ $data['pageTitle'] }}
                        </div>
                    `;
                    },
                    createdCell(cell) {
                        cell.classList.add('text-center');
                    },
                },
            }
        };
        const dataTable = new KTDataTable(element, dataTableOptions);
        // const refreshTable = document.getElementById('refresh-btn').addEventListener('click', function() {
        //     dataTable.reload();
        // });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function() {
            // Data dari controller
            const chartData = @json($data['chartData']);

            // Balik urutan data agar yang terbaru di kanan
            const reversedData = [...chartData].reverse();

            // Siapkan label dan data
            const labels = reversedData.map(item => `${item.date}`);
            const originalData = reversedData.map(item => item.total_errors);

            // Modifikasi data untuk visual: jika nilai 0, ganti dengan 0.1 untuk menampilkan bar tipis
            const visualData = originalData.map(value => value === 0 ? 0.1 : value);

            // Inisialisasi chart
            const ctx = document.getElementById('errorsChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Jumlah Kesalahan',
                        data: visualData,
                        backgroundColor: [
                            '#1A3A5F', // Navy Blue
                            '#722F37', // Burgundy
                            '#C9A227', // Gold
                            '#4C5958', // Charcoal
                            '#5C7470', // Sage
                            '#7B68EE', // Medium Slate Blue
                            '#CD5C5C', // Indian Red
                            '#DAA520', // Goldenrod
                            '#4682B4', // Steel Blue
                            '#8B4513', // Saddle Brown
                        ],
                        borderColor: [
                            '#0F1E2F', // Dark Navy
                            '#5A1F24', // Dark Burgundy
                            '#8B6F1B', // Dark Gold
                            '#2E3A3A', // Dark Charcoal
                            '#3D4F4B', // Dark Sage
                            '#5A4FBE', // Dark Medium Slate Blue
                            '#A04040', // Dark Indian Red
                            '#B8860B', // Dark Goldenrod
                            '#2F5A8A', // Dark Steel Blue
                            '#6B3410', // Dark Saddle Brown
                        ],
                        borderWidth: 1,
                        borderRadius: 4,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'x', // Tetap horizontal bar
                    scales: {
                        y: {
                            beginAtZero: true,
                            min: 0, // Pastikan sumbu Y mulai dari 0
                            max: Math.max(...originalData, 1) + 1, // Atur maksimum berdasarkan data
                            ticks: {
                                stepSize: 1,
                                precision: 0,
                                callback: function(value) {
                                    // Tampilkan 0 di sumbu Y meskipun ada nilai 0.1 di data
                                    return value === 0.1 ? '0' : value;
                                }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            padding: 12,
                            cornerRadius: 4,
                            displayColors: false,
                            callbacks: {
                                title: function(context) {
                                    return reversedData[context.dataIndex];
                                },
                                label: function(context) {
                                    const item = reversedData[context.dataIndex];
                                    const originalValue = originalData[context.dataIndex];
                                    return [
                                        `Surah: ${item.surah}`,
                                        `Halaman: ${item.page}`,
                                        `Ayat: ${item.verse}`,
                                        `Total Kesalahan: ${originalValue}`
                                    ];
                                }
                            }
                        }
                    },
                    animation: {
                        duration: 1000,
                        easing: 'easeOutQuart'
                    },
                    // Menampilkan nilai 0 dengan bar yang sangat tipis
                    layout: {
                        padding: {
                            top: 10,
                            right: 10,
                            bottom: 10,
                            left: 10
                        }
                    }
                }
            });
        });
    </script>
@endpush
