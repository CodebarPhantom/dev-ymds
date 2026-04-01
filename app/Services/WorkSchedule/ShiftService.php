<?php

namespace App\Services\WorkSchedule;

use App\Models\WorkSchedule\Shift;
use App\Services\MasterService;

class ShiftService extends MasterService
{
    public function createShift(array $data)
    {
        $shift = Shift::create($data);
        $this->appLogService->logChange($shift, 'created');
        return $shift;
    }

    public function getAllShift()
    {
        return Shift::orderBy('name','asc')->get();

    }

    public function getShiftById(int $id)
    {
        return Shift::find($id);

    }

    public function updateShift(int $id, array $data)
    {
        $shift = Shift::find($id);
        $shift->update($data);
        $this->appLogService->logChange($shift, 'updated');
        return $shift;
    }

    public function destroyShift(int $id)
    {
        $shift = Shift::findOrFail($id);
        if ($shift->delete()) {
            $this->appLogService->logChange($shift, 'deleted');
        }
        return $shift;
    }
}
