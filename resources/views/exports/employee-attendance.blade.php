<table>
    <thead>
        <tr>
            <th> Periode: {{ $period }}</th>
        </tr>
        <tr>
            <th rowspan="2">Nama Karyawan</th>
            <th rowspan="2">Shift</th>
            <th rowspan="2">Total Hadir</th>
            <th rowspan="2">Total Libur</th>
            <th rowspan="2">Total Tidak Hadir</th>
            <th rowspan="2">Total Cuti</th>
            <th rowspan="2">Total Telat</th>
            <th rowspan="2">Akumulasi Waktu Telat</th>
            <th rowspan="2">Total Pulang Cepat</th>
            <th rowspan="2">Akumulasi Waktu Pulang Cepat</th>
            <th rowspan="2">% Kehadiran</th>

            @for ($day = 1; $day <= $daysInMonth; $day++)
                <th colspan="4" class="text-center">{{ $day }}</th>
            @endfor
        </tr>
        <tr>
            @for ($day = 1; $day <= $daysInMonth; $day++)
                <th class="text-center">Jam Masuk</th>
                <th class="text-center">Telat</th>
                <th class="text-center">Jam Keluar</th>
                <th class="text-center">Pulang Cepat</th>
            @endfor
        </tr>
    </thead>
    <tbody>
        @foreach ($reports as $report)
            <tr>
                <td>{{ $report['employee_name'] }}</td>
                <td>{{ $report['shift'] }}</td>
                <td>{{ $report['attend_total'] }}</td>
                <td>{{ $report['holiday_total'] }}</td>
                <td>{{ $report['absent_total'] }}</td>
                <td>{{ $report['leave_total'] }}</td>
                <td>{{ $report['late_total'] }}</td>
                <td>{{ $report['accumulation_late_time_total'] }}</td>
                <td>{{ $report['early_clock_out_total'] }}</td>
                <td>{{ $report['accumulation_early_clock_out_total'] }}</td>
                <td>{{ $report['attendance_percentage'] }}</td>

                @foreach ($report['attendance'] as $day)
                    <td>{{ $day['clock_in'] }}</td>
                    <td>{{ $day['is_late'] ? 'Yes' : 'No' }}</td>
                    <td>{{ $day['clock_out'] }}</td>
                    <td>{{ $day['is_early_clock_out'] ? 'Yes' : 'No' }}</td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
