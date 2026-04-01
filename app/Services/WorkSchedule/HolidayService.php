<?php

namespace App\Services\WorkSchedule;

use App\Models\WorkSchedule\Holiday;
use App\Services\MasterService;
use App\Enums\ShiftAttendanceStatus;
use App\Models\Workforce\Employee;
use App\Models\WorkSchedule\HolidayRole;
use Illuminate\Support\Arr;


class HolidayService extends MasterService
{
    public function createHoliday(array $data)
    {
        $holiday = Holiday::create($data);

        foreach ($data['roles'] as $roleId) {
            $holidayRole = [
                'role_id' => $roleId,
                'holiday_id' => $holiday->id
            ];
            $holidayRoleData = HolidayRole::create($holidayRole);
            $this->appLogService->logChange($holidayRoleData, 'created');
        }


        $shiftAttendanceData = [
            'date' => $data['date'],
            'status' => ShiftAttendanceStatus::LIBUR_NASIONAL->value
        ];

        $employeeIds = Employee::whereIn('role_id', $data['roles'])->pluck('id');


        $this->shiftAttendanceService->updateShiftAttendanceByDateAndEmployee($shiftAttendanceData, $employeeIds);
        $this->appLogService->logChange($holiday, 'created');
        return $holiday;
    }

    public function getAllHoliday()
    {
        return Holiday::orderBy('date', 'asc')->get();
    }

    public function getHolidayById(int $id)
    {
        return Holiday::find($id);
    }

    public function getHolidayByDate($date)
    {
        return Holiday::where('date', $date)->exists();
    }

    public function getHolidayByDateAndRole($date,$roleId)
    {
        return Holiday::where('date', $date)
            ->whereHas('holidayRoles', function ($query) use ($roleId) {
                $query->where('role_id', $roleId);
            })
            ->exists();
    }

    public function updateHoliday(int $id, array $data)
    {
        $holidayData = Arr::except($data, ['roles']);

        $holiday = Holiday::find($id);
        $this->appLogService->logChange($holiday, 'updated');
        $getHolidayRole = HolidayRole::where('holiday_id', $holiday->id);

        $getCurrentRoleIds =  $getHolidayRole->get()->pluck('role_id');
        $currentEmployeeIds = Employee::whereIn('role_id', $getCurrentRoleIds)->pluck('id');
        $currentShiftAttendanceData = [
            'date' => $holiday->date,
            'status' => ShiftAttendanceStatus::TIDAK_MASUK->value
        ];
        $this->shiftAttendanceService->updateShiftAttendanceByDateAndEmployee($currentShiftAttendanceData, $currentEmployeeIds);

        $deleteCurrentRole = $getHolidayRole->delete();
        $holiday->update($holidayData);

        foreach ($data['roles'] as $roleId) {
            $holidayRole = [
                'role_id' => $roleId,
                'holiday_id' => $holiday->id
            ];

            $holidayRoleData = HolidayRole::create($holidayRole);
            $this->appLogService->logChange($holidayRoleData, 'updated');
        }
        $shiftAttendanceData = [
            'date' => $data['date'],
            'status' => ShiftAttendanceStatus::LIBUR_NASIONAL->value
        ];
        $employeeIds = Employee::whereIn('role_id', $data['roles'])->pluck('id');
        $this->shiftAttendanceService->updateShiftAttendanceByDateAndEmployee($shiftAttendanceData, $employeeIds);

        return $holiday;
    }

    public function destroyHoliday(int $id)
    {
        $holiday = Holiday::findOrFail($id);
        $getHolidayRole = HolidayRole::where('holiday_id', $holiday->id);
        $getCurrentRoleIds =  $getHolidayRole->get()->pluck('role_id');
        $currentEmployeeIds = Employee::whereIn('role_id', $getCurrentRoleIds)->pluck('id');
        $currentShiftAttendanceData = [
            'date' => $holiday->date,
            'status' => ShiftAttendanceStatus::TIDAK_MASUK->value
        ];
        $this->shiftAttendanceService->updateShiftAttendanceByDateAndEmployee($currentShiftAttendanceData, $currentEmployeeIds);
        $deleteCurrentRole = $getHolidayRole->delete();

        if ($holiday->delete()) {
            $this->appLogService->logChange($holiday, 'deleted');
        }
        return $holiday;
    }
}
