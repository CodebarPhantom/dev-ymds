@extends('layouts.main')

@push('head')
    <style>
        /* Freeze first column */
        td.sticky,
        th.sticky {
            position: sticky;
            background: white;
            z-index: 10;
            /* Ensure the sticky columns appear above other elements */
        }

        /* First column (Nama Karyawan) */
        td.sticky.left-0,
        th.sticky.left-0 {
            left: 0;
        }

        /* Second column (Shift) */
        /* td.sticky.left-[200px],
        th.sticky.left-[200px] {
            left: 200px;
        } */
    </style>
@endpush

@section('content')
    @php
        use Carbon\Carbon;

        // Get current month and year
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Get total days in the month
        $daysInMonth = Carbon::now()->daysInMonth;
    @endphp
    <div id="loading" style="display: none;">
        <div class="backdrop"></div>
        <div class="loading-content">
            <div class="spinner"></div>
            <p>Sending data, please wait...</p>
        </div>
    </div>
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
            <div class="flex flex-wrap gap-2.5 w-full md:w-1/2">

                <!-- Month Dropdown -->
                <select id="month-select" class="select w-full md:flex-1" name="month">
                    @foreach (range(1, 12) as $month)
                        <option value="{{ $month }}" {{ $month == $currentMonth ? 'selected' : '' }}>
                            {{ DateTime::createFromFormat('!m', $month)->format('F') }}
                        </option>
                    @endforeach
                </select>

                <!-- Year Dropdown -->
                <select id="year-select" class="select w-full md:flex-1" name="year">
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

                <!-- Button -->
                <button id="generate-data" class="btn btn-sm text-center btn-info w-full md:flex-1">
                    <i class="ki-filled ki-some-files mr-2"></i> Tampilkan Laporan
                </button>

                <!-- Initially hidden -->
                <button id="export-excel" class="btn btn-sm text-center btn-success w-full md:flex-1">
                    <i class="ki-filled ki-tablet-ok mr-2"></i> Export Excel
                </button>


            </div>
        </div>

        @include('partials.attention')

        <!-- Placeholder Container (Initially Visible) -->
        <div id="placeholder-container" class="container-fixed flex flex-col items-center justify-center h-1/2 mt-2">
            <img src="{{ asset('assets/media/illustrations/22.svg') }}" alt="Tidak ada data" class="w-52 h-52">
            <h3 class="text-lg font-semibold text-gray-900 mt-4">
                Pilih tanggal dan tahun
            </h3>
        </div>

        <!-- Report Container (Initially Hidden) -->
        <div id="report-container" class="container-fixed hidden">
            <div class="grid pb-7.5">
                <div class="card min-w-full">
                    {{-- <div class="card-header">
                        <h3 id="report-title" class="card-title text-center font-bold">
                            <!-- Month and Year will be dynamically inserted -->
                        </h3>
                    </div> --}}
                    <div class="card-table scrollable-x-auto">
                        <table class="table table-border align-middle text-gray-700 font-medium text-sm">
                            <thead>
                                <tr id="days-header">
                                    <th rowspan="2" class="min-w-[200px]">Nama Karyawan</th>
                                    <th rowspan="2" class="min-w-44">Total Hadir</th>
                                    <th rowspan="2" class="min-w-44">Total Libur</th>
                                    <th rowspan="2" class="min-w-44">Total Tidak Hadir</th>
                                    <th rowspan="2" class="min-w-44">Total Cuti</th>
                                    <th rowspan="2" class="min-w-44">Total Telat</th>
                                    <th rowspan="2" class="min-w-44">Akumulasi Waktu Telat</th>
                                    <th rowspan="2" class="min-w-44">Total Pulang Cepat</th>
                                    <th rowspan="2" class="min-w-44">Akumulasi Waktu Pulang Cepat</th>
                                    <th rowspan="2" class="min-w-44">% Kehadiran</th>
                                </tr>
                                <tr id="sub-days-header"></tr>
                            </thead>
                            <tbody id="report-body"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endsection

    @push('javascript')
        <script>
            document.getElementById('generate-data').addEventListener('click', function(e) {
                e.preventDefault();

                // Get selected values for month and year
                const month = document.querySelector('select[name="month"]').value;
                const year = document.querySelector('select[name="year"]').value;
                const loadingIndicator = document.getElementById('loading');
                loadingIndicator.style.display = 'flex';


                // Axios GET request to fetch attendance report
                axios
                    .get('{{ route('api.v1.report.employee.report-attendance') }}', {
                        params: {
                            month,
                            year
                        },
                    })
                    .then((response) => {
                        console.log('Report data:', response.data.data.reports);

                        // Process the report data and dynamically update the table
                        updateTable(response.data.data.reports, year, month);
                        loadingIndicator.style.display = 'none';

                        // Hide the placeholder
                        document.getElementById('placeholder-container').style.display = 'none';

                        // Show the report container
                        document.getElementById('report-container').style.display = 'block';
                    })
                    .catch((error) => {
                        loadingIndicator.style.display = 'none';
                        alert('Failed to get data.');
                    });
            });


            // Function to dynamically update the table with fetched report data
            function updateTable(data, year, month) {
                const daysHeader = document.getElementById('days-header');
                const subDaysHeader = document.getElementById('sub-days-header');
                const tbody = document.getElementById('report-body');

                // Clear existing table content
                daysHeader.innerHTML = `
                    <th rowspan="2" class="sticky left-0 bg-white z-10 min-w-[200px]">Nama Karyawan</th>
                    <th rowspan="2" class="min-w-44">Total Hadir</th>
                    <th rowspan="2" class="min-w-44">Total Libur</th>
                    <th rowspan="2" class="min-w-44">Total Tidak Hadir</th>
                    <th rowspan="2" class="min-w-44">Total Cuti</th>
                    <th rowspan="2" class="min-w-44">Total Telat</th>
                    <th rowspan="2" class="min-w-44">Akumulasi Waktu Telat</th>
                    <th rowspan="2" class="min-w-44">Total Pulang Cepat</th>
                    <th rowspan="2" class="min-w-44">Akumulasi Waktu Pulang Cepat</th>
                    <th rowspan="2" class="min-w-44">% Kehadiran</th>
                `;
                subDaysHeader.innerHTML = '';
                tbody.innerHTML = '';

                // Get number of days in the selected month
                const daysInMonth = new Date(year, month, 0).getDate();

                // Generate main headers for each day with colspan="4"
                // for (let day = 1; day <= daysInMonth; day++) {
                //     daysHeader.innerHTML += `<th colspan="4" class="text-center">${day}</th>`;
                // }

                // Generate sub-headers for each day's sub-columns
                for (let day = 1; day <= daysInMonth; day++) {
                    daysHeader.innerHTML += `<th colspan="4" class="text-center">${day}</th>`;

                    subDaysHeader.innerHTML += `
                        <th class="text-center">Jam Masuk</th>
                        <th class="text-center">Telat</th>
                        <th class="text-center">Jam Keluar</th>
                        <th class="text-center">Pulang Cepat</th>
                    `;
                }

                // Populate table body with employee attendance data
                data.forEach(employee => {
                    const row = document.createElement('tr');

                    // Append employee name and shift
                    row.innerHTML += `<td class="sticky left-0 bg-white">${employee.employee_name}</td>`;

                    row.innerHTML += `<td>${employee.attend_total ?? '-'}</td>`;
                    row.innerHTML += `<td>${employee.holiday_total ?? '-'}</td>`;
                    row.innerHTML += `<td>${employee.absent_total ?? '-'}</td>`;
                    row.innerHTML += `<td>${employee.leave_total ?? '-'}</td>`;
                    row.innerHTML += `<td>${employee.late_total ?? '-'}</td>`;
                    row.innerHTML += `<td>${employee.accumulation_late_time_total ?? '-'}</td>`;
                    row.innerHTML += `<td>${employee.early_clock_out_total ?? '-'}</td>`;
                    row.innerHTML += `<td>${employee.accumulation_early_clock_out_total ?? '-'}</td>`;
                    row.innerHTML += `<td>${employee.attendance_percentage ?? '-'}</td>`;

                    // Append attendance for each day
                    employee.attendance.forEach(day => {
                        row.innerHTML += `<td>${day.clock_in ?? '-'}</td>`;
                        row.innerHTML += `<td>${day.is_late ? 'Yes' : 'No'}</td>`;
                        row.innerHTML += `<td>${day.clock_out ?? '-'}</td>`;
                        row.innerHTML += `<td>${day.is_early_clock_out ? 'Yes' : 'No'}</td>`;
                    });

                    tbody.appendChild(row);
                });
            }

            document.getElementById('export-excel').addEventListener('click', () => {
                // Get selected month and year
                const month = document.getElementById('month-select').value;
                const year = document.getElementById('year-select').value;

                // Call the exportAttendance function
                exportAttendance(month, year);
            });

            const exportAttendance = async (month, year) => {
                const loadingIndicator = document.getElementById('loading');


                try {
                    // Show loading indicator
                    loadingIndicator.style.display = 'flex';

                    // Axios GET request to export attendance
                    const response = await axios.get('{{ route('api.v1.report.employee.export-attendance') }}', {
                        params: {
                            month,
                            year
                        },
                        responseType: 'blob', // Important for file downloads
                    });

                    // Hide loading indicator
                    loadingIndicator.style.display = 'none';

                    // Trigger file download
                    const url = window.URL.createObjectURL(new Blob([response.data]));
                    const link = document.createElement('a');
                    link.href = url;
                    console.log(url);
                    // Generate file name dynamically
                    const fileName = `employee_attendance_report_${year}_${month}.xlsx`;
                    link.setAttribute('download', fileName);

                    document.body.appendChild(link);
                    link.click();
                    link.remove();

                    // Hide placeholder

                } catch (error) {
                    // Hide loading indicator
                    loadingIndicator.style.display = 'none';

                    // Hide placeholder


                    // Show error message
                    alert('Failed to export attendance report. Please try again.');
                    console.error(error);
                }
            };
        </script>
    @endpush
