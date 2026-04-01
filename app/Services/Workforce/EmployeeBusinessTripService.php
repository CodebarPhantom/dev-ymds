<?php

namespace App\Services\Workforce;

use App\Enums\BusinessTripStatus;
use App\Models\Workforce\Employee;
use App\Services\MasterService;
use App\Enums\ShiftAttendanceStatus;
use App\Enums\GlobalDepartmentId;
use App\Models\Workforce\EmployeeBusinessTrip;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EmployeeBusinessTripService extends MasterService
{
    public function createBusinessTrip(array $data)
    {
        $authUserData = Auth::user();
        $userReportToId = $authUserData->employee->reportTo ? $authUserData->employee->reportTo->user->id : null;
        $employeeReportToId = $authUserData->employee->reportTo ? $authUserData->employee->reportTo->id : null;

        $data['employee_id'] = $authUserData->employee_id;
        $data['known_by'] = $employeeReportToId;
        $data['last_business_trip_id'] = EmployeeBusinessTrip::where('employee_id', $authUserData->employee_id)->latest()->value('id');


        $data['status'] = $userReportToId === null
            ? BusinessTripStatus::MENUNGGU_PERSETUJUAN_HR->value
            : BusinessTripStatus::MENUNGGU_PERSETUJUAN_ATASAN->value;

        $businessTrip = EmployeeBusinessTrip::create($data);
        $this->appLogService->logChange($businessTrip, 'created');

        if ($userReportToId === null) {

            $employeeHrs = Employee::where('department_id', GlobalDepartmentId::HUMAN_RESOURCE->value)
                ->whereHas('user') // Ensure employee has a related user
                ->get();
            foreach ($employeeHrs as $employeeHr) {
                $this->baseNotificationService->createNotification(
                    'Persetujuan Pengajuan Perjalanan Dinas',
                    "Pengajuan Perjalanan Dinas oleh {$authUserData->name} mohon untuk diperiksa",
                    $employeeHr->user->id,
                    GlobalDepartmentId::HUMAN_RESOURCE->value
                );
            }
        } else {
            $this->baseNotificationService->createNotification(
                'Persetujuan Pengajuan Perjalanan Dinas',
                "Pengajuan Perjalanan Dinas oleh {$authUserData->name} mohon untuk diperiksa",
                $userReportToId
            );
        }
        return $businessTrip;
    }

    public function createBusinessTripForEmployee(array $data)
    {
        $employee = Employee::find($data['employee_id']);
        //$employeeReportToId = $employee->reportTo ? $employee->reportTo->id : null;
        // $data['known_by'] = $employeeReportToId;
        // $data['known_at'] = Carbon::now();
        $data['approved_by'] = Auth::user()->employee->id;
        $data['approved_at'] = Carbon::now();
        $data['status'] = BusinessTripStatus::DISETUJUI->value;
        $data['last_business_trip_id'] = EmployeeBusinessTrip::where('employee_id', $data['employee_id'])->latest()->value('id');


        $businessTrip = EmployeeBusinessTrip::create($data);
        $startDate = Carbon::parse($businessTrip->start_date)->startOfDay();
        $endDate = Carbon::parse($businessTrip->end_date)->endOfDay();
        for ($date = $startDate; $date <= $endDate; $date->addDay()) {

            $shiftAttendance = [
                'employee_id' => $businessTrip->employee_id,
                'date' => $date->toDateString(),
                'status' => ShiftAttendanceStatus::PERJALANAN_DINAS->value
            ];
            $this->shiftAttendanceService->createShiftAttendance($shiftAttendance);
        }
        $this->appLogService->logChange($businessTrip, 'created');
        $this->baseNotificationService->createNotification(
            'Perjalanan Dinas telah disetujui',
            'Perjalanan Dinas anda telah disetujui oleh ' . Auth::user()->name,
            $employee->user->id
        );

        return;
    }
    public function showBusinessTrip($id)
    {
        return EmployeeBusinessTrip::with(['knownBy', 'approvedBy', 'rejectBy', 'employee'])->find($id);
    }

    public function approveBusinessTrip(int $id)
    {
        $businessTrip = EmployeeBusinessTrip::find($id);
        $employee = Employee::find($businessTrip->employee_id);

        if ($businessTrip->status->value === BusinessTripStatus::MENUNGGU_PERSETUJUAN_ATASAN->value) {
            $businessTrip->update(['status' => BusinessTripStatus::MENUNGGU_PERSETUJUAN_HR->value, 'known_by' => Auth::user()->employee->id, 'known_at' => Carbon::now()]);

            $employeeHrs = Employee::where('department_id', GlobalDepartmentId::HUMAN_RESOURCE->value)
                ->whereHas('user') // Ensure employee has a related user
                ->get();
            foreach ($employeeHrs as $employeeHr) {
                $this->baseNotificationService->createNotification(
                    'Ada perjalanan dinas yang perlu diperiksa',
                    "Perjalanan dinas atas nama {$employee->user->name} perlu diperiksa",
                    $employeeHr->user->id,
                    GlobalDepartmentId::HUMAN_RESOURCE->value
                );
            }
        } elseif ($businessTrip->status->value === BusinessTripStatus::MENUNGGU_PERSETUJUAN_HR->value) {

            $businessTrip->update([
                'status' => BusinessTripStatus::DISETUJUI->value,
                'approved_by' => Auth::user()->employee->id,
                'approved_at' => Carbon::now(),
            ]);

            $startDate = Carbon::parse($businessTrip->start_date)->startOfDay();
            $endDate = Carbon::parse($businessTrip->end_date)->endOfDay();

                for ($date = $startDate; $date <= $endDate; $date->addDay()) {
                    $shiftAttendance = [
                        'employee_id' => $businessTrip->employee_id,
                        'date' => $date->toDateString(),
                        'status' => ShiftAttendanceStatus::PERJALANAN_DINAS->value
                    ];
                    $this->shiftAttendanceService->createShiftAttendance($shiftAttendance);
                }

        }

        $this->appLogService->logChange($businessTrip, 'updated');
        $this->baseNotificationService->createNotification(
            'Perjalanan Dinas telah disetujui',
            'Perjalanan Dinas anda telah disetujui oleh ' . Auth::user()->name,
            $employee->user->id
        );
        return;
    }


    public function cancelBusinessTrip(int $id)
    {
        $businessTrip = EmployeeBusinessTrip::find($id);
        $authUserData = Auth::user();
        $userReportToId = $authUserData->employee->reportTo ? $authUserData->employee->reportTo->user->id : null;

        if ($businessTrip->known_by !== null) {
            $this->baseNotificationService->createNotification(
                'Perjalanan Dinas telah dibatalkan',
                "Pengajuan perjalanan dinas {$authUserData->name} telah dibatalkan",
                $userReportToId
            );
        }

        if ($businessTrip->approved_by !== null) {
            $this->baseNotificationService->createNotification(
                'Perjalanan Dinas telah dibatalkan',
                "Pengajuan perjalanan dinas {$authUserData->name} telah dibatalkan",
                $businessTrip->approved_by
            );
        }


        $this->appLogService->logChange($businessTrip, 'updated');

        $businessTrip->update(['status' => BusinessTripStatus::DIBATALKAN->value]);
        return;
    }

    public function rejectBusinessTrip(int $id)
    {
        $businessTrip = EmployeeBusinessTrip::find($id);
        $employee = Employee::find($businessTrip->employee_id);
        $businessTrip->update(['status' => BusinessTripStatus::DITOLAK->value, 'reject_by' => Auth::user()->employee->id]);
        $this->appLogService->logChange($businessTrip, 'updated');
        $this->baseNotificationService->createNotification(
            'Perjalanan Dinas telah ditolak',
            "Pengajuan perjalanan dinas anda telah ditolak oleh " . Auth::user()->name,
            $employee->user->id
        );
        return;
    }

    public function updateBusinessTrip(int $id, array $data)
    {
        $businessTrip = EmployeeBusinessTrip::find($id);
        $businessTrip->update(['type' => $data['type']]);
        $this->appLogService->logChange($businessTrip, 'updated');

        return;
    }

    public function getBusinessTripDateEmployee($employeeId, $startDate, $endDate)
    {
        return EmployeeBusinessTrip::where('employee_id', $employeeId)
            ->where('status', BusinessTripStatus::DISETUJUI->value)
            ->where('start_date', '>=', $startDate)
            ->where('end_date', '<=', $endDate)
            ->get();

        // return EmployeeBusinessTrip::where('employee_id', $employeeId)
        // ->where('status', BusinessTripStatus::DISETUJUI->value)
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
}
