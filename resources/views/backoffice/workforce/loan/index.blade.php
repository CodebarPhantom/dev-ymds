@extends('layouts.main')

@section('content')
    <!-- Container -->
    {{-- @include('backoffice.config.company.partials.submenu') --}}
    <!-- Container -->
    <div class="container-fixed">
        <div class="flex flex-wrap items-center lg:items-end justify-between gap-5 pb-7.5">
            <div class="flex flex-col justify-center gap-2">
                <h1 class="text-xl font-bold leading-none text-gray-900">
                    {{ $data['pageTitle'] }}
                </h1>
                {{-- <div class="flex items-center gap-2 text-sm font-normal text-gray-700">
                    Central Hub for Personal Customization
                </div> --}}
            </div>
            <div class="flex items-center gap-2.5">
                <div class="relative">
                    <i
                        class="ki-filled ki-magnifier leading-none text-md text-gray-500 absolute top-1/2 left-0 -translate-y-1/2 ml-3">
                    </i>
                    <input class="input input-sm pl-8 text-center" data-datatable-search="#kt_remote_table"
                        placeholder="Cari Data" type="text">
                </div>
                <button id="refresh-btn" class="btn btn-sm text-center btn-info">
                    <i class="ki-filled ki-arrows-circle"></i>
                </button>
                <a class="btn btn-sm text-center btn-success" href="{{ route('workforce.employee-loan.create') }}">
                    <i class="ki-filled ki-plus"></i>Tambah {{ $data['pageTitle'] }}
                </a>

            </div>
        </div>
    </div>

    @include('partials.attention')

    <div class="container-fixed">
        <div class="grid pb-7.5">
            <div class="card card-grid min-w-full">
                <div class="card-body">
                    <div id="kt_remote_table">
                        <div class="scrollable-x-auto">
                            <table class="table table-auto table-border align-middle text-gray-700 font-medium text-sm"
                                data-datatable-table="true">
                                <thead>
                                    <tr>
                                        <th class=" text-center" data-datatable-column="employee_code">
                                            <span class="sort">
                                                <span class="sort-label">
                                                    Tanggal Peminjaman
                                                </span>
                                                <span class="sort-icon">
                                                </span>
                                            </span>
                                        </th>
                                        <th class=" text-center" data-datatable-column="name">
                                            <span class="sort">
                                                <span class="sort-label">
                                                    Nama
                                                </span>
                                                <span class="sort-icon">
                                                </span>
                                            </span>
                                        </th>
                                        <th class="text-center" data-datatable-column="divisi">
                                            <span class="sort">
                                                <span class="sort-label">
                                                    Nilai Pinjaman
                                                </span>
                                            </span>
                                        </th>
                                        <th class="text-center" data-datatable-column="departemen">
                                            <span class="sort">
                                                <span class="sort-label">
                                                    Nilai Angusuran
                                                </span>
                                            </span>
                                        </th>
                                        <th class="text-center" data-datatable-column="jabatan">
                                            <span class="sort">
                                                <span class="sort-label">
                                                    Periode Angsuran
                                                </span>
                                            </span>
                                        </th>
                                        <th class="text-center" data-datatable-column="is_active">
                                            <span class="sort">
                                                <span class="sort-label">
                                                    Status
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

    @include('partials.modal-confirm-delete', [
        'mainTitle' => 'Hapus pinjaman?',
        'mainContent' => 'Apakah anda yakin untuk menghapus pinjaman ini?',
    ])
@endsection

@push('javascript')
    <!-- Begin - Define Route -->
    <script type="text/javascript">
        const showRoute = "{{ route('workforce.employee-loan.show', ':id') }}";
        const editRoute = "{{ route('workforce.employee-loan.edit', ':id') }}";
        const acceleratedRepaymentRoute = "{{ route('workforce.employee-loan.accelerated-repayment', ':id') }}";
    </script>
    <!-- End - Define Route -->

    <script type="text/javascript">
        function confirmAccleratedRepayment(event) {
            if (!confirm("Are you sure you want to approve this accclerated repayment?")) {
                event.preventDefault(); // Prevent form submission if user cancels
                return false;
            }
            return true; // Allow form submission if user confirms
        }
    </script>

    <script type="text/javascript">
        const apiUrl = '{{ route('api.v1.workforce.employee-loan.datatable') }}';
        const deleteUrl = "{{ route('api.v1.workforce.employee-loan.destroy', ':id') }}";;
        const element = document.querySelector('#kt_remote_table');

        const dataTableOptions = {
            apiEndpoint: apiUrl,
            pageSize: 10,
            search: "",
            stateSave: false,
            infoEmpty: "Data Kosong",
            columns: {
                loan_date: {
                    title: 'Tanggal Peminjaman',
                    render: (item, row, context) => {
                        return ` ${row.formatted_loan_date}`;
                    },
                    createdCell(cell) {
                        cell.classList.add('text-center');
                    },
                },
                employee_name: {
                    title: 'Nama',
                    render: (item, row, context) => {
                        return ` ${row.employee.name}`;
                    },
                    createdCell(cell) {
                        cell.classList.add('text-center');
                    },
                },
                loan_amount: {
                    title: 'Nilai Pinjaman',
                    render: (item, row, context) => {
                        return ` ${row.formatted_loan_amount}`;
                    },
                    createdCell(cell) {
                        cell.classList.add('text-center');
                    },
                },
                installment_amount: {
                    title: 'Nilai Angsuran',
                    render: (item, row, context) => {
                        return ` ${row.formatted_installment_amount}`;
                    },
                    createdCell(cell) {
                        cell.classList.add('text-center');
                    },
                },
                installment_period: {
                    title: 'Periode Angsuran',
                    render: (item, row, context) => {
                        return ` ${item}X`;
                    },
                    createdCell(cell) {
                        cell.classList.add('text-center');
                    },
                },
                status: {
                    title: 'Status',
                    render: (item, row, context) => {
                        return `
                        <span class="badge badge-pill badge-outline badge-${row.status_color}">
                            ${row.status_label}
                        </span>`;
                    },
                    createdCell(cell) {
                        cell.classList.add('text-center');
                    },
                },
                action: {
                    title: 'Action',
                    render: (item, row, context) => {

                        const showUrl = showRoute.replace(':id', row.id);
                        const editUrl = editRoute.replace(':id', row.id);
                        const acceleratedRepaymentUrl = acceleratedRepaymentRoute.replace(':id', row.id)

                        const showButton = `<a href="${showUrl}" class="btn btn-icon btn-sm btn-clear btn-primary" data-tooltip="#show${row.id}">
                            <i class="ki-filled ki-eye"></i>
                        </a>
                        <div class="tooltip transition-opacity duration-300" id="show${row.id}">
                            Lihat {{ $data['pageTitle'] }}
                        </div>`;
                        const editButton = `<a href="${editUrl}" class="btn btn-icon btn-sm btn-clear btn-warning" data-tooltip="#edit${row.id}">
                            <i class="ki-filled ki-notepad-edit"></i>
                        </a>
                        <div class="tooltip transition-opacity duration-300" id="edit${row.id}">
                            Ubah {{ $data['pageTitle'] }}
                        </div>`;

                        const deleteButton = `<button class="btn btn-icon btn-sm btn-clear btn-danger" id="delete" data-tooltip="#delete${row.id}" data-delete-id="${row.id}"  data-modal-toggle="#modal_confirm_delete" onclick="openDeleteModal(${row.id},'modal_confirm_delete')">
                            <i class="ki-filled ki-trash"></i>
                        </button>
                        <div class="tooltip transition-opacity duration-300" id="delete${row.id}">
                            Hapus {{ $data['pageTitle'] }}
                        </div>`;

                        const acceleratedRepaymentButton = `
                            <form action="${acceleratedRepaymentUrl}" method="POST" onsubmit="return confirmAccleratedRepayment(event)" class="inline">
                                @csrf
                                <input type="hidden" name="_method" value="PUT">
                                <button type="submit" class="btn btn-icon btn-sm btn-clear btn-info" data-tooltip="#confirmAccleratedRepayment${row.id}">
                                    <i class="ki-filled ki-calculator"></i>
                                </button>
                            </form>
                            <div class="tooltip transition-opacity duration-300" id="confirmAccleratedRepayment${row.id}">
                                Pelunasan {{ $data['pageTitle'] }}
                            </div>
                        `;

                        return row.status === 1 ?
                            showButton + acceleratedRepaymentButton :
                            row.status === 0 ?
                            showButton + editButton + deleteButton + acceleratedRepaymentButton :
                            showButton;

                    },
                    createdCell(cell) {
                        cell.classList.add('text-center');
                    },
                },
            }
        };
        const dataTable = new KTDataTable(element, dataTableOptions);
        dataTable.search('');

        const refreshTable = document.getElementById('refresh-btn').addEventListener('click', function() {
            const searchInput = document.querySelector('[data-datatable-search="#kt_remote_table"]');
            searchInput.value = '';
            dataTable.reload();
        });
    </script>
@endpush
