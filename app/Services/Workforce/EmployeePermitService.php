<?php

namespace App\Services\Workforce;

use App\Models\Workforce\Employee;
use App\Models\Workforce\EmployeePermit;
use App\Services\MasterService;
use App\Enums\PermitStatus;
use App\Enums\ShiftAttendanceStatus;
use App\Enums\GlobalDepartmentId;
use App\Enums\PermitType;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EmployeePermitService extends MasterService
{
    public function createPermit(array $data)
    {
        $authUserData = Auth::user();
        $userReportToId = $authUserData->employee->reportTo ? $authUserData->employee->reportTo->user->id : null;
        $employeeReportToId = $authUserData->employee->reportTo ? $authUserData->employee->reportTo->id : null;

        $data['employee_id'] = $authUserData->employee_id;
        $data['known_by'] = $employeeReportToId;
        $data['last_permit_id'] = EmployeePermit::where('employee_id', $authUserData->employee_id)->latest()->value('id');


        $data['status'] = $userReportToId === null
            ? PermitStatus::MENUNGGU_PERSETUJUAN_HR->value
            : PermitStatus::MENUNGGU_PERSETUJUAN_ATASAN->value;

        $permit = EmployeePermit::create($data);
        $this->appLogService->logChange($permit, 'created');

        if ($userReportToId === null) {

            $employeeHrs = Employee::where('department_id', GlobalDepartmentId::HUMAN_RESOURCE->value)
                ->whereHas('user') // Ensure employee has a related user
                ->get();
            foreach ($employeeHrs as $employeeHr) {
                $this->baseNotificationService->createNotification(
                    'Persetujuan Pengajuan Izin',
                    "Pengajuan Izin oleh {$authUserData->name} mohon untuk diperiksa",
                    $employeeHr->user->id,
                    GlobalDepartmentId::HUMAN_RESOURCE->value
                );
            }
        } else {
            $this->baseNotificationService->createNotification(
                'Persetujuan Pengajuan Izin',
                "Pengajuan Izin oleh {$authUserData->name} mohon untuk diperiksa",
                $userReportToId
            );
        }
        return $permit;
    }

    public function createPermitForEmployee(array $data)
    {
        $employee = Employee::find($data['employee_id']);
        //$employeeReportToId = $employee->reportTo ? $employee->reportTo->id : null;
        // $data['known_by'] = $employeeReportToId;
        // $data['known_at'] = Carbon::now();
        $data['approved_by'] = Auth::user()->employee->id;
        $data['approved_at'] = Carbon::now();
        $data['request_day'] = (int) $this->diffInWeekdays($data['start_date'], $data['end_date']);
        $data['approve_day'] = (int) $this->diffInWeekdays($data['start_date'], $data['end_date']);
        $data['status'] = PermitStatus::DISETUJUI->value;
        $data['last_permit_id'] = EmployeePermit::where('employee_id', $data['employee_id'])->latest()->value('id');


        $permit = EmployeePermit::create($data);
        $startDate = Carbon::parse($permit->start_date)->startOfDay();
        $endDate = Carbon::parse($permit->end_date)->endOfDay();
        for ($date = $startDate; $date <= $endDate; $date->addDay()) {

            $shiftAttendance = [
                'employee_id' => $permit->employee_id,
                'date' => $date->toDateString(),
                //'status' => ShiftAttendanceStatus::IZIN->value
            ];
            $this->shiftAttendanceService->createShiftAttendance($shiftAttendance);
        }
        $this->appLogService->logChange($permit, 'created');
        $this->baseNotificationService->createNotification(
            'Izin telah disetujui',
            'Izin anda telah disetujui oleh ' . Auth::user()->name,
            $employee->user->id
        );

        return;
    }
    public function showPermit($id)
    {
        return EmployeePermit::with(['knownBy', 'approvedBy', 'rejectBy', 'employee'])->find($id);
    }

    public function approvePermit(int $id)
    {
        $permit = EmployeePermit::find($id);
        $employee = Employee::find($permit->employee_id);

        if ($permit->status->value === PermitStatus::MENUNGGU_PERSETUJUAN_ATASAN->value) {
            $permit->update(['status' => PermitStatus::MENUNGGU_PERSETUJUAN_HR->value, 'known_by' => Auth::user()->employee->id, 'known_at' => Carbon::now()]);

            $employeeHrs = Employee::where('department_id', GlobalDepartmentId::HUMAN_RESOURCE->value)
                ->whereHas('user') // Ensure employee has a related user
                ->get();
            foreach ($employeeHrs as $employeeHr) {
                $this->baseNotificationService->createNotification(
                    'Ada Izin yang perlu diperiksa',
                    "Izin atas nama {$employee->user->name} perlu diperiksa",
                    $employeeHr->user->id,
                    GlobalDepartmentId::HUMAN_RESOURCE->value
                );
            }
        } elseif ($permit->status->value === PermitStatus::MENUNGGU_PERSETUJUAN_HR->value) {

            $permit->update([
                'status' => PermitStatus::DISETUJUI->value,
                'approved_by' => Auth::user()->employee->id,
                'approved_at' => Carbon::now(),
                'approve_day' => $permit->request_day
            ]);

            $startDate = Carbon::parse($permit->start_date)->startOfDay();
            $endDate = Carbon::parse($permit->end_date)->endOfDay();
            if ($permit->permit_type->value === PermitType::SAKIT->value) { //jika sakit maka looping berapa hari sakit
                for ($date = $startDate; $date <= $endDate; $date->addDay()) {
                    $shiftAttendance = [
                        'employee_id' => $permit->employee_id,
                        'date' => $date->toDateString(),
                        'status' => ShiftAttendanceStatus::SAKIT->value
                    ];
                    $this->shiftAttendanceService->createShiftAttendance($shiftAttendance);
                }
            }
        }

        $this->appLogService->logChange($permit, 'updated');
        $this->baseNotificationService->createNotification(
            'Izin telah disetujui',
            'Izin anda telah disetujui oleh ' . Auth::user()->name,
            $employee->user->id
        );
        return;
    }

    public function approvePermitUnpaidLeave(int $id)
    {
        //dd('test');
        $permit = EmployeePermit::find($id);
        $employee = Employee::find($permit->employee_id);

        $permit->update([
            'status' => PermitStatus::DISETUJUI->value,
            'approved_by' => Auth::user()->employee->id,
            'approved_at' => Carbon::now(),
            'approve_day' => $permit->request_day
        ]);

        $startDate = Carbon::parse($permit->start_date)->startOfDay();
        $endDate = Carbon::parse($permit->end_date)->endOfDay();
        for ($date = $startDate; $date <= $endDate; $date->addDay()) {

            $shiftAttendance = [
                'employee_id' => $permit->employee_id,
                'date' => $date->toDateString(),
                'status' => ShiftAttendanceStatus::UNPAID_LEAVE->value
            ];
            $this->shiftAttendanceService->createShiftAttendance($shiftAttendance);
        }

        $this->appLogService->logChange($permit, 'updated');
        $this->baseNotificationService->createNotification(
            'Izin telah disetujui',
            'Izin anda telah disetujui oleh ' . Auth::user()->name,
            $employee->user->id
        );
        return;
    }

    public function cancelPermit(int $id)
    {
        $permit = EmployeePermit::find($id);
        $authUserData = Auth::user();
        $userReportToId = $authUserData->employee->reportTo ? $authUserData->employee->reportTo->user->id : null;

        if ($permit->known_by !== null) {
            $this->baseNotificationService->createNotification(
                'Izin telah dibatalkan',
                "Pengajuan Izin {$authUserData->name} telah dibatalkan",
                $userReportToId
            );
        }

        if ($permit->approved_by !== null) {
            $this->baseNotificationService->createNotification(
                'Izin telah dibatalkan',
                "Pengajuan Izin {$authUserData->name} telah dibatalkan",
                $permit->approved_by
            );
        }


        $this->appLogService->logChange($permit, 'updated');

        $permit->update(['status' => PermitStatus::DIBATALKAN->value]);
        return;
    }

    public function rejectPermit(int $id)
    {
        $permit = EmployeePermit::find($id);
        $employee = Employee::find($permit->employee_id);
        $permit->update(['status' => PermitStatus::DITOLAK->value, 'reject_by' => Auth::user()->employee->id]);
        $this->appLogService->logChange($permit, 'updated');
        $this->baseNotificationService->createNotification(
            'Izin telah ditolak',
            "Pengajuan Izin anda telah ditolak oleh " . Auth::user()->name,
            $employee->user->id
        );
        return;
    }

    public function updatePermit(int $id, array $data)
    {
        $permit = EmployeePermit::find($id);
        $permit->update(['type' => $data['type']]);
        $this->appLogService->logChange($permit, 'updated');

        return;
    }

    public function getPermitDateEmployee($employeeId, $startDate, $endDate)
    {
        return EmployeePermit::where('employee_id', $employeeId)
            ->where('status', PermitStatus::DISETUJUI->value)
            ->where('start_date', '>=', $startDate)
            ->where('end_date', '<=', $endDate)
            ->get();

        // return EmployeePermit::where('employee_id', $employeeId)
        // ->where('status', PermitStatus::DISETUJUI->value)
        // ->where(function ($query) use ($startDate, $endDate) {
        //     $query->whereBetween('start_date', [$startDate, $endDate])
        //           ->orWhereBetween('end_date', [$startDate, $endDate])
        //           ->orWhere(function ($q) use ($startDate, $endDate) {
        //               $q->where('start_date', '<', $startDate)
        //                 ->where('end_date', '>', $endDate);
        //           });
        // })
        // ->get();
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
