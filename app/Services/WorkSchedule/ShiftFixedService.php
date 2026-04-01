<?php

namespace App\Services\WorkSchedule;

use App\Models\WorkSchedule\ShiftAttendance;
use App\Models\WorkSchedule\ShiftFixed;
use App\Models\WorkSchedule\ShiftFixedLog;
use App\Models\WorkSchedule\ShiftFixedRole;
use App\Services\MasterService;
use Illuminate\Support\Arr;


class ShiftFixedService extends MasterService
{
    public function createShiftFixed(array $data)
    {

        //dd($data);
        $data['day_off'] = array_map('intval', $data['day_off']);

        $shiftFixedData = Arr::except($data, ['roles']);
        $shiftFixed = ShiftFixed::create($shiftFixedData);

        foreach ($data['roles'] as $roleId) {
            $shiftRole = [
                'role_id' => $roleId,
                'shift_fixed_id' => $shiftFixed->id
            ];
            $fixedShiftRole = ShiftFixedRole::create($shiftRole);
            $this->appLogService->logChange($fixedShiftRole, 'created');
        }


        $this->appLogService->logChange($shiftFixed, 'created');
        return $shiftFixed;
    }

    public function getAllShiftFixed()
    {
        return ShiftFixed::get();

    }

    public function shiftFixedQuery()
    {
        return ShiftFixed::query();
    }

    public function getShiftFixedById(int $id)
    {
        return ShiftFixed::find($id);

    }

    public function updateShiftFixed(int $id, array $data)
    {

        $shiftFixedData = Arr::except($data, ['roles']);
        $shiftFixedData['day_off'] = array_map('intval', $shiftFixedData['day_off']);

        $shiftFixed = ShiftFixed::find($id);
        $shiftFixed->update($shiftFixedData);
        $this->appLogService->logChange($shiftFixed, 'updated');

        ShiftFixedRole::where('shift_fixed_id', $shiftFixed->id)->delete();
        foreach ($data['roles'] as $roleId) {
            $shiftRole = [
                'role_id' => $roleId,
                'shift_fixed_id' => $shiftFixed->id
            ];

            $fixedShiftRole = ShiftFixedRole::create($shiftRole);
            $this->appLogService->logChange($fixedShiftRole, 'updated');

        }
        return $shiftFixed;
    }

    public function destroyShiftFixed(int $id)
    {
        $shiftFixed = ShiftFixed::findOrFail($id);
        ShiftFixedRole::where('shift_fixed_id', $shiftFixed->id)->delete();

        if ($shiftFixed->delete()) {
            $this->appLogService->logChange($shiftFixed, 'deleted');
        }
        return $shiftFixed;
    }

    public function destroyShiftFixedLog(int $shiftFixedLogId)
    {

        $shiftFixedLog = ShiftFixedLog::findOrFail($shiftFixedLogId);
        ShiftAttendance::where('shift_fixed_log_id',$shiftFixedLogId)->delete();

        if ($shiftFixedLog->delete()) {
            $this->appLogService->logChange($shiftFixedLog, 'deleted');
        }
        return $shiftFixedLog;
    }

    public function createShiftFixedLog(array $data)
    {
        $shiftFixedLog = ShiftFixedLog::create($data);

        return $shiftFixedLog;

    }

    public function shiftFixedLogQuery()
    {
        return ShiftFixedLog::query();

    }

}
