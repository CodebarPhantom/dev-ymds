@extends('layouts.main')

@section('content')
    <!-- Container -->
    @include('backoffice.my-activity.partials.submenu')
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
                <button class="btn btn-sm text-center bg-[#4b5563] text-white">
                    <i class="ki-filled ki-calendar"></i> &nbsp; Tersisa {{ $data['remainingLeave'] }} Hari Cuti
                 </button>
                <button id="refresh-btn" class="btn btn-sm text-center btn-info">
                    <i class="ki-filled ki-arrows-circle"></i>
                </button>
                <a class="btn btn-sm text-center btn-success" href="{{ route('my-activity.my-leave.create') }}">
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
                                        <th class=" text-center" data-datatable-column="type">
                                            <span class="sort">
                                                <span class="sort-label">
                                                    Tipe
                                                </span>
                                            </span>
                                        </th>
                                        <th class="text-center" data-datatable-column="start_date">
                                            <span class="sort">
                                                <span class="sort-label">
                                                    Dari Tanggal
                                                </span>
                                            </span>
                                        </th>
                                        <th class="text-center" data-datatable-column="end_date">
                                            <span class="sort">
                                                <span class="sort-label">
                                                    Sampai Tanggal
                                                </span>
                                            </span>
                                        </th>
                                        <th class="text-center" data-datatable-column="request_day">
                                            <span class="sort">
                                                <span class="sort-label">
                                                    Jumlah Hari
                                                </span>
                                            </span>
                                        </th>
                                        <th class="text-center" data-datatable-column="status">
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
    <!-- End of Container -->
    @include('partials.modal-confirm-delete', [
        'mainTitle' => 'Batalkan Cuti?',
        'mainContent' => 'Apakah anda yakin untuk membatalkan cuti ini?',
    ])
@endsection

@push('javascript')
    <!-- Begin - Define Route -->
    <script type="text/javascript">
        const showRoute = "{{ route('my-activity.my-leave.show', ':id') }}"; // Pass route pattern to JS
        const editRoute = "{{ route('my-activity.my-leave.cancel-leave-update', ':id') }}"; // Pass route pattern to JS
    </script>
    <!-- End - Define Route -->

    <script type="text/javascript">
        function confirmLeaveCancel(event) {
            if (!confirm("Are you sure you want to cancel this leave?")) {
                event.preventDefault(); // Prevent form submission if user cancels
                return false;
            }
            return true; // Allow form submission if user confirms
        }
    </script>

    <script type="text/javascript">
        const apiUrl = '{{ route('api.v1.my-activity.my-leave.datatable') }}';
        const deleteUrl = '#';
        const element = document.querySelector('#kt_remote_table');

        const dataTableOptions = {
            apiEndpoint: apiUrl,
            pageSize: 10,
            searchQuery: '',
            infoEmpty: 'Data Kosong',
            stateSave: false,
            columns: {
                type: {
                    title: 'Tipe',
                    render: (data, type, row) => {
                        return `Cuti ${type.type.charAt(0).toUpperCase() + type.type.slice(1)}`;
                    },
                    createdCell(cell) {
                        cell.classList.add('text-center');
                    },
                },
                start_date: {
                    title: 'Dari Tanggal',
                    render: (data, type, row) => {
                        return `${type.formatted_start_date}`;
                    },
                    createdCell(cell) {
                        cell.classList.add('text-center');
                    },
                },
                end_date: {
                    title: 'Sampai Tanggal',
                    render: (data, type, row) => {
                        return `${type.formatted_end_date}`;
                    },
                    createdCell(cell) {
                        cell.classList.add('text-center');
                    },
                },
                request_day: {
                    title: 'Jumlah Hari',
                    render: (data, type, row) => {
                        return `${type.approve_day !== null ? type.approve_day : type.request_day} Hari`;
                        //return `${ type.request_day } hari ${()type.approve_day !== null && type.approve_day !==  type.request_day) ? ' / disetujui ' type.approve_day 'hari' : ''}`;
                    },
                    createdCell(cell) {
                        cell.classList.add('text-center');
                    },
                },
                status: {
                    title: 'Status',
                    render: (data, type, row) => {
                        return `
                        <span class="badge badge-pill badge-outline badge-${type.status_color}">
                            ${type.status_label}
                        </span>`;
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

                        const buttonShow = `
                            <a href="${showUrl}" class="btn btn-icon btn-sm btn-clear btn-primary" data-tooltip="#show">
                                <i class="ki-filled ki-eye"></i>
                            </a>
                            <div class="tooltip transition-opacity duration-300" id="show">
                                Lihat {{ $data['pageTitle'] }}
                            </div>
                        `

                        const buttonCancel = `<form action="${editUrl}" method="POST" onsubmit="return confirmLeaveCancel(event)" class="inline">
                                @csrf
                                <input type="hidden" name="_method" value="PUT">
                                <button type="submit" class="btn btn-icon btn-sm btn-clear btn-danger" data-tooltip="#confirmLeaveCancel">
                                    <i class="ki-filled ki-cross"></i>
                                </button>
                            </form>
                            <div class="tooltip transition-opacity duration-300" id="confirmLeaveCancel">
                                Batalkan {{ $data['pageTitle'] }}
                            </div>`

                            return `
                            <div class="items-center space-x-2">
                                ${buttonShow} ${type.status ===  1 || type.status ===  2 ? buttonCancel : ''}
                            </div>
                            `
                    },
                    createdCell(cell) {
                        cell.classList.add('text-center');
                    },
                },
            }
        };
        const dataTable = new KTDataTable(element, dataTableOptions);
        const refreshTable = document.getElementById('refresh-btn').addEventListener('click', function() {
            dataTable.reload();
        });
    </script>
@endpush
