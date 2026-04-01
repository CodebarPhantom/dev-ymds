@extends('layouts.main')

@section('content')
    @php
        use Carbon\Carbon;

        // Get current month and year
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Get total days in the month
        $daysInMonth = Carbon::now()->daysInMonth;
    @endphp
    <!-- Modal -->
    <div class="modal" data-modal="true" id="modal_1">

        <div class="modal-content max-w-[600px] top-[20%]">
            <div class="modal-header">
                <h3 class="modal-title text-lg font-bold">Generate Payroll</h3>

                <button class="btn btn-xs btn-icon btn-light" data-modal-dismiss="true">
                    <i class="ki-outline ki-cross">
                    </i>
                </button>
            </div>
            <form id="generateForm" method="POST" action="{{ route('workforce.employee-payroll.generate') }}">
                @csrf
                <div class="modal-body mt-4">

                    <div class="flex flex-col gap-4">
                        <!-- Month Selector -->
                        <div>
                            <label for="month" class="block text-sm font-medium text-gray-700">
                                Select Month
                            </label>
                            <select id="month-select" class="select w-32 flex-1" name="month">
                                @foreach (range(1, 12) as $month)
                                    <option value="{{ $month }}" {{ $month == $currentMonth ? 'selected' : '' }}>
                                        {{ DateTime::createFromFormat('!m', $month)->format('F') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Year Selector -->
                        <div>
                            <label for="year" class="block text-sm font-medium text-gray-700">
                                Select Year
                            </label>
                            <select id="year-select" class="select flex-1" name="year">
                                @php
                                    $currentYear = date('Y');
                                    $startYear = 2024;
                                    $endYear = $currentYear + 2;
                                @endphp
                                @for ($year = $startYear; $year <= $endYear; $year++)
                                    <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>

                </div>
                <div class="modal-footer flex justify-end mt-4">

                    <button class="btn btn-primary" type="submit">Generate</button>
                </div>
            </form>
        </div>
        </form>
    </div>
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
                    <input class="input input-sm pl-8 text-center" id="search" placeholder="Cari Data" type="text">
                </div>

                <select id="month-search" class="select w-32 flex-1" name="month">
                    @foreach (range(1, 12) as $month)
                        <option value="{{ $month }}" {{ $month == $currentMonth ? 'selected' : '' }}>
                            {{ DateTime::createFromFormat('!m', $month)->format('F') }}
                        </option>
                    @endforeach
                </select>

                <!-- Year Dropdown -->
                <select id="year-search" class="select w-32 flex-1" name="year">
                    @php
                        $currentYear = date('Y');
                        $startYear = 2024;
                        $endYear = $currentYear + 2;
                    @endphp
                    @for ($year = $startYear; $year <= $endYear; $year++)
                        <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endfor
                </select>
                <!-- Modal Trigger Button -->
                <button class="btn btn-sm text-center btn-info" data-modal-toggle="#modal_1">
                    <i class="ki-filled ki-financial-schedule"></i>Generate {{ $data['pageTitle'] }}
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
                                                    Nama
                                                </span>
                                                <span class="sort-icon">
                                                </span>
                                            </span>
                                        </th>
                                        <th class="text-center" data-datatable-column="salary_base">
                                            <span class="sort">
                                                <span class="sort-label">
                                                    Gaji Pokok
                                                </span>
                                            </span>
                                        </th>
                                        <th class="text-center" data-datatable-column="salary_comben">
                                            <span class="sort">
                                                <span class="sort-label">
                                                    Comp. Ben.
                                                </span>
                                            </span>
                                        </th>
                                        <th class="text-center" data-datatable-column="salary_remuneration">
                                            <span class="sort">
                                                <span class="sort-label">
                                                    Tunjangan
                                                </span>
                                            </span>
                                        </th>
                                        <th class="text-center" data-datatable-column="salary_gross">
                                            <span class="sort">
                                                <span class="sort-label">
                                                    Gaji kotor
                                                </span>
                                            </span>
                                        </th>
                                        <th class="text-center" data-datatable-column="salary_deduction">
                                            <span class="sort">
                                                <span class="sort-label">
                                                    Deduction
                                                </span>
                                            </span>
                                        </th>
                                        <th class="text-center" data-datatable-column="salary_tax">
                                            <span class="sort">
                                                <span class="sort-label">
                                                    Pajak
                                                </span>
                                            </span>
                                        </th>
                                        <th class="text-center" data-datatable-column="salary_thp">
                                            <span class="sort">
                                                <span class="sort-label">
                                                    THP
                                                </span>
                                            </span>
                                        </th>
                                        <th class="text-center" data-datatable-column="formatted_payday_date_period">
                                            <span class="sort">
                                                <span class="sort-label">
                                                    Periode Gaji
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
        const showRoute = "{{ route('workforce.employee-payroll.show', ':id') }}"; // Pass route pattern to JS
    </script>
    <!-- End - Define Route -->


    <script type="text/javascript">
        const apiUrl = '{{ route('api.v1.workforce.employee-payroll.datatable') }}';
        const deleteUrl = "#";
        const pageTitle = '{{ $data['pageTitle'] }}';
        const element = document.querySelector('#kt_remote_table');

        const dataTableOptions = {
            apiEndpoint: apiUrl,
            pageSize: 10,
            search: "",
            infoEmpty: "Data Kosong",
            stateSave: false,
            columns: {
                name: {
                    title: 'Nama',
                    render: (item, row, context) => {
                        const email = row.user ? row.user.email : 'Email Not Found';
                        const profilePicture = row.user && row.user.url_image ?
                            `${window.location.origin}/${row.user.url_image}` :
                            '{{ asset('assets/media/avatars/blank.png') }}';

                        let badgeClass = '';

                        switch (row.employee.employee_status.toLowerCase()) {
                            case 'tetap':
                                badgeClass = 'badge-success';
                                break;
                            case 'kontrak':
                                badgeClass = 'badge-warning';
                                break;
                            case 'harian':
                                badgeClass = 'badge-danger';
                                break;
                            default:
                                badgeClass = 'badge-secondary'; // Fallback class for unexpected statuses
                        }

                        return `
                        <div class="flex items-center gap-2.5">

                            <div class="flex flex-col">
                                <a class="text-sm font-medium text-gray-900 hover:text-primary-active mb-px" href="#">
                                    ${row.employee.name}
                                </a>
                                <a class="text-2sm text-gray-700 font-normal hover:text-primary-active" href="#">
                                    <span class="badge badge-sm badge-light badge-outline ${badgeClass}">
                                        ${row.employee.employee_status}
                                    </span>
                                </a>
                            </div>
                        </div>
                        `;
                    },
                },
                salary_base: {
                    title: 'Gaji pokok',
                    render: (item, row, context) => {

                        return `${row.formatted_salary_base}`;
                    },
                    createdCell(cell) {
                        cell.classList.add('text-center', 'text-success');
                    },
                },
                salary_comben: {
                    title: 'Comp. Ben.',
                    render: (item, row, context) => {

                        return `${row.formatted_salary_comben}`;
                    },
                    createdCell(cell) {
                        cell.classList.add('text-center', 'text-success');
                    },
                },
                salary_remuneration: {
                    title: 'Tunjangan',
                    render: (item, row, context) => {

                        return `${row.formatted_salary_remuneration}`;
                    },
                    createdCell(cell) {
                        cell.classList.add('text-center', 'text-success');
                    },
                },
                salary_gross: {
                    title: 'Gaji Kotor',
                    render: (item, row, context) => {

                        return `${row.formatted_salary_gross}`;
                    },
                    createdCell(cell) {
                        cell.classList.add('text-center');
                    },
                },
                salary_deduction: {
                    title: 'Deduction',
                    render: (item, row, context) => {

                        return `${row.formatted_salary_deduction}`;
                    },
                    createdCell(cell) {
                        cell.classList.add('text-center', 'text-danger');
                    },
                },
                salary_tax: {
                    title: 'Pajak',
                    render: (item, row, context) => {

                        return `${row.formatted_salary_tax}`;
                    },
                    createdCell(cell) {
                        cell.classList.add('text-center', 'text-danger');
                    },
                },
                salary_thp: {
                    title: 'THP',
                    render: (item, row, context) => {

                        return `${row.formatted_salary_thp}`;
                    },
                    createdCell(cell) {
                        cell.classList.add('text-center', 'text-primary');
                    },
                },
                formatted_payday_date_period: {
                    title: 'Periode Gaji',
                    render: (item, row, context) => {

                        return `${item}`;
                    },
                    createdCell(cell) {
                        cell.classList.add('text-center');
                    },
                },


                action: {
                    title: 'Action',
                    render: (item, row, context) => {
                        const showUrl = showRoute.replace(':id', row.id);
                        return `
                            <a href="${showUrl}" class="btn btn-icon btn-sm btn-clear btn-success" target="_blank" data-tooltip="#show-${row.id}">
                                <i class="ki-filled ki-document"></i>
                            </a>
                            <div class="tooltip transition-opacity duration-300" id="show-${row.id}">
                                Slip Gaji
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
        const refreshTable = () => {
            const month = document.querySelector('#month-search').value;
            const year = document.querySelector('#year-search').value;
            const searchTerm = document.querySelector('#search').value; // Capture search input

            const params = new URLSearchParams({
                month,
                year,
                name: searchTerm, // Include search term as "name"
            });

            dataTable.search(params.toString());
        };
        document.querySelector('#search').addEventListener('change', refreshTable);

        document.querySelector('#month-search').addEventListener('change', refreshTable);
        document.querySelector('#year-search').addEventListener('change', refreshTable);
    </script>
@endpush
