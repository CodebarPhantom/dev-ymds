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
                    <input class="input input-sm pl-8 text-center" data-datatable-search="#kt_purchase_request_table"
                        placeholder="Cari Data" value="" type="text">
                </div>
                <button id="refresh-btn" class="btn btn-sm text-center btn-info">
                    <i class="ki-filled ki-arrows-circle"></i>
                </button>
                @can('createPolicy', App\Models\PurchaseRequest::class)
                    <a class="btn btn-sm text-center btn-success" href="{{ route('purchase-requests.create') }}">
                        <i class="ki-filled ki-plus"></i>Buat Pengajuan
                    </a>
                @endcan
            </div>
        </div>
    </div>

    @include('partials.attention')

    <div class="container-fixed">
        <div class="grid pb-7.5">
            <div class="card card-grid min-w-full">
                <div class="card-body">
                    <div id="kt_purchase_request_table">
                        <div class="scrollable-x-auto">
                            <table class="table table-auto table-border align-middle text-gray-700 font-medium text-sm"
                                data-datatable-table="true">
                                <thead>
                                    <tr>
                                        <th class="text-center" data-datatable-column="nomor_pengajuan">
                                            <span class="sort"><span class="sort-label">Nomor Pengajuan</span><span class="sort-icon"></span></span>
                                        </th>
                                        @if($data['isPrivilegedUser'])
                                            <th class="text-center" data-datatable-column="divisi_id">
                                                <span class="sort"><span class="sort-label">Divisi</span><span class="sort-icon"></span></span>
                                            </th>
                                        @endif
                                        <th class="text-center" data-datatable-column="judul_pengajuan">
                                            <span class="sort"><span class="sort-label">Judul Pengajuan</span><span class="sort-icon"></span></span>
                                        </th>
                                        <th class="text-center" data-datatable-column="tanggal_dibutuhkan">
                                            <span class="sort"><span class="sort-label">Tgl. Dibutuhkan</span><span class="sort-icon"></span></span>
                                        </th>
                                        <th class="text-center" data-datatable-column="total_item">
                                            <span class="sort"><span class="sort-label">Total Item</span><span class="sort-icon"></span></span>
                                        </th>
                                        <th class="text-center" data-datatable-column="total_biaya_estimasi">
                                            <span class="sort"><span class="sort-label">Total Biaya Estimasi</span><span class="sort-icon"></span></span>
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

    <!-- Modal Approve -->
    <div class="modal hidden" id="modal_approve_pr" data-modal="true">
        <div class="modal-content max-w-md top-[15%]">
            <div class="modal-header">
                <h3 class="modal-title">Setujui Pengajuan</h3>
                <button class="btn btn-xs btn-icon btn-light" data-modal-dismiss="true">
                    <i class="ki-outline ki-cross"></i>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-gray-700 text-sm">Yakin ingin menyetujui pengajuan ini?</p>
                <div class="flex flex-col gap-3 mt-3">
                    <label class="form-label">Catatan (opsional)</label>
                    <textarea id="approve-catatan" class="textarea" rows="3" placeholder="Catatan persetujuan..."></textarea>
                </div>
            </div>
            <div class="modal-footer justify-end">
                <button class="btn btn-sm btn-light" data-modal-dismiss="true">Batal</button>
                <button id="confirm-approve-btn" class="btn btn-sm btn-success">Setujui</button>
            </div>
        </div>
    </div>
    <!-- End Modal Approve -->

    <!-- Modal Reject -->
    <div class="modal hidden" id="modal_reject_pr" data-modal="true">
        <div class="modal-content max-w-md top-[15%]">
            <div class="modal-header">
                <h3 class="modal-title">Tolak Pengajuan</h3>
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
                <button id="confirm-reject-btn" class="btn btn-sm btn-danger">Tolak Pengajuan</button>
            </div>
        </div>
    </div>
    <!-- End Modal Reject -->

    <!-- Modal Cancel -->
    <div class="modal hidden" id="modal_cancel_pr" data-modal="true">
        <div class="modal-content max-w-md top-[15%]">
            <div class="modal-header">
                <h3 class="modal-title">Batalkan Pengajuan</h3>
                <button class="btn btn-xs btn-icon btn-light" data-modal-dismiss="true">
                    <i class="ki-outline ki-cross"></i>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-gray-700 text-sm">Yakin ingin membatalkan pengajuan ini? Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-footer justify-end">
                <button class="btn btn-sm btn-light" data-modal-dismiss="true">Tidak</button>
                <button id="confirm-cancel-btn" class="btn btn-sm btn-danger">Ya, Batalkan</button>
            </div>
        </div>
    </div>
    <!-- End Modal Cancel -->
@endsection

@push('javascript')
    <script type="text/javascript">
        const showRoute             = "{{ route('purchase-requests.show', ':id') }}";
        const editRoute             = "{{ route('purchase-requests.edit', ':id') }}";
        const submitUrl             = "{{ route('api.v1.purchase-requests.submit', ':id') }}";
        const cancelUrl             = "{{ route('api.v1.purchase-requests.cancel', ':id') }}";
        const approveUrl            = "{{ route('api.v1.purchase-requests.approve', ':id') }}";
        const rejectUrl             = "{{ route('api.v1.purchase-requests.reject', ':id') }}";
        const startPurchasingUrl    = "{{ route('api.v1.purchase-requests.start-purchasing', ':id') }}";
        const isPrivilegedUser      = {{ $data['isPrivilegedUser'] ? 'true' : 'false' }};

        const apiUrl = '{{ route('api.v1.purchase-requests.datatable') }}';
        const element = document.querySelector('#kt_purchase_request_table');

        let activeFilters = {};
        let pendingApproveUrl = null;
        let pendingRejectUrl = null;
        let pendingCancelUrl = null;

        const columns = {
            nomor_pengajuan: { title: 'Nomor Pengajuan' },
        };

        if (isPrivilegedUser) {
            columns.divisi_id = { title: 'Divisi' };
        }

        columns.judul_pengajuan = {
            title: 'Judul Pengajuan',
        };

        columns.tanggal_dibutuhkan = {
            title: 'Tgl. Dibutuhkan',
            createdCell(cell) { cell.classList.add('text-center'); },
        };

        columns.total_item = {
            title: 'Total Item',
            createdCell(cell) { cell.classList.add('text-center'); },
        };

        columns.total_biaya_estimasi = {
            title: 'Total Biaya Estimasi',
            render: (data, type, row) => `Rp ${type.total_biaya_estimasi_formatted ?? data}`,
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
                const spUrl     = startPurchasingUrl.replace(':id', type.id);

                const status = type.status;
                const rejectedStatuses = ['REJECTED_BENDAHARA', 'REJECTED_KETUA'];
                const purchasingStatuses = ['APPROVED', 'PURCHASING', 'PARTIALLY_PURCHASED'];

                let buttons = `
                    <a href="${showUrl}" class="btn btn-icon btn-sm btn-clear btn-primary" title="Lihat Pengajuan">
                        <i class="ki-filled ki-eye"></i>
                    </a>
                `;

                // Edit & Resubmit: only for REJECTED statuses
                if (rejectedStatuses.includes(status) && type.can_edit) {
                    buttons += `
                        <a href="${editUrl}" class="btn btn-icon btn-sm btn-clear btn-warning" title="Edit & Resubmit">
                            <i class="ki-filled ki-notepad-edit"></i>
                        </a>
                    `;
                }

                // Submit: for DRAFT
                if (type.can_submit) {
                    buttons += `
                        <button onclick="doSubmit('${subUrl}')" class="btn btn-icon btn-sm btn-clear btn-success" title="Ajukan">
                            <i class="ki-filled ki-check"></i>
                        </button>
                    `;
                }

                // Approve/Reject: for Bendahara (PENDING_BENDAHARA) and Ketua (PENDING_KETUA)
                if (type.can_approve) {
                    buttons += `
                        <button onclick="openApproveModal('${appUrl}')" class="btn btn-icon btn-sm btn-clear btn-success" title="Setujui">
                            <i class="ki-filled ki-check-circle"></i>
                        </button>
                        <button onclick="openRejectModal('${rejUrl}')" class="btn btn-icon btn-sm btn-clear btn-danger" title="Tolak">
                            <i class="ki-filled ki-minus-circle"></i>
                        </button>
                    `;
                }

                // Proses Pembelian: for Sarpras on APPROVED, PURCHASING, PARTIALLY_PURCHASED
                if (type.can_start_purchasing && purchasingStatuses.includes(status)) {
                    buttons += `
                        <button onclick="doStartPurchasing('${spUrl}')" class="btn btn-icon btn-sm btn-clear btn-info" title="Proses Pembelian">
                            <i class="ki-filled ki-basket"></i>
                        </button>
                    `;
                }

                // Cancel: only for cancellable statuses
                if (type.can_cancel) {
                    buttons += `
                        <button onclick="openCancelModal('${canUrl}')" class="btn btn-icon btn-sm btn-clear btn-danger" title="Batalkan">
                            <i class="ki-filled ki-cross-circle"></i>
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
            if (!confirm('Yakin ingin mengajukan pengajuan ini?')) return;
            postAction(url).then(res => {
                if (res.error) { showApiError(res.messages); return; }
                dataTable.reload();
            });
        }

        function doStartPurchasing(url) {
            if (!confirm('Yakin ingin memulai proses pembelian untuk pengajuan ini?')) return;
            postAction(url).then(res => {
                if (res.error) { showApiError(res.messages); return; }
                dataTable.reload();
            });
        }

        function openApproveModal(url) {
            pendingApproveUrl = url;
            document.getElementById('approve-catatan').value = '';
            KTModal.getInstance(document.getElementById('modal_approve_pr')).show();
        }

        function openRejectModal(url) {
            pendingRejectUrl = url;
            document.getElementById('reject-catatan').value = '';
            KTModal.getInstance(document.getElementById('modal_reject_pr')).show();
        }

        function openCancelModal(url) {
            pendingCancelUrl = url;
            KTModal.getInstance(document.getElementById('modal_cancel_pr')).show();
        }

        document.getElementById('confirm-approve-btn').addEventListener('click', () => {
            const catatan = document.getElementById('approve-catatan').value.trim();
            postAction(pendingApproveUrl, { catatan }).then(res => {
                KTModal.getInstance(document.getElementById('modal_approve_pr')).hide();
                if (res.error) { showApiError(res.messages); return; }
                dataTable.reload();
            });
        });

        document.getElementById('confirm-reject-btn').addEventListener('click', () => {
            const catatan = document.getElementById('reject-catatan').value.trim();
            if (!catatan) { alert('Catatan penolakan wajib diisi.'); return; }
            postAction(pendingRejectUrl, { catatan }).then(res => {
                KTModal.getInstance(document.getElementById('modal_reject_pr')).hide();
                if (res.error) { showApiError(res.messages); return; }
                dataTable.reload();
            });
        });

        document.getElementById('confirm-cancel-btn').addEventListener('click', () => {
            postAction(pendingCancelUrl).then(res => {
                KTModal.getInstance(document.getElementById('modal_cancel_pr')).hide();
                if (res.error) { showApiError(res.messages); return; }
                dataTable.reload();
            });
        });
    </script>
@endpush
