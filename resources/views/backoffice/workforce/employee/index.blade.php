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
                <a class="btn btn-sm text-center btn-success" href="{{ route('workforce.employee.create') }}">
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
                                                    Kode
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
                                                    Divisi
                                                </span>
                                            </span>
                                        </th>
                                        <th class="text-center" data-datatable-column="departemen">
                                            <span class="sort">
                                                <span class="sort-label">
                                                    Departemen
                                                </span>
                                            </span>
                                        </th>
                                        <th class="text-center" data-datatable-column="jabatan">
                                            <span class="sort">
                                                <span class="sort-label">
                                                    Jabatan
                                                </span>
                                            </span>
                                        </th>
                                        <th class="text-center" data-datatable-column="is_active">
                                            <span class="sort">
                                                <span class="sort-label">
                                                    is Active
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
        const showRoute = "{{ route('workforce.employee.show', ':id') }}"; // Pass route pattern to JS
        const editRoute = "{{ route('workforce.employee.edit', ':id') }}"; // Pass route pattern to JS
        const editUserRoute = "{{ route('workforce.employee.edit.user', ':id') }}"; // Pass route pattern to JS

    </script>
    <!-- End - Define Route -->

    <script type="text/javascript">
        const apiUrl = '{{ route('api.v1.workforce.employee.datatable') }}';
        const deleteUrl = "#";
        const element = document.querySelector('#kt_remote_table');

        const dataTableOptions = {
            apiEndpoint: apiUrl,
            pageSize: 10,
            search:"",
            stateSave: false,
            infoEmpty: "Data Kosong",
            columns: {
                employee_code: {
                    title: 'Kode',
                    render: (item, row, context) => {
                        return ` ${item}`;
                    },
                    createdCell(cell) {
                        cell.classList.add('text-center');
                    },
                },
                name: {
                    title: 'Nama',
                    render: (data, type, row) => {
                        const email = type.user ? type.user.email : 'Email Not Found';
                        return `
                            <div class="flex flex-col items-center text-center">
                                <span class="font-semibold text-gray-800">${type.name}</span>
                                <span class="text-sm text-gray-500">${email}</span>
                            </div>
                        `;
                    },
                    createdCell(cell) {
                        cell.classList.add('text-center');
                    },
                },
                divisi: {
                    title: 'divisi',
                    render: (data, type, row) => {
                        return ` ${type.division.name}`;
                    },
                    createdCell(cell) {
                        cell.classList.add('text-center');
                    },
                },
                departemen: {
                    title: 'departemen',
                    render: (data, type, row) => {
                        return ` ${type.department.name}`;
                    },
                    createdCell(cell) {
                        cell.classList.add('text-center');
                    },
                },
                jabatan: {
                    title: 'Jabatan',
                    render: (data, type, row) => {
                        return ` ${type.role.name}`;
                    },
                    createdCell(cell) {
                        cell.classList.add('text-center');
                    },
                },
                is_active: {
                    title: 'is Active',
                    render: (data, type, row) => {
                        return `
                    <div class="flex items-center gap-1.5">
                        <span class="badge badge-dot size-2 ${type.is_active_color}"></span>
                        <span class="leading-none text-gray-700"> ${type.is_active_name}</span>
                    </div>
                    `;
                    },
                    createdCell(cell) {
                        cell.classList.add('text-center');
                    },
                },
                action: {
                    title: 'Action',
                    render: (data, type, row) => {

                        const showUrl = showRoute.replace(':id', type.id);
                        const editUrl = editRoute.replace(':id', type.id);
                        const editUserUrl = editUserRoute.replace(':id', type.id);

                        return `
                        <a href="${editUserUrl}" class="btn btn-icon btn-sm btn-clear btn-info" data-tooltip="#user">
                            <i class="ki-filled ki-user-tick"></i>
                        </a>
                        <div class="tooltip transition-opacity duration-300" id="user">
                            User {{ $data['pageTitle'] }}
                        </div>

                        <a href="${showUrl}" class="btn btn-icon btn-sm btn-clear btn-primary" data-tooltip="#show">
                            <i class="ki-filled ki-eye"></i>
                        </a>
                        <div class="tooltip transition-opacity duration-300" id="show">
                            Lihat {{ $data['pageTitle'] }}
                        </div>

                        <a href="${editUrl}" class="btn btn-icon btn-sm btn-clear btn-warning" data-tooltip="#edit">
                            <i class="ki-filled ki-notepad-edit"></i>
                        </a>
                        <div class="tooltip transition-opacity duration-300" id="edit">
                            Ubah {{ $data['pageTitle'] }}
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
        dataTable.search('');

        const refreshTable = document.getElementById('refresh-btn').addEventListener('click', function() {
            const searchInput = document.querySelector('[data-datatable-search="#kt_remote_table"]');
            searchInput.value = '';
            dataTable.reload();
        });

    </script>
@endpush
