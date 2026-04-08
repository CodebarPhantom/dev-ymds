@extends('layouts.main')

@section('content')
    <!-- Container -->
    <div class="container-fixed">
        <div class="flex flex-wrap items-center lg:items-end justify-between gap-5 pb-7.5">
            <div class="flex flex-col justify-center gap-2">
                <h1 class="text-xl font-bold leading-none text-gray-900">
                    {{ $data['pageTitle'] }}
                </h1>
            </div>
            <div class="flex items-center gap-2.5">
                <div class="relative">
                    <i class="ki-filled ki-magnifier leading-none text-md text-gray-500 absolute top-1/2 left-0 -translate-y-1/2 ml-3"></i>
                    <input class="input input-sm pl-8 text-center" data-datatable-search="#kt_rab_table"
                        placeholder="Cari Data" value="" type="text">
                </div>
                <button id="refresh-btn" class="btn btn-sm text-center btn-info">
                    <i class="ki-filled ki-arrows-circle"></i>
                </button>
                @can('createPolicy', App\Models\Rab::class)
                    <a class="btn btn-sm text-center btn-success" href="{{ route('rab.create') }}">
                        <i class="ki-filled ki-plus"></i>Buat RAB
                    </a>
                @endcan
            </div>
        </div>
    </div>

    @include('partials.attention')

    <!-- Filter Section -->
    <div class="container-fixed pb-4">
        <div class="card">
            <div class="card-body">
                <div class="flex flex-wrap gap-3 items-end">
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-gray-600">Status</label>
                        <select id="filter-status" class="select select-sm w-48">
                            <option value="">Semua Status</option>
                            <option value="DRAFT">Draft</option>
                            <option value="PENDING_BENDAHARA">Menunggu Bendahara</option>
                            <option value="REJECTED_BENDAHARA">Ditolak Bendahara</option>
                            <option value="PENDING_KETUA">Menunggu Ketua</option>
                            <option value="REJECTED_KETUA">Ditolak Ketua</option>
                            <option value="APPROVED">Disetujui</option>
                            <option value="CANCELLED">Dibatalkan</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-gray-600">Bulan</label>
                        <input id="filter-bulan" class="input input-sm w-40" type="month" placeholder="Bulan">
                    </div>
                    @auth
                        @hasrole('bendahara_umum|ketua_yayasan')
                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-medium text-gray-600">Divisi</label>
                                <input id="filter-divisi" class="input input-sm w-40" type="text" placeholder="Nama divisi">
                            </div>
                        @endhasrole
                    @endauth
                    <button id="apply-filter-btn" class="btn btn-sm btn-primary">
                        <i class="ki-filled ki-filter"></i>Filter
                    </button>
                    <button id="reset-filter-btn" class="btn btn-sm btn-light">
                        Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fixed">
        <div class="grid pb-7.5">
            <div class="card card-grid min-w-full">
                <div class="card-body">
                    <div id="kt_rab_table">
                        <div class="scrollable-x-auto">
                            <table class="table table-auto table-border align-middle text-gray-700 font-medium text-sm"
                                data-datatable-table="true">
                                <thead>
                                    <tr>
                                        <th class="text-center" data-datatable-column="nomor_rab">
                                            <span class="sort"><span class="sort-label">Nomor RAB</span><span class="sort-icon"></span></span>
                                        </th>
                                        @auth
                                            @hasrole('bendahara_umum|ketua_yayasan')
                                                <th class="text-center" data-datatable-column="divisi_id">
                                                    <span class="sort"><span class="sort-label">Divisi</span><span class="sort-icon"></span></span>
                                                </th>
                                            @endhasrole
                                        @endauth
                                        <th class="text-center" data-datatable-column="bulan_pengajuan">
                                            <span class="sort"><span class="sort-label">Bulan Pengajuan</span><span class="sort-icon"></span></span>
                                        </th>
                                        <th class="text-center" data-datatable-column="total_kegiatan">
                                            <span class="sort"><span class="sort-label">Total Kegiatan</span><span class="sort-icon"></span></span>
                                        </th>
                                        <th class="text-center" data-datatable-column="total_biaya_anggaran">
                                            <span class="sort"><span class="sort-label">Total Biaya</span><span class="sort-icon"></span></span>
                                        </th>
                                        <th class="text-center" data-datatable-column="status">
                                            <span class="sort"><span class="sort-label">Status</span><span class="sort-icon"></span></span>
                                        </th>
                                        <th class="text-center" data-datatable-column="action">
                                            <span class="sort"><span class="sort-label">Aksi</span></span>
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

    <!-- Modal Reject -->
    <div class="modal hidden" id="modal_reject_rab" data-modal="true">
        <div class="modal-content max-w-md top-[15%]">
            <div class="modal-header">
                <h3 class="modal-title">Tolak RAB</h3>
                <button class="btn btn-xs btn-icon btn-light" data-modal-dismiss="true">
                    <i class="ki-outline ki-cross"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="flex flex-col gap-3">
                    <label class="form-label">Catatan Penolakan <span class="text-danger">*</span></label>
                    <textarea id="reject-catatan" class="textarea" rows="4" placeholder="Masukkan alasan penolakan..."></textarea>
                </div>
            </div>
            <div class="modal-footer justify-end">
                <button class="btn btn-sm btn-light" data-modal-dismiss="true">Batal</button>
                <button id="confirm-reject-btn" class="btn btn-sm btn-danger">Tolak RAB</button>
            </div>
        </div>
    </div>
    <!-- End Modal Reject -->
@endsection

@push('javascript')
    <script type="text/javascript">
        const showRoute   = "{{ route('rab.show', ':id') }}";
        const editRoute   = "{{ route('rab.edit', ':id') }}";
        const submitUrl   = "{{ route('api.v1.rab.submit', ':id') }}";
        const cancelUrl   = "{{ route('api.v1.rab.cancel', ':id') }}";
        const approveUrl  = "{{ route('api.v1.rab.approve', ':id') }}";
        const rejectUrl   = "{{ route('api.v1.rab.reject', ':id') }}";

        @auth
        const isBendaharaOrKetua = {{ auth()->user()->hasAnyRole(['bendahara_umum', 'ketua_yayasan']) ? 'true' : 'false' }};
        @else
        const isBendaharaOrKetua = false;
        @endauth

        const apiUrl = '{{ route('api.v1.rab.datatable') }}';
        const element = document.querySelector('#kt_rab_table');

        let activeFilters = { status: '', bulan: '', divisi_id: '' };
        let pendingRejectId = null;

        const columns = {
            nomor_rab: { title: 'Nomor RAB' },
        };

        if (isBendaharaOrKetua) {
            columns.divisi_id = { title: 'Divisi' };
        }

        columns.bulan_pengajuan = {
            title: 'Bulan Pengajuan',
            render: (data, type, row) => type.bulan_pengajuan_label ?? data,
            createdCell(cell) { cell.classList.add('text-center'); },
        };

        columns.total_kegiatan = {
            title: 'Total Kegiatan',
            createdCell(cell) { cell.classList.add('text-center'); },
        };

        columns.total_biaya_anggaran = {
            title: 'Total Biaya',
            render: (data, type, row) => `Rp ${type.total_biaya_anggaran_formatted ?? data}`,
            createdCell(cell) { cell.classList.add('text-right'); },
        };

        columns.status = {
            title: 'Status',
            render: (data, type, row) => `
                <span class="badge badge-${type.status_color ?? 'secondary'} badge-outline">
                    ${type.status_label ?? data}
                </span>
            `,
            createdCell(cell) { cell.classList.add('text-center'); },
        };

        columns.action = {
            title: 'Aksi',
            render: (data, type, row) => {
                const showUrl   = showRoute.replace(':id', type.id);
                const editUrl   = editRoute.replace(':id', type.id);
                const subUrl    = submitUrl.replace(':id', type.id);
                const canUrl    = cancelUrl.replace(':id', type.id);
                const appUrl    = approveUrl.replace(':id', type.id);
                const rejUrl    = rejectUrl.replace(':id', type.id);

                let buttons = `
                    <a href="${showUrl}" class="btn btn-icon btn-sm btn-clear btn-primary" title="Lihat RAB">
                        <i class="ki-filled ki-eye"></i>
                    </a>
                `;

                if (type.can_edit) {
                    buttons += `
                        <a href="${editUrl}" class="btn btn-icon btn-sm btn-clear btn-warning" title="Edit RAB">
                            <i class="ki-filled ki-notepad-edit"></i>
                        </a>
                    `;
                }

                if (type.can_submit) {
                    buttons += `
                        <button onclick="doSubmit('${subUrl}')" class="btn btn-icon btn-sm btn-clear btn-success" title="Submit RAB">
                            <i class="ki-filled ki-check"></i>
                        </button>
                    `;
                }

                if (type.can_cancel) {
                    buttons += `
                        <button onclick="doCancel('${canUrl}')" class="btn btn-icon btn-sm btn-clear btn-danger" title="Batalkan RAB">
                            <i class="ki-filled ki-cross-circle"></i>
                        </button>
                    `;
                }

                if (type.can_approve) {
                    buttons += `
                        <button onclick="doApprove('${appUrl}')" class="btn btn-icon btn-sm btn-clear btn-success" title="Setujui RAB">
                            <i class="ki-filled ki-check-circle"></i>
                        </button>
                        <button onclick="openRejectModal('${rejUrl}')" class="btn btn-icon btn-sm btn-clear btn-danger" title="Tolak RAB">
                            <i class="ki-filled ki-minus-circle"></i>
                        </button>
                    `;
                }

                return buttons;
            },
            createdCell(cell) { cell.classList.add('text-center'); },
        };

        const dataTableOptions = {
            apiEndpoint: apiUrl,
            pageSize: 10,
            searchQuery: '',
            infoEmpty: 'Data Kosong',
            stateSave: false,
            columns,
            serverParams: () => activeFilters,
        };

        const dataTable = new KTDataTable(element, dataTableOptions);

        document.getElementById('refresh-btn').addEventListener('click', () => dataTable.reload());

        document.getElementById('apply-filter-btn').addEventListener('click', () => {
            activeFilters.status  = document.getElementById('filter-status').value;
            activeFilters.bulan   = document.getElementById('filter-bulan').value;
            const divisiEl = document.getElementById('filter-divisi');
            activeFilters.divisi_id = divisiEl ? divisiEl.value : '';
            dataTable.reload();
        });

        document.getElementById('reset-filter-btn').addEventListener('click', () => {
            document.getElementById('filter-status').value = '';
            document.getElementById('filter-bulan').value  = '';
            const divisiEl = document.getElementById('filter-divisi');
            if (divisiEl) divisiEl.value = '';
            activeFilters = { status: '', bulan: '', divisi_id: '' };
            dataTable.reload();
        });

        function postAction(url, body = {}) {
            return fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(body),
            }).then(r => r.json());
        }

        function showApiError(messages) {
            const container = document.getElementById('error-container');
            const attention = document.getElementById('error-api-attention');
            if (container && attention) {
                container.innerHTML = (Array.isArray(messages) ? messages : [messages])
                    .map(m => `<p class="text-gray-700 text-2sm font-normal">${m}</p>`).join('');
                attention.classList.remove('hidden');
                attention.scrollIntoView({ behavior: 'smooth' });
            }
        }

        function doSubmit(url) {
            if (!confirm('Yakin ingin mengajukan RAB ini?')) return;
            postAction(url).then(res => {
                if (res.error) { showApiError(res.messages); return; }
                dataTable.reload();
            });
        }

        function doCancel(url) {
            if (!confirm('Yakin ingin membatalkan RAB ini?')) return;
            postAction(url).then(res => {
                if (res.error) { showApiError(res.messages); return; }
                dataTable.reload();
            });
        }

        function doApprove(url) {
            if (!confirm('Yakin ingin menyetujui RAB ini?')) return;
            postAction(url).then(res => {
                if (res.error) { showApiError(res.messages); return; }
                dataTable.reload();
            });
        }

        function openRejectModal(url) {
            pendingRejectId = url;
            document.getElementById('reject-catatan').value = '';
            KTModal.getInstance(document.getElementById('modal_reject_rab')).show();
        }

        document.getElementById('confirm-reject-btn').addEventListener('click', () => {
            const catatan = document.getElementById('reject-catatan').value.trim();
            if (!catatan) { alert('Catatan penolakan wajib diisi.'); return; }
            postAction(pendingRejectId, { catatan }).then(res => {
                KTModal.getInstance(document.getElementById('modal_reject_rab')).hide();
                if (res.error) { showApiError(res.messages); return; }
                dataTable.reload();
            });
        });
    </script>
@endpush
