<?php

namespace App\Services\WorkSchedule;

use App\Models\WorkSchedule\ShiftAttendance;
use App\Models\WorkSchedule\ShiftRotating;
use App\Models\WorkSchedule\ShiftRotatingEmployee;
use App\Services\MasterService;
use Illuminate\Support\Arr;



class ShiftRotatingService extends MasterService
{
    public function createShiftRotating(array $data)
    {
        $shiftRotatingData = Arr::except($data, ['shift_employees']);
        $shiftRotating = ShiftRotating::create($shiftRotatingData);

        foreach ($data['shift_employees'] as $employeeId) {
            $workEmployee = [
                'employee_id' => $employeeId,
                'shift_rotating_id' => $shiftRotating->id
            ];
            $rotatingShiftEmployee = ShiftRotatingEmployee::create($workEmployee);
            $this->appLogService->logChange($rotatingShiftEmployee, 'created');
        }

        $this->appLogService->logChange($shiftRotating, 'created');
        return $shiftRotating;
    }

    public function getAllShiftRotating()
    {
        return ShiftRotating::with(['employeeShifts'])->notGenerated()->orderBy('start_date','desc')->get();

    }

    public function getShiftRotatingById(int $id)
    {
        return ShiftRotating::with(['employeeShifts','shift'])->find($id);

    }

    public function updateShiftRotating(int $id, array $data)
    {
        $shiftRotatingData = Arr::except($data, ['shift_employees']);
        $shiftRotating = ShiftRotating::find($id);
        $shiftRotating->update($shiftRotatingData);
        $this->appLogService->logChange($shiftRotating, 'updated');


        ShiftRotatingEmployee::where('shift_rotating_id', $shiftRotating->id)->delete();
        foreach ($data['shift_employees'] as $employeeId) {
            $workEmployee = [
                'employee_id' => $employeeId,
                'shift_rotating_id' => $shiftRotating->id
            ];

            $rotatingShiftEmployee = ShiftRotatingEmployee::create($workEmployee);
            $this->appLogService->logChange($rotatingShiftEmployee, 'updated');

        }

        return $shiftRotating;
    }

    public function destroyShiftRotating(int $id)
    {
        $shiftRotating = ShiftRotating::findOrFail($id);
        ShiftRotatingEmployee::where('shift_rotating_id', $shiftRotating->id)->delete();
        ShiftAttendance::where('shift_rotating_id', $shiftRotating->id)->delete();

        if ($shiftRotating->delete()) {
            $this->appLogService->logChange($shiftRotating, 'deleted');
        }
        return $shiftRotating;
    }

    public function updateStatusShiftRotating(int $id, bool $alreadyGenerated)
    {
        $shiftRotating = ShiftRotating::find($id);
        $shiftRotating->update(['already_generated'=>$alreadyGenerated]);
        $this->appLogService->logChange($shiftRotating, 'updated');
        return $shiftRotating;
    }

}
