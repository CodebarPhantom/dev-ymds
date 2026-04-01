@extends('layouts.main')

@section('content')
    <!-- Container -->
    <div class="container-fixed">
        <div class="flex flex-wrap items-center lg:items-end justify-between gap-5 pb-7.5">
            <div class="flex flex-col justify-center gap-2">
                <h1 class="text-xl font-bold leading-none text-gray-900">
                    Konfirmasi Pembayaran
                </h1>
            </div>
            <div class="flex items-center gap-2.5">
                <div class="relative">
                    <i class="ki-filled ki-magnifier leading-none text-md text-gray-500 absolute top-1/2 left-0 -translate-y-1/2 ml-3"></i>
                    <input class="input input-sm pl-8 text-center" data-datatable-search="#kt_remote_table" placeholder="Cari Data" value="" type="text">
                </div>
                <button id="refresh-btn" class="btn btn-sm text-center btn-info">
                    <i class="ki-filled ki-arrows-circle"></i>
                </button>
            </div>
        </div>
    </div>

    @include('partials.attention')

    <div class="container-fixed">
        <div class="grid pb-7.5">
            <div class="card card-grid min-w-full">
                <div class="card-header">
                    <h3 class="card-title">Konfirmasi Pembayaran</h3>
                    <div class="flex items-center gap-2">
                        <!-- Month Filter -->
                        <select id="monthFilter" class="select select-sm w-32">
                            <option value="">Semua Bulan</option>
                            @php
                                $months = [
                                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                ];
                            @endphp
                            @foreach($months as $key => $month)
                                <option value="{{ $key }}">{{ $month }}</option>
                            @endforeach
                        </select>

                        <!-- Year Filter -->
                        <select id="yearFilter" class="select select-sm w-20">
                            <option value="">Semua</option>
                            @for($year = 2025; $year <= date('Y') + 2; $year++)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endfor
                        </select>

                        <button id="exportBtn" class="btn btn-sm btn-success">
                            <i class="ki-filled ki-file-down"></i>
                            Export Excel
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="kt_remote_table">
                        <div class="scrollable-x-auto">
                            <table class="table table-auto table-border align-middle text-gray-700 font-medium text-sm" data-datatable-table="true">
                                <thead>
                                    <tr>
                                        <th class="w-1/12 text-center" data-datatable-column="confirmation_code">
                                            <span class="sort">
                                                <span class="sort-label">Kode</span>
                                                <span class="sort-icon"></span>
                                            </span>
                                        </th>
                                        <th class="w-2/12 text-center" data-datatable-column="payer_name">
                                            <span class="sort">
                                                <span class="sort-label">Nama</span>
                                            </span>
                                        </th>
                                        <th class="w-2/12 text-center" data-datatable-column="category">
                                            <span class="sort">
                                                <span class="sort-label">Kategori</span>
                                            </span>
                                        </th>
                                        <th class="w-2/12 text-center" data-datatable-column="location">
                                            <span class="sort">
                                                <span class="sort-label">Lokasi</span>
                                            </span>
                                        </th>
                                        <th class="w-1/12 text-center" data-datatable-column="period">
                                            <span class="sort">
                                                <span class="sort-label">Periode</span>
                                            </span>
                                        </th>
                                        <th class="w-1/12 text-center" data-datatable-column="amount">
                                            <span class="sort">
                                                <span class="sort-label">Nominal</span>
                                            </span>
                                        </th>
                                        <th class="w-1/12 text-center" data-datatable-column="status">
                                            <span class="sort">
                                                <span class="sort-label">Status</span>
                                            </span>
                                        </th>
                                        <th class="w-2/12 text-center" data-datatable-column="action">
                                            <span class="sort">
                                                <span class="sort-label">Action</span>
                                            </span>
                                        </th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                        <div class="card-footer justify-center md:justify-between flex-col md:flex-row gap-3 text-gray-600 text-2sm font-medium">
                            <div class="flex items-center gap-2">
                                Show
                                <select class="select select-sm w-16" data-datatable-size="true" name="perpage"></select>
                                per page
                            </div>
                            <div class="flex items-center gap-4">
                                <span data-datatable-info="true"></span>
                                <div class="pagination" data-datatable-pagination="true"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('javascript')
    <script type="text/javascript">
        const apiUrl = '{{ route('api.v1.payment-confirmations.datatable') }}';
        const updateStatusUrl = "{{ route('admin.payment-confirmations.update-status', ':id') }}";
        const element = document.querySelector('#kt_remote_table');

        const dataTableOptions = {
            apiEndpoint: apiUrl,
            pageSize: 10,
            searchQuery: '',
            infoEmpty: 'Data Kosong',
            stateSave: false,
            columns: {
                confirmation_code: {
                    title: 'Kode',
                },
                payer_name: {
                    title: 'Nama',
                },
                category: {
                    title: 'Kategori',
                },
                location: {
                    title: 'Lokasi',
                },
                period: {
                    title: 'Periode',
                },
                amount: {
                    title: 'Nominal',
                    render: (data, type, row) => {
                        return `Rp ${type.amount_formatted}`;
                    },
                    createdCell(cell) {
                        cell.classList.add('text-center');
                    },
                },
                status: {
                    title: 'Status',
                    render: (data, type, row) => {
                        const badgeClass = type.status === 'sudah_dicek' ? 'badge-success' : 'badge-warning';
                        return `<span class="badge ${badgeClass}">${type.status_label}</span>`;
                    },
                    createdCell(cell) {
                        cell.classList.add('text-center');
                    },
                },
                action: {
                    title: 'Action',
                    render: (data, type, row) => {
                        let actions = '';

                        // Show opposite action button based on current status
                        if (type.status === 'butuh_pengecekan') {
                            actions += `
                            <button class="btn btn-icon btn-sm btn-clear btn-success" data-tooltip="#checked" onclick="updateStatus(${type.id}, 'sudah_dicek')">
                                <i class="ki-filled ki-check"></i>
                            </button>
                            <div class="tooltip transition-opacity duration-300" id="checked">
                                Sudah Dicek
                            </div>`;
                        } else {
                            actions += `
                            <button class="btn btn-icon btn-sm btn-clear btn-warning" data-tooltip="#pending" onclick="updateStatus(${type.id}, 'butuh_pengecekan')">
                                <i class="ki-filled ki-time"></i>
                            </button>
                            <div class="tooltip transition-opacity duration-300" id="pending">
                                Butuh Pengecekan
                            </div>`;
                        }

                        if (type.proof_file) {
                            actions += `
                        <a href="${type.proof_file_url}" class="btn btn-icon btn-sm btn-clear btn-primary" target="_blank" data-tooltip="#view-proof">
                            <i class="ki-filled ki-eye"></i>
                        </a>
                        <div class="tooltip transition-opacity duration-300" id="view-proof">
                            Lihat Bukti
                        </div>`;
                        }

                        return actions;
                    },
                    createdCell(cell) {
                        cell.classList.add('text-center');
                    },
                },
            }
        };

        let dataTable = new KTDataTable(element, dataTableOptions);
        
        // Global filter variables
        let currentMonth = '';
        let currentYear = '';
        
        // Override datatable's data fetching
        const originalFetch = window.fetch;
        window.fetch = function(url, options) {
            // Check if this is our datatable API call
            if (url.includes('/api/v1/payment-confirmations/datatable')) {
                // Add filter parameters to URL
                const urlObj = new URL(url);
                if (currentMonth) urlObj.searchParams.set('month', currentMonth);
                if (currentYear) urlObj.searchParams.set('year', currentYear);
                
                console.log('Modified URL:', urlObj.toString());
                url = urlObj.toString();
            }
            return originalFetch.call(this, url, options);
        };

        // Simple approach - trigger table reload with custom parameters
        function applyFilters() {
            const month = document.getElementById('monthFilter').value;
            const year = document.getElementById('yearFilter').value;
            
            console.log('Filter values:', {month, year});
            
            // Update global filter variables
            currentMonth = month;
            currentYear = year;
            
            // Reload table - fetch override will handle the parameters
            dataTable.reload();
        }

        // Add event listeners
        document.getElementById('monthFilter').addEventListener('change', applyFilters);
        document.getElementById('yearFilter').addEventListener('change', applyFilters);

        // Export handler
        document.getElementById('exportBtn').addEventListener('click', function() {
            const month = document.getElementById('monthFilter').value;
            const year = document.getElementById('yearFilter').value;

            let exportUrl = '{{ route("admin.payment-confirmations.export") }}?';
            const params = new URLSearchParams();

            if (month) params.append('month', month);
            if (year) params.append('year', year);

            window.open(exportUrl + params.toString(), '_blank');
        });

        const refreshTable = document.getElementById('refresh-btn').addEventListener('click', function() {
            // Reset filters
            document.getElementById('monthFilter').value = '';
            document.getElementById('yearFilter').value = '';
            dataTable.reload();
        });

        function updateStatus(confirmationId, status) {
            if (confirm('Yakin ingin mengubah status?')) {
                const url = updateStatusUrl.replace(':id', confirmationId);
                fetch(url, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ status: status })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        dataTable.reload();
                        alert('Status berhasil diupdate!');
                    } else {
                        alert('Gagal mengupdate status');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan');
                });
            }
        }
    </script>
@endpush
