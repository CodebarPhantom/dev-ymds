<?php

namespace App\Services\Report;

use App\Enums\ShiftAttendanceStatus;
use App\Models\Workforce\Employee;
use App\Services\MasterService;
use App\Models\WorkSchedule\ShiftAttendance;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\Log;
use App\Models\WorkSchedule\Shift;

class ReportEmployeeShiftService extends MasterService
{
    public function getShiftEmployee($data)
    {
        $startDate = Carbon::parse($data['start_date'])->startOfDay();
        $endDate = Carbon::parse($data['end_date'])->endOfDay();
        $shiftId = $data['shift_id'];




        $attendanceData = ShiftAttendance::with(['shift', 'employee'])
            ->when($shiftId != 0, fn($query) => $query->where('shift_id', $shiftId))
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->groupBy('employee_id');


        $processedData = [];

        foreach ($attendanceData as $employeeId => $records) {
            $employeeName = $records[0]['employee']['name']; // Assuming all records for an employee share the same name
            $events = [];
            $currentEvent = null;

            foreach ($records as $record) {
                $note =  '|' . $record['shift']['formatted_start_time'] . ' - ' .
                    $record['shift']['formatted_end_time'] . '| |' .
                    $record['shift']['name'] . '| |' .
                    $record['status_label'] . '| ' .
                    ($record['clock_in'] === null ? 'Belum Hadir' : ($record['clock_out'] === null ? 'Belum Pulang' : 'Kehadiran Lengkap'));

                $color = $record['status_color']; // E.g., "success", "warning", etc.
                $date = $record['date']; // Current record date

                // if ($currentEvent && $currentEvent['note'] === $note && $currentEvent['color'] === $color) {
                //     // Extend the current event if the status is the same as the last one
                //     $currentEvent['end_date'] = $date;
                // } else {
                // Save the current event if it exists and start a new one
                if ($currentEvent) {
                    $events[] = $currentEvent;
                }

                $currentEvent = [
                    'employee_name' => $employeeName,
                    'shift_name' => $record['shift']['name'],
                    'start_date' => $date,
                    'end_date' => $date,
                    'shift_date' => $record['date'],
                    'note' => $note,
                    'color' => $color,
                    'employee_id' => $employeeId,
                    'clock_in' => $record['clock_in'] ?? '-',
                    'clock_out' => $record['clock_out'] ?? '-',
                    'status' => $record['status_label'] . ' - ' .
                        ($record['clock_in'] === null ? 'Belum Hadir' : ($record['clock_out'] === null ? 'Belum Pulang' : 'Kehadiran Lengkap')),
                ];

                //}
            }

            // Add the last event for the employee
            if ($currentEvent) {
                $events[] = $currentEvent;
            }

            // Add the employee's data to the result
            $processedData[] = [
                'name' => $employeeName,
                'events' => $events,
            ];
        }
        //Log::debug(['dari service: ',$processedData]);
        usort($processedData, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });
        return $processedData;
    }
}
