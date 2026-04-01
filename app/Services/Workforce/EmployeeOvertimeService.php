<?php

namespace App\Services\Workforce;

use App\Models\Workforce\Employee;
use App\Models\Workforce\EmployeeLeave;
use App\Services\MasterService;
use App\Enums\OvertimeStatus;
use App\Enums\ShiftAttendanceStatus;
use App\Enums\GlobalDepartmentId;
use App\Models\Workforce\EmployeeOvertime;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EmployeeOvertimeService extends MasterService
{

    public function createOvertime(array $data)
    {

        //dd($data);
        $authUserData = Auth::user();
        $userReportToId = $authUserData->employee->reportTo ? $authUserData->employee->reportTo->user->id : null;
        $employeeReportToId = $authUserData->employee->reportTo ? $authUserData->employee->reportTo->id : null;


        $data['employee_id'] = $authUserData->employee_id;
        $data['known_by'] = $employeeReportToId;
        $data['request_day'] = (int) $this->diffInWeekdays($data['start_date'], $data['end_date']);
        $data['last_overtime_id'] = EmployeeOvertime::where('employee_id', $authUserData->employee_id)->latest()->value('id');


        $data['status'] = $userReportToId === null
            ? OvertimeStatus::MENUNGGU_PERSETUJUAN_HR->value
            : OvertimeStatus::MENUNGGU_PERSETUJUAN_ATASAN->value;

        $overtime = EmployeeOvertime::create($data);
        $this->appLogService->logChange($overtime, 'created');

        if ($userReportToId === null) {

            $employeeHrs = Employee::where('department_id', GlobalDepartmentId::HUMAN_RESOURCE->value)
                ->whereHas('user') // Ensure employee has a related user
                ->get();
            foreach ($employeeHrs as $employeeHr) {
                $this->baseNotificationService->createNotification(
                    'Persetujuan Pengajuan Lembur',
                    "Pengajuan lembur oleh {$authUserData->name} mohon untuk diperiksa",
                    $employeeHr->user->id,
                    GlobalDepartmentId::HUMAN_RESOURCE->value
                );
            }
        } else {
            $this->baseNotificationService->createNotification(
                'Persetujuan Pengajuan Lembur',
                "Pengajuan lembur oleh {$authUserData->name} mohon untuk diperiksa",
                $userReportToId
            );
        }
        return $overtime;
    }

    public function createOvertimeForEmployee(array $data)
    {
        $employee = Employee::find($data['employee_id']);
        //$employeeReportToId = $employee->reportTo ? $employee->reportTo->id : null;
        // $data['known_by'] = $employeeReportToId;
        // $data['known_at'] = Carbon::now();
        $data['approved_by'] = Auth::user()->employee->id;
        $data['approved_at'] = Carbon::now();
        $data['status'] = OvertimeStatus::DISETUJUI->value;
        $data['last_overtime_id'] = EmployeeOvertime::where('employee_id', $data['employee_id'])->latest()->value('id');


        $overtime = EmployeeOvertime::create($data);

        // $startDate = Carbon::parse($overtime->start_date)->startOfDay();
        // $endDate = Carbon::parse($overtime->end_date)->endOfDay();
        // for ($date = $startDate; $date <= $endDate; $date->addDay()) {

        //     $shiftAttendance = [
        //         'employee_id' => $overtime->employee_id,
        //         'date' => $date->toDateString(),
        //         'status' => ShiftAttendanceStatus::CUTI->value
        //     ];
        //     $this->shiftAttendanceService->createShiftAttendance($shiftAttendance);
        // }
        $this->appLogService->logChange($overtime, 'created');
        $this->baseNotificationService->createNotification(
            'Lembur telah disetujui',
            'Lembur anda telah disetujui oleh ' . Auth::user()->name,
            $employee->user->id
        );
    }
    public function showOvertime($id)
    {
        $overtime = EmployeeOvertime::with(['knownBy', 'approvedBy', 'rejectBy', 'employee'])->find($id);

        return $overtime;
    }


    public function approveOvertime(int $id, $data)
    {
        $overtime = EmployeeOvertime::find($id);
        $employee = Employee::find($overtime->employee_id);

        if ($overtime->status->value === OvertimeStatus::MENUNGGU_PERSETUJUAN_ATASAN->value) {
            $overtime->update(['status' => OvertimeStatus::MENUNGGU_PERSETUJUAN_HR->value, 'known_by' => Auth::user()->employee->id, 'known_at' => Carbon::now()]);

            $employeeHrs = Employee::where('department_id', GlobalDepartmentId::HUMAN_RESOURCE->value)
                ->whereHas('user') // Ensure employee has a related user
                ->get();
            foreach ($employeeHrs as $employeeHr) {
                $this->baseNotificationService->createNotification(
                    'Ada lembur yang perlu diperiksa',
                    "Lembur atas nama {$employee->user->name} perlu diperiksa",
                    $employeeHr->user->id,
                    GlobalDepartmentId::HUMAN_RESOURCE->value
                );
            }
        } elseif ($overtime->status->value === OvertimeStatus::MENUNGGU_PERSETUJUAN_HR->value) {

            $overtime->update([
                'status' => OvertimeStatus::DISETUJUI->value,
                'approved_by' => Auth::user()->employee->id,
                'approved_at' => Carbon::now(),
                'overtime_pay' => $data['overtime_pay']
            ]);

            // $startDate = Carbon::parse($overtime->start_date)->startOfDay();
            // $endDate = Carbon::parse($overtime->end_date)->endOfDay();
            // for ($date = $startDate; $date <= $endDate; $date->addDay()) {

            //     $shiftAttendance = [
            //         'employee_id' => $overtime->employee_id,
            //         'date' => $date->toDateString(),
            //         'status' => ShiftAttendanceStatus::CUTI->value
            //     ];
            //     $this->shiftAttendanceService->createShiftAttendance($shiftAttendance);
            // }
        }

        $this->appLogService->logChange($overtime, 'updated');
        $this->baseNotificationService->createNotification(
            'Lembur telah disetujui',
            'Lembur anda telah disetujui oleh ' . Auth::user()->name,
            $employee->user->id
        );
        return;
    }

    public function cancelOvertime(int $id)
    {
        $overtime = EmployeeOvertime::find($id);
        $authUserData = Auth::user();
        $userReportToId = $authUserData->employee->reportTo ? $authUserData->employee->reportTo->user->id : null;

        if ($overtime->known_by !== null) {
            $this->baseNotificationService->createNotification(
                'Lembur telah dibatalkan',
                "Pengajuan lembur {$authUserData->name} telah dibatalkan",
                $userReportToId
            );
        }

        if ($overtime->approved_by !== null) {
            $this->baseNotificationService->createNotification(
                'Lembur telah dibatalkan',
                "Pengajuan lembur {$authUserData->name} telah dibatalkan",
                $overtime->approved_by
            );
        }


        $this->appLogService->logChange($overtime, 'updated');

        $overtime->update(['status' => OvertimeStatus::DIBATALKAN->value]);
        return;
    }

    public function rejectOvertime(int $id)
    {
        $overtime = EmployeeOvertime::find($id);
        $employee = Employee::find($overtime->employee_id);
        $overtime->update(['status' => OvertimeStatus::DITOLAK->value, 'reject_by' => Auth::user()->employee->id]);
        $this->appLogService->logChange($overtime, 'updated');
        $this->baseNotificationService->createNotification(
            'Lembur telah ditolak',
            "Pengajuan lembur anda telah ditolak oleh " . Auth::user()->name,
            $employee->user->id
        );
        return;
    }

    public function updateOvertime(int $id, array $data)
    {
        $overtime = EmployeeOvertime::find($id);
        $employee = Employee::find($overtime->employee_id);
        $overtime->update([
            'overtime_pay' => $data['overtime_pay'],
            'status' => OvertimeStatus::DISETUJUI->value
        ]);
        $this->appLogService->logChange($overtime, 'updated');
        $this->baseNotificationService->createNotification(
            'Lembur telah disetujui',
            "Pengajuan lembur anda telah disetujui oleh " . Auth::user()->name,
            $employee->user->id
        );

        return;
    }

    public function getOvertimeDateEmployee($employeeId, $startDate, $endDate)
    {
        return EmployeeOvertime::where('employee_id', $employeeId)
            ->where('status', OvertimeStatus::DISETUJUI->value)
            ->where('start_date', '>=', $startDate)
            ->where('end_date', '<=', $endDate)
            ->get();
    }

    private function diffInWeekdays($startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $days = 0;

        while ($start->lte($end)) {
            if (!$start->isWeekend()) {
                $days++;
            }
            $start->addDay();
        }

        return $days;
    }
}
