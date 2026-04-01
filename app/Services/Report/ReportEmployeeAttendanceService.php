<?php

namespace App\Services\Report;

use App\Enums\ShiftAttendanceStatus;
use App\Models\Workforce\Employee;
use App\Services\MasterService;
use App\Models\WorkSchedule\ShiftAttendance;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\Log;

class ReportEmployeeAttendanceService extends MasterService
{
    public function getEmployeeAttendance($month, $year)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $employees = Employee::active()->orderBy('name', 'asc')->get();


        $attendanceData = ShiftAttendance::with(['shift'])
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->groupBy('employee_id');


        $report = $employees->map(function ($employee) use ($attendanceData, $startDate) {
            $daysInMonth = $startDate->daysInMonth;
            $attendance = $attendanceData->get($employee->id);

            $dailyData = [];
            $attendanceTotal = null;
            $holidayTotal = null;
            $absentTotal = null;
            $leaveTotal = null;
            $lateTotal = null;
            $earlyClockOutTotal = null;
            $lateTimeTotal = 0;
            $earlyTimeTotal = 0;


            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = $startDate->copy()->day($day)->format('Y-m-d');
                $record = $attendance?->firstWhere('date', $date);

                // If the record exists and status is 1, increment the attendance total
                if ($record && ($record->status->value == ShiftAttendanceStatus::MASUK->value)) {
                    $attendanceTotal++;
                }

                if ($record && ($record->status->value == ShiftAttendanceStatus::LIBUR_KERJA->value || $record->status->value == ShiftAttendanceStatus::LIBUR_NASIONAL->value)) {
                    $holidayTotal++;
                }


                if ($record && ($record->status->value == ShiftAttendanceStatus::TIDAK_MASUK->value)) {
                    $absentTotal++;
                }

                if ($record && ($record->status->value == ShiftAttendanceStatus::CUTI->value)) {
                    $leaveTotal++;
                }

                if ($record && $record->is_late) {
                    $lateTotal++;
                    $lateTimeTotal += $record->late_clock_in_time ?? 0;
                }

                if ($record && $record->is_early_clock_out) {
                    $earlyClockOutTotal++;
                    $earlyTimeTotal += $record->early_clock_out_time ?? 0;

                }

                $dailyData[] = [
                    'date' => $date,
                    'clock_in' => $record->clock_in ?? '-',
                    'is_late' => $record->is_late ?? false,
                    'clock_out' => $record->clock_out ?? '-',
                    'is_early_clock_out' => $record->is_early_clock_out ?? false,
                ];
            }



            $accumulatedLateTime = CarbonInterval::seconds($lateTimeTotal)->cascade()->format('%H Jam %I Menit');
            $accumulatedEarlyTime = CarbonInterval::seconds($earlyTimeTotal)->cascade()->format('%H Jam %I Menit');



            $shift = $attendance?->first()?->shift;
            return [
                'employee_name' => $employee->name,
                'attend_total' => $attendanceTotal,  // Set the calculated attendance total
                'holiday_total' => $holidayTotal,
                'absent_total' => $absentTotal,
                'leave_total' => $leaveTotal,
                'late_total' => $lateTotal,
                'accumulation_late_time_total' => $accumulatedLateTime,
                'early_clock_out_total' => $earlyClockOutTotal,
                'accumulation_early_clock_out_total' => $accumulatedEarlyTime,
                'attendance_percentage' => $attendanceTotal !== null ? round(($attendanceTotal / ($absentTotal + $leaveTotal + $attendanceTotal)) * 100, 2) . '%' : '',
                'shift' => $shift ? $shift->name . ' (' . $shift->formatted_start_time . ' - ' . $shift->formatted_end_time . ')' : 'No Shift',
                'attendance' => $dailyData
            ];
        });




        return $report;
    }
}
