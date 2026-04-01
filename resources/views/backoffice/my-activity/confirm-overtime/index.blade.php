@extends('layouts.main')

@section('content')
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
                                        <th class=" text-center" data-datatable-column="employee_id">
                                            <span class="sort">
                                                <span class="sort-label">
                                                    Nama Rekan Kerja
                                                </span>
                                            </span>
                                        </th>
                                        <th class="text-center" data-datatable-column="start_date">
                                            <span class="sort">
                                                <span class="sort-label">
                                                    Dari
                                                </span>
                                            </span>
                                        </th>
                                        <th class="text-center" data-datatable-column="end_date">
                                            <span class="sort">
                                                <span class="sort-label">
                                                    Sampai
                                                </span>
                                            </span>
                                        </th>
                                        <th class="text-center" data-datatable-column="overtime_hour">
                                            <span class="sort">
                                                <span class="sort-label">
                                                    Durasi
                                                </span>
                                            </span>
                                        </th>
                                        <th class="text-center" data-datatable-column="shift_id">
                                            <span class="sort">
                                                <span class="sort-label">
                                                    Shift
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
        'mainTitle' => 'Batalkan lembur?',
        'mainContent' => 'Apakah anda yakin untuk membatalkan lembur ini?',
    ])
@endsection

@push('javascript')
    <!-- Begin - Define Route -->
    <script type="text/javascript">
        const showRoute = "{{ route('my-activity.my-overtime-confirm.confirm-overtime-show', ':id') }}"; // Pass route pattern to JS
        const editRoute = "{{ route('my-activity.my-overtime-confirm.confirm-overtime-update', ':id') }}"; // Pass route pattern to JS
        const rejectRoute = "{{ route('my-activity.my-overtime-confirm.reject-overtime-update', ':id') }}"; // Pass route pattern to JS

    </script>
    <!-- End - Define Route -->
    <script type="text/javascript">
        function confirmOvertimeApproval(event) {
            if (!confirm("Are you sure you want to approve this overtime?")) {
                event.preventDefault(); // Prevent form submission if user cancels
                return false;
            }
            return true; // Allow form submission if user confirms
        }

        function rejectOvertime(event) {
            if (!confirm("Are you sure you want to reject this overtime?")) {
                event.preventDefault(); // Prevent form submission if user cancels
                return false;
            }
            return true; // Allow form submission if user confirms
        }
    </script>

    <script type="text/javascript">
        const apiUrl = '{{ route('api.v1.my-activity.my-overtime-confirm.datatable-confirm-overtime') }}';
        const deleteUrl = '#';
        const element = document.querySelector('#kt_remote_table');

        const dataTableOptions = {
            apiEndpoint: apiUrl,
            pageSize: 10,
            searchQuery: '',
            infoEmpty: 'Data Kosong',
            stateSave: false,
            columns: {
                employee_id: {
                    title: 'Nama Rekan Kerja',
                    render: (data, type, row) => {
                        return `${type.employee.name}`;
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
                overtime_hour: {
                    title: 'Durasi',
                    render: (item, row, context) => {
                        return `${item} Jam`;
                    },
                    createdCell(cell) {
                        cell.classList.add('text-center');
                    },
                },
                shift_id: {
                    title: 'Shift',
                    render: (item, row, context) => {
                        console.log(row);

                        return `${row.shift.name}`;
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
                        const rejectUrl = rejectRoute.replace(':id', type.id);


                        const buttonShow = `
                            <a href="${showUrl}" class="btn btn-icon btn-sm btn-clear btn-primary" data-tooltip="#show${type.id}">
                                <i class="ki-filled ki-eye"></i>
                            </a>
                            <div class="tooltip transition-opacity duration-300" id="show${type.id}">
                                Lihat {{ $data['pageTitle'] }}
                            </div>
                        `;

                        const buttonApprove = `
                            <form action="${editUrl}" method="POST" onsubmit="return confirmOvertimeApproval(event)" class="inline">
                                @csrf
                                <input type="hidden" name="_method" value="PUT">
                                <button type="submit" class="btn btn-icon btn-sm btn-clear btn-success" data-tooltip="#confirmOvertime${type.id}">
                                    <i class="ki-filled ki-check"></i>
                                </button>
                            </form>
                            <div class="tooltip transition-opacity duration-300" id="confirmOvertime${type.id}">
                                Setujui {{ $data['pageTitle'] }}
                            </div>
                        `

                        const buttonReject = `
                            <form action="${rejectUrl}" method="POST" onsubmit="return rejectOvertime(event)" class="inline">
                                @csrf
                                <input type="hidden" name="_method" value="PUT">
                                <button type="submit" class="btn btn-icon btn-sm btn-clear btn-danger" data-tooltip="#rejectOvertime${type.id}">
                                    <i class="ki-filled ki-cross"></i>
                                </button>
                            </form>
                            <div class="tooltip transition-opacity duration-300" id="rejectOvertime${type.id}">
                                Tolak {{ $data['pageTitle'] }}
                            </div>
                        `

                        return `
                        <div class="items-center space-x-2">
                            ${buttonShow} ${type.status ===  1 ? buttonApprove : ''} ${type.status ===  1 ? buttonReject : ''}
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
        const refreshTable = document.getElementById('refresh-btn').addEventListener('click', function() {
            dataTable.reload();
        });
    </script>
@endpush
