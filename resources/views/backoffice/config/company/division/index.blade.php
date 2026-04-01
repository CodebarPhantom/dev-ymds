@extends('layouts.main')

@section('content')
    <!-- Container -->
    @include('backoffice.config.company.partials.submenu')
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
                <a class="btn btn-sm text-center btn-success" href="{{ route('division.create') }}">
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
                                                    Nama
                                                </span>
                                                <span class="sort-icon">
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
    <!-- End of Container -->
    @include('partials.modal-confirm-delete', [
        'mainTitle' => 'Hapus divisi?',
        'mainContent' => 'Apakah anda yakin untuk menghapus divisi ini?',
    ])
@endsection

@push('javascript')
    <!-- Begin - Define Route -->
    <script type="text/javascript">
        const showRoute = "{{ route('division.show', ':id') }}"; // Pass route pattern to JS
        const editRoute = "{{ route('division.edit', ':id') }}"; // Pass route pattern to JS
    </script>
    <!-- End - Define Route -->

    <script type="text/javascript">
        const apiUrl = '{{ route('api.v1.division.datatable') }}';
        const deleteUrl = "{{ route('api.v1.division.destroy', ':id') }}";
        const element = document.querySelector('#kt_remote_table');

        const dataTableOptions = {
            apiEndpoint: apiUrl,
            pageSize: 10,
            searchQuery: '',
            infoEmpty: 'Data Kosong',
            stateSave: false,
            columns: {
                name: {
                    title: 'Nama',
                },
                is_active: {
                    title: 'is Active',
                    render: (data, type, row) => {
                        return `
                    <div class="flex items-center gap-1.5">
                        <span class="badge badge-dot size-2 ${type.isActiveColor}"></span>
                        <span class="leading-none text-gray-700"> ${type.isActiveName}</span>
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

                        return `
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
                        <button class="btn btn-icon btn-sm btn-clear btn-danger" data-tooltip="#delete" data-delete-id="${type.id}"  data-modal-toggle="#modal_confirm_delete" onclick="openDeleteModal(${type.id},'modal_confirm_delete')">
                            <i class="ki-filled ki-trash"></i>
                        </button>
                        <div class="tooltip transition-opacity duration-300" id="delete">
                            Hapus {{ $data['pageTitle'] }}
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
