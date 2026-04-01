<?php

namespace App\Services\WorkSchedule;

use App\Models\WorkSchedule\ShiftAttendance;
// use App\Models\WorkSchedule\ShiftFixed;
// use App\Services\MasterService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ShiftAttendanceService
{
    public function createShiftAttendance(array $data)
    {
        // Use updateOrCreate to either update an existing record or create a new one
        $shiftAttendance = ShiftAttendance::updateOrCreate(
            [
                'employee_id' => $data['employee_id'],
                'date' => $data['date']
            ], // Conditions to check for an existing record
            $data // Data to update or insert
        );

        // Log the action
        // $this->appLogService->logChange($shiftAttendance, $shiftAttendance->wasRecentlyCreated ? 'created' : 'updated');

        return $shiftAttendance;



        // $checkAttendance = ShiftAttendance::where('employee_id',$data['employee_id'])->where('date',$data['date'])->exists();

        // if(!$checkAttendance){
        //     $shiftAttendance = ShiftAttendance::create($data);
        //     $this->appLogService->logChange($shiftAttendance, 'created');
        // }

        // return;

        // Check if a record exists for the employee on the specified date
        //     $shiftAttendance = ShiftAttendance::where('employee_id', $data['employee_id'])
        //         ->where('date', $data['date'])
        //         ->first();

        //     if ($shiftAttendance) {
        //         // If a record exists, update it with the new data
        //         $shiftAttendance->update($data);
        //         $this->appLogService->logChange($shiftAttendance, 'updated');
        //     } else {
        //         // If no record exists, create a new one
        //         $shiftAttendance = ShiftAttendance::create($data);
        //         $this->appLogService->logChange($shiftAttendance, 'created');
        //     }

        //     return $shiftAttendance;
    }

    public function updateShiftAttendanceByDate(array $data)
    {
        $shiftAttendance = ShiftAttendance::where('date', $data['date'])
            ->update($data);
        return $shiftAttendance;
    }

    public function updateShiftAttendanceByDateAndEmployee(array $data, $employees)
    {
        $shiftAttendance = ShiftAttendance::where('date', $data['date'])
            ->whereIn('employee_id', $employees) // Filter by role_id
            ->update($data);

        return $shiftAttendance;
    }

    public function getShiftAttendanceByDateEmployeeId($date, $employeeId)
    {
        //$shiftAttendance = ShiftAttendance::with(['shift'])->where('date',$date)->where('employee_id',$employeeId)->first();

        $now = Carbon::now();


        $shiftYesterday = ShiftAttendance::with(['shift'])->where('date', Carbon::parse($date)->subDay())->where('employee_id', $employeeId)->first();
        //Log::debug($date);
        $shiftAttendance = ShiftAttendance::with(['shift'])->where('date', $date)->where('employee_id', $employeeId)->first();

        // Log::debug([$shiftYesterday['shift']['is_night_shift'], $shiftYesterday['clock_in'] != null]);
        //dd($date, $employeeId);



        if (($shiftYesterday['shift']['is_night_shift'] ?? false)) {

            if ($shiftYesterday['clock_in'] !== null && $shiftYesterday['clock_out'] === null) {
                //dd('test',$now->copy()->format('H:i'), $shiftYesterday['shift']['end_time']);

                if ($now->copy()->format('H:i:s') > $shiftYesterday['shift']['start_time']) {
                    return $shiftAttendance;
                } else {
                    return $shiftYesterday;
                }
                //return $shiftYesterday;//apa ini?
            } else {
                return $shiftAttendance;
            }
        } else {
            //dd($shiftYesterday['clock_in'],$shiftYesterday['clock_out'] ?? null,$now->copy()->format('H:i:s'),($shiftYesterday['shift']['start_time'] ?? '00:00'));
            if (
                $shiftYesterday &&
                ($shiftYesterday['clock_in'] ?? null) !== null &&
                ($shiftYesterday['clock_out'] ?? null) === null
                //&& $now->copy()->format('H:i:s') > ($shiftYesterday['shift']['start_time'] ?? '00:00:00')
            ) {
                return $shiftYesterday;
            } else {
                return $shiftAttendance;
            }
        }




        // if($shiftAttendance['shift']['is_night_shift'] && $now != $date && $shiftAttendance['clock_in'] != null){
        //     $shiftAttendance['date'] = $now->subDay()->format('Y-m-d');
        // }

        // return $shiftAttendance;
    }

    public function updateAttendanceByDateEmployeeId($date, $employeeId, array $data)
    {
        return ShiftAttendance::where('date', $date)->where('employee_id', $employeeId)->update($data);
    }
}
