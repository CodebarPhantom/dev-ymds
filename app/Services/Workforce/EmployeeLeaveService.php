<?php

namespace App\Services\Workforce;

use App\Models\Workforce\Employee;
use App\Models\Workforce\EmployeeLeave;
use App\Services\MasterService;
use App\Enums\LeaveStatus;
use App\Enums\ShiftAttendanceStatus;
use App\Enums\GlobalDepartmentId;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EmployeeLeaveService extends MasterService
{

    public function createLeave(array $data)
    {
        $authUserData = Auth::user();
        $userReportToId = $authUserData->employee->reportTo ? $authUserData->employee->reportTo->user->id : null;
        $employeeReportToId = $authUserData->employee->reportTo ? $authUserData->employee->reportTo->id : null;

        $data['type'] = $data['type'] ?? 'pengurangan';
        $data['employee_id'] = $authUserData->employee_id;
        $data['known_by'] = $employeeReportToId;
        $data['request_day'] = (int) $this->diffInWeekdays($data['start_date'], $data['end_date']);
        $data['last_leave_id'] = EmployeeLeave::where('employee_id', $authUserData->employee_id)->latest()->value('id');


        $data['status'] = $userReportToId === null
            ? LeaveStatus::MENUNGGU_PERSETUJUAN_HR->value
            : LeaveStatus::MENUNGGU_PERSETUJUAN_ATASAN->value;

        $leave = EmployeeLeave::create($data);
        $this->appLogService->logChange($leave, 'created');

        if ($userReportToId === null) {

            $employeeHrs = Employee::where('department_id', GlobalDepartmentId::HUMAN_RESOURCE->value)
                ->whereHas('user') // Ensure employee has a related user
                ->get();
            foreach ($employeeHrs as $employeeHr) {
                $this->baseNotificationService->createNotification(
                    'Persetujuan Pengajuan Cuti',
                    "Pengajuan cuti oleh {$authUserData->name} mohon untuk diperiksa",
                    $employeeHr->user->id,
                    GlobalDepartmentId::HUMAN_RESOURCE->value
                );
            }
        } else {
            $this->baseNotificationService->createNotification(
                'Persetujuan Pengajuan Cuti',
                "Pengajuan cuti oleh {$authUserData->name} mohon untuk diperiksa",
                $userReportToId
            );
        }
        return $leave;
    }

    public function createLeaveForEmployee(array $data)
    {
        $employee = Employee::find($data['employee_id']);
        $remainingLeave = $employee->leave; // kalo khusus ga masuk ke rumus ini
        $leaveType =  $data['type'];
        //$employeeReportToId = $employee->reportTo ? $employee->reportTo->id : null;
        // $data['known_by'] = $employeeReportToId;
        // $data['known_at'] = Carbon::now();
        $data['approved_by'] = Auth::user()->employee->id;
        $data['approved_at'] = Carbon::now();
        $data['request_day'] = (int) $this->diffInWeekdays($data['start_date'], $data['end_date']);
        $data['approve_day'] = (int) $this->diffInWeekdays($data['start_date'], $data['end_date']);
        $data['status'] = LeaveStatus::DISETUJUI->value;
        $data['last_leave_id'] = EmployeeLeave::where('employee_id', $data['employee_id'])->latest()->value('id');


        if ($leaveType === 'pengurangan') {
            $remainingLeave = $employee->leave - $data['request_day'];
        } elseif ($leaveType === 'penambahan') {
            $remainingLeave = $employee->leave + $data['request_day'];
        } elseif ($leaveType === 'reset') {
            $remainingLeave = 0;
        } else {
            $remainingLeave = $employee->leave; // kalo khusus ga masuk ke rumus ini
        }
        $employee->update(['leave' => $remainingLeave]);

        $leave = EmployeeLeave::create($data);

        $startDate = Carbon::parse($leave->start_date)->startOfDay();
        $endDate = Carbon::parse($leave->end_date)->endOfDay();
        for ($date = $startDate; $date <= $endDate; $date->addDay()) {

            $shiftAttendance = [
                'employee_id' => $leave->employee_id,
                'date' => $date->toDateString(),
                'status' => ShiftAttendanceStatus::CUTI->value
            ];
            $this->shiftAttendanceService->createShiftAttendance($shiftAttendance);
        }
        $this->appLogService->logChange($leave, 'created');
        $this->baseNotificationService->createNotification(
            'Cuti telah disetujui',
            'Cuti anda telah disetujui oleh ' . Auth::user()->name,
            $employee->user->id
        );
    }
    public function showLeave($id)
    {
        $leave = EmployeeLeave::with(['knownBy', 'approvedBy', 'rejectBy', 'employee'])->find($id);

        return $leave;
    }

    public function remainingLeave(int $employeeId)
    {
        return Employee::findOrFail($employeeId)->leave;
    }

    public function approveLeave(int $id)
    {
        $leave = EmployeeLeave::find($id);
        $employee = Employee::find($leave->employee_id);

        if ($leave->status->value === LeaveStatus::MENUNGGU_PERSETUJUAN_ATASAN->value) {
            $leave->update(['status' => LeaveStatus::MENUNGGU_PERSETUJUAN_HR->value, 'known_by' => Auth::user()->employee->id, 'known_at' => Carbon::now()]);

            $employeeHrs = Employee::where('department_id', GlobalDepartmentId::HUMAN_RESOURCE->value)
                ->whereHas('user') // Ensure employee has a related user
                ->get();
            foreach ($employeeHrs as $employeeHr) {
                $this->baseNotificationService->createNotification(
                    'Ada cuti yang perlu diperiksa',
                    "Cuti atas nama {$employee->user->name} perlu diperiksa",
                    $employeeHr->user->id,
                    GlobalDepartmentId::HUMAN_RESOURCE->value
                );
            }
        } elseif ($leave->status->value === LeaveStatus::MENUNGGU_PERSETUJUAN_HR->value) {

            $leave->update([
                'status' => LeaveStatus::DISETUJUI->value,
                'approved_by' => Auth::user()->employee->id,
                'approved_at' => Carbon::now(),
                'approve_day' => $leave->request_day
            ]);

            $startDate = Carbon::parse($leave->start_date)->startOfDay();
            $endDate = Carbon::parse($leave->end_date)->endOfDay();
            for ($date = $startDate; $date <= $endDate; $date->addDay()) {

                $shiftAttendance = [
                    'employee_id' => $leave->employee_id,
                    'date' => $date->toDateString(),
                    'status' => ShiftAttendanceStatus::CUTI->value
                ];
                $this->shiftAttendanceService->createShiftAttendance($shiftAttendance);
            }

            $remainingLeave = $employee->leave - $leave->request_day;
            $employee->update(['leave' => $remainingLeave]);
        }

        $this->appLogService->logChange($leave, 'updated');
        $this->baseNotificationService->createNotification(
            'Cuti telah disetujui',
            'Cuti anda telah disetujui oleh ' . Auth::user()->name,
            $employee->user->id
        );
        return;
    }

    public function cancelLeave(int $id)
    {
        $leave = EmployeeLeave::find($id);
        $authUserData = Auth::user();
        $userReportToId = $authUserData->employee->reportTo ? $authUserData->employee->reportTo->user->id : null;

        if ($leave->known_by !== null) {
            $this->baseNotificationService->createNotification(
                'Cuti telah dibatalkan',
                "Pengajuan cuti {$authUserData->name} telah dibatalkan",
                $userReportToId
            );
        }

        if ($leave->approved_by !== null) {
            $this->baseNotificationService->createNotification(
                'Cuti telah dibatalkan',
                "Pengajuan cuti {$authUserData->name} telah dibatalkan",
                $leave->approved_by
            );
        }


        $this->appLogService->logChange($leave, 'updated');

        $leave->update(['status' => LeaveStatus::DIBATALKAN->value]);
        return;
    }

    public function rejectLeave(int $id)
    {
        $leave = EmployeeLeave::find($id);
        $employee = Employee::find($leave->employee_id);
        $leave->update(['status' => LeaveStatus::DITOLAK->value, 'reject_by' => Auth::user()->employee->id]);
        $this->appLogService->logChange($leave, 'updated');
        $this->baseNotificationService->createNotification(
            'Cuti telah ditolak',
            "Pengajuan cuti anda telah ditolak oleh " . Auth::user()->name,
            $employee->user->id
        );
        return;
    }

    public function updateLeave(int $id, array $data)
    {
        $leave = EmployeeLeave::find($id);
        $leave->update(['type' => $data['type']]);
        $this->appLogService->logChange($leave, 'updated');

        return;
    }

    public function getLeaveDateEmployee($employeeId, $startDate, $endDate)
    {
        return EmployeeLeave::where('employee_id', $employeeId)
            ->where('status', LeaveStatus::DISETUJUI->value)
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
