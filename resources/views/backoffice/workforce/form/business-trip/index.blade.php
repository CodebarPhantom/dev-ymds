@extends('layouts.main')

@section('content')
    <!-- Container -->
    @include('backoffice.workforce.form.partials.submenu')
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
                        placeholder="Cari Data" value="" type="text">
                </div>
                <button id="refresh-btn" class="btn btn-sm text-center btn-info">
                    <i class="ki-filled ki-arrows-circle"></i>
                </button>
                <a class="btn btn-sm text-center btn-success" href="{{ route('workforce.submitted-form.business-trip.create') }}">
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
                                        <th class="text-center" data-datatable-column="name">
                                            <span class="sort">
                                                <span class="sort-label">
                                                    Nama
                                                </span>
                                                <span class="sort-icon">
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
@endsection

@push('javascript')
    <!-- Begin - Define Route -->
    <script type="text/javascript">
        const confirmRoute = "{{ route('workforce.submitted-form.business-trip.confirm-business-trip-update', ':id') }}";
        const rejectRoute = "{{ route('workforce.submitted-form.business-trip.reject-business-trip-update', ':id') }}";
        // const editRoute = "{{ route('workforce.submitted-form.business-trip.edit', ':id') }}";
        const showRoute = "{{ route('workforce.submitted-form.business-trip.show', ':id') }}";

    </script>
    <!-- End - Define Route -->

    <script type="text/javascript">
        function confirmBusinessTripApproval(event) {
            if (!confirm("Are you sure you want to approve this business trip?")) {
                event.preventDefault(); // Prevent form submission if user cancels
                return false;
            }
            return true; // Allow form submission if user confirms
        }

        function rejectBusinessTrip(event) {
            if (!confirm("Are you sure you want to reject this business trip?")) {
                event.preventDefault(); // Prevent form submission if user cancels
                return false;
            }
            return true; // Allow form submission if user confirms
        }
    </script>

    <script type="text/javascript">
        const apiUrl = '{{ route('api.v1.workforce.submitted-form.business-trip.dataTable') }}';
        const deleteUrl = "#";
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

                        const confirmUrl = confirmRoute.replace(':id', type.id);
                        const rejectUrl = rejectRoute.replace(':id', type.id);
                        // const editUrl = editRoute.replace(':id', type.id);
                        const showUrl = showRoute.replace(':id', type.id);

                        const buttonShow = `
                            <a href="${showUrl}" class="btn btn-icon btn-sm btn-clear btn-primary" data-tooltip="#show${type.id}">
                                <i class="ki-filled ki-eye"></i>
                            </a>
                            <div class="tooltip transition-opacity duration-300" id="show${type.id}">
                                Lihat {{ $data['pageTitle'] }}
                            </div>
                        `;

                        // const buttonEdit = `
                        //     <a href="${editUrl}" class="btn btn-icon btn-sm btn-clear btn-warning" data-tooltip="#edit">
                        //         <i class="ki-filled ki-notepad-edit"></i>
                        //     </a>
                        //     <div class="tooltip transition-opacity duration-300" id="edit">
                        //         Ubah {{ $data['pageTitle'] }}
                        //     </div>
                        // `;

                        const buttonApprove = `
                            <form action="${confirmUrl}" method="POST" onsubmit="return confirmBusinessTripApproval(event)" class="inline">
                                @csrf
                                <input type="hidden" name="_method" value="PUT">
                                <button type="submit" class="btn btn-icon btn-sm btn-clear btn-success" data-tooltip="#confirmLeave${type.id}">
                                    <i class="ki-filled ki-check"></i>
                                </button>
                            </form>
                            <div class="tooltip transition-opacity duration-300" id="confirmLeave${type.id}">
                                Setujui {{ $data['pageTitle'] }}
                            </div>
                        `;

                        const buttonReject = `
                            <form action="${rejectUrl}" method="POST" onsubmit="return rejectBusinessTrip(event)" class="inline">
                                @csrf
                                <input type="hidden" name="_method" value="PUT">
                                <button type="submit" class="btn btn-icon btn-sm btn-clear btn-danger" data-tooltip="#rejectBusinessTrip${type.id}">
                                    <i class="ki-filled ki-cross"></i>
                                </button>
                            </form>
                            <div class="tooltip transition-opacity duration-300" id="rejectBusinessTrip${type.id}">
                                Tolak {{ $data['pageTitle'] }}
                            </div>
                        `;

                        return `
                        <div class="items-center space-x-2">
                            ${buttonShow}  ${type.status ===  2 ? buttonApprove + buttonReject : ''}
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
            // Clear the search input field
            searchInput.value = '';
            dataTable.reload();
        });
    </script>
@endpush
