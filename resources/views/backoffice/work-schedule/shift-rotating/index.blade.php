@extends('layouts.main')

@section('content')
    <!-- Container -->
    @include('backoffice.work-schedule.partials.submenu')
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
                <a class="btn btn-sm text-center btn-success" href="{{ route('work-schedule.shift-rotating.create') }}">
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
                                        <th class=" text-center" data-datatable-column="start_date">
                                            <span class="sort">
                                                <span class="sort-label">
                                                    Tgl. Mulai
                                                </span>
                                                <span class="sort-icon">
                                                </span>
                                            </span>
                                        </th>
                                        <th class=" text-center" data-datatable-column="end_date">
                                            <span class="sort">
                                                <span class="sort-label">
                                                    Tgl. Akhir
                                                </span>
                                                <span class="sort-icon">
                                                </span>
                                            </span>
                                        </th>
                                        <th class="text-center" data-datatable-column="shift_name">
                                            <span class="sort">
                                                <span class="sort-label">
                                                    Shift
                                                </span>
                                            </span>
                                        </th>
                                        <th class="text-center" data-datatable-column="employee_shifts_count">
                                            <span class="sort">
                                                <span class="sort-label">
                                                    Jml. Karyawan
                                                </span>
                                            </span>
                                        </th>
                                        <th class="text-center" data-datatable-column="already_generated">
                                            <span class="sort">
                                                <span class="sort-label">
                                                    Shift telah digenerate?
                                                </span>
                                            </span>
                                        </th>
                                        <th class="text-center" data-datatable-column="is_dayoff">
                                            <span class="sort">
                                                <span class="sort-label">
                                                    Kategori
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
        'mainTitle' => 'Hapus shift bergilir?',
        'mainContent' => 'Apakah anda yakin untuk menghapus shift bergilir ini?',
    ])
@endsection

@push('javascript')
    <!-- Begin - Define Route -->
    <script type="text/javascript">
        const showRoute = "{{ route('work-schedule.shift-rotating.show', ':id') }}"; // Pass route pattern to JS
        const editRoute = "{{ route('work-schedule.shift-rotating.edit', ':id') }}"; // Pass route pattern to JS
    </script>
    <!-- End - Define Route -->

    <script type="text/javascript">
        const apiUrl = '{{ route('api.v1.work-schedule.shift-rotating.datatable') }}';
        const deleteUrl = "{{ route('api.v1.work-schedule.shift-rotating.destroy', ':id') }}";
        const element = document.querySelector('#kt_remote_table');

        const dataTableOptions = {
            apiEndpoint: apiUrl,
            pageSize: 10,
            searchQuery: '',
            infoEmpty: 'Data Kosong',
            stateSave: false,
            columns: {
                start_date: {
                    title: 'Tgl. Mulai',
                    createdCell(cell) {
                        cell.classList.add('text-center');
                    }
                },
                end_date: {
                    title: 'Tgl. Akhir',
                    createdCell(cell) {
                        cell.classList.add('text-center');
                    },
                },
                shift: {
                    title: 'Shift',
                    createdCell(cell, cellData, rowData) {
                        const shiftName = cellData?.name || 'N/A';
                        cell.textContent = shiftName;
                        cell.classList.add('text-center');
                    },
                },
                employee_shifts_count: {
                    title: 'Jml. Karyawan',
                    createdCell(cell) {
                        cell.classList.add('text-center');
                    },
                    render: (item, row, context) => {
                        return ` ${item} Orang`;
                    }
                },
                already_generated: {
                    title: 'Shift telah digenerate?',
                    render: (item, row, context) => {
                        return `
                        <div class="flex items-center justify-center gap-1.5">
                            <span class="badge badge-dot size-2 ${row.already_generated_color}"></span>
                            <span class="leading-none text-gray-700"> ${row.already_generated_name}</span>
                        </div>
                        `;
                    },
                    createdCell(cell) {
                        cell.classList.add('text-center');
                    },
                },
                is_dayoff: {
                    title: 'Kategori',
                    createdCell(cell) {
                        cell.classList.add('text-center');
                    },
                    render: (item, row, context) => {
                        return item ?
                            `<span class="badge badge-outline badge-pill badge-danger">Jadwal Libur</span>` :
                            `<span class="badge badge-outline badge-pill badge-success">Jadwal Masuk</span>`;
                    },

                },
                action: {
                    title: 'Action',
                    render: (item, row, context) => {

                        const showUrl = showRoute.replace(':id', row.id);
                        const editUrl = editRoute.replace(':id', row.id);

                        const showButton = `<a href="${showUrl}" class="btn btn-icon btn-sm btn-clear btn-primary" data-tooltip="#show">
                            <i class="ki-filled ki-eye"></i>
                        </a>
                        <div class="tooltip transition-opacity duration-300" id="show">
                            Lihat {{ $data['pageTitle'] }}
                        </div>`;
                        const editButton = `<a href="${editUrl}" class="btn btn-icon btn-sm btn-clear btn-warning" data-tooltip="#edit">
                            <i class="ki-filled ki-notepad-edit"></i>
                        </a>
                        <div class="tooltip transition-opacity duration-300" id="edit">
                            Ubah {{ $data['pageTitle'] }}
                        </div>`;

                        const deleteButton = `<button class="btn btn-icon btn-sm btn-clear btn-danger" id="delete" data-delete-id="${row.id}"  data-modal-toggle="#modal_confirm_delete" onclick="openDeleteModal(${row.id},'modal_confirm_delete')">
                            <i class="ki-filled ki-trash"></i>
                        </button>
                        <div class="tooltip transition-opacity duration-300" id="delete">
                            Hapus {{ $data['pageTitle'] }}
                        </div>`;

                        return row.already_generated ?  showButton + deleteButton : showButton + editButton + deleteButton;
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
