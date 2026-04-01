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
                <a class="btn btn-sm text-center btn-success" href="{{ route('workforce.employee-payroll-comben.create') }}">
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
                                        <th class=" text-center" data-datatable-column="name">
                                            <span class="sort">
                                                <span class="sort-label">
                                                    Name
                                                </span>
                                                <span class="sort-icon">
                                                </span>
                                            </span>
                                        </th>
                                        <th class=" text-center" data-datatable-column="date">
                                            <span class="sort">
                                                <span class="sort-label">
                                                    Tanggal
                                                </span>
                                            </span>
                                        </th>
                                        <th class=" text-center" data-datatable-column="note">
                                            <span class="sort">
                                                <span class="sort-label">
                                                    Note
                                                </span>
                                            </span>
                                        </th>
                                        <th class=" text-center" data-datatable-column="value">
                                            <span class="sort">
                                                <span class="sort-label">
                                                    Nominal
                                                </span>
                                            </span>
                                        </th>
                                        <th class=" text-center" data-datatable-column="is_paid">
                                            <span class="sort">
                                                <span class="sort-label">
                                                    Telah Dibayar?
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
        'mainTitle' => 'Hapus Compensation Benefit?',
        'mainContent' => 'Apakah anda yakin untuk menghapus compensation benefit ini?',
    ])
@endsection

@push('javascript')
    <!-- Begin - Define Route -->
    <script type="text/javascript">
        const showRoute = "{{ route('workforce.employee-payroll-comben.show', ':id') }}";
        const editRoute = "{{ route('workforce.employee-payroll-comben.edit', ':id') }}";
    </script>
    <!-- End - Define Route -->

    <script type="text/javascript">
        const apiUrl = '{{ route('api.v1.workforce.employee-payroll-comben.datatable') }}';
        const deleteUrl = "{{ route('api.v1.workforce.employee-payroll-comben.destroy', ':id') }}";
        const pageTitle = '{{ $data['pageTitle'] }}';
        const element = document.querySelector('#kt_remote_table');

        const dataTableOptions = {
            apiEndpoint: apiUrl,
            pageSize: 10,
            search: "",
            infoEmpty: "Data Kosong",
            stateSave: false, // Disable state persistence to avoid cross-table issues
            columns: {
                name: {
                    title: 'name',
                    render: (item, row, context) => {
                        return `${row.employee.name}`;
                    },
                    createdCell(cell) {
                        cell.classList.add('text-center');
                    }
                },
                date: {
                    title: 'Tanggal',
                    render: (item, row, context) => {
                        return `${row.formatted_date}`;
                    },
                    createdCell(cell) {
                        cell.classList.add('text-center');
                    }
                },
                note: {
                    title: 'note',
                    render: (item, row, context) => {
                        return `${row.note}`;
                    },
                    createdCell(cell) {
                        cell.classList.add('text-center');
                    }
                },
                value: {
                    title: 'nominal',
                    render: (item, row, context) => {
                        return `${row.formatted_value}`;
                    },
                    createdCell(cell) {
                        cell.classList.add('text-center');
                    }
                },
                is_paid: {
                    title: 'Telah Dibayar?',
                    render: (item, row, context) => {
                        return item ?
                            `<span class="badge badge-outline badge-pill badge-success">Sudah</span>`:
                            `<span class="badge badge-outline badge-pill badge-danger">Belum</span>`;
                    },
                    createdCell(cell) {
                        cell.classList.add('text-center');
                    }
                },
                action: {
                    title: 'Action',
                    render: (item, row, context) => {
                        const showUrl = showRoute.replace(':id', row.id);
                        const editUrl = editRoute.replace(':id', row.id);

                       const showButton = `
                            <a href="${showUrl}" class="btn btn-icon btn-sm btn-clear btn-primary" data-tooltip="#show-${row.id}">
                                <i class="ki-filled ki-eye"></i>
                            </a>
                            <div class="tooltip transition-opacity duration-300" id="show-${row.id}">
                                Lihat ${pageTitle}
                            </div>
                        `;

                        const editButton = `
                            <a href="${editUrl}" class="btn btn-icon btn-sm btn-clear btn-warning" data-tooltip="#edit-${row.id}">
                                <i class="ki-filled ki-notepad-edit"></i>
                            </a>
                            <div class="tooltip transition-opacity duration-300" id="edit-${row.id}">
                                Ubah ${pageTitle}
                            </div>
                        `;

                        const deleteButton = `
                            <button class="btn btn-icon btn-sm btn-clear btn-danger" data-tooltip="#delete-${row.id}" data-delete-id="${row.id}"  data-modal-toggle="#modal_confirm_delete" onclick="openDeleteModal(${row.id},'modal_confirm_delete')">
                                <i class="ki-filled ki-trash"></i>
                            </button>
                            <div class="tooltip transition-opacity duration-300" id="delete-${row.id}">
                                Hapus ${pageTitle}
                            </div>
                        `;

                        if (row.is_paid) {
                            return `${showButton}`;
                        }else{
                            return `${showButton} ${editButton} ${deleteButton}`;
                        }


                    },
                    createdCell(cell) {
                        cell.classList.add('text-center');
                    },
                },
            }
        };

        const dataTable = new KTDataTable(element, dataTableOptions);

        const refreshTable = document.getElementById('refresh-btn').addEventListener('click', function() {
            const searchInput = document.querySelector('[data-datatable-search="#kt_remote_table"]');
            searchInput.value = '';

            dataTable.search('');
        });

    </script>
@endpush
