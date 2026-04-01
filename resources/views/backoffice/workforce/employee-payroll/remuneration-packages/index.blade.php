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
                                                    Jabatan
                                                </span>
                                                <span class="sort-icon">
                                                </span>
                                            </span>
                                        </th>
                                        <th class=" text-center" data-datatable-column="remuneration_packages_count">
                                            <span class="sort">
                                                <span class="sort-label">
                                                    Total Paket Remunerasi
                                                </span>
                                                <span class="sort-icon">
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
        const showRoute = "{{ route('workforce.employee-role-remuneration-package.show', ':id') }}";
        const editRoute = "{{ route('workforce.employee-role-remuneration-package.edit', ':id') }}";
    </script>
    <!-- End - Define Route -->

    <script type="text/javascript">
        const apiUrl = '{{ route('api.v1.workforce.employee-role-remuneration-package.datatable') }}';
        const deleteUrl = "#";
        const pageTitle = '{{ $data['pageTitle'] }}';
        const element = document.querySelector('#kt_remote_table');

        const dataTableOptions = {
            apiEndpoint: apiUrl,
            pageSize: 10,
            search: "",
            stateSave: false,
            infoEmpty: "Data Kosong",
            columns: {
                name: {
                    title: 'Jabatan',
                    render: (item, row, context) => {
                        return `${item}`;
                    }
                },
                remuneration_packages_count: {
                    title: 'Total Paket Remunerasi',
                    render: (item, row, context) => {
                        return `${item} Paket`;
                    }
                },
                action: {
                    title: 'Action',
                    render: (item, row, context) => {
                        const showUrl = showRoute.replace(':id', row.id);
                        const editUrl = editRoute.replace(':id', row.id);

                        return `

                        <a href="${showUrl}" class="btn btn-icon btn-sm btn-clear btn-primary" data-tooltip="#show">
                            <i class="ki-filled ki-eye"></i>
                        </a>
                        <div class="tooltip transition-opacity duration-300" id="show-${row.id}">
                            Lihat ${pageTitle}
                        </div>

                        <a href="${editUrl}" class="btn btn-icon btn-sm btn-clear btn-warning" data-tooltip="#edit">
                            <i class="ki-filled ki-notepad-edit"></i>
                        </a>
                        <div class="tooltip transition-opacity duration-300" id="edit-${row.id}">
                            Ubah ${pageTitle}
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
