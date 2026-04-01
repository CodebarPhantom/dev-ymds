<?php

namespace App\Services\Workforce;

use App\Models\Workforce\EmployeePayrollComben;
use App\Services\MasterService;

class EmployeePayrollCombenService extends MasterService
{

    public function createComben($data)
    {
        $comben = EmployeePayrollComben::create($data);
        $this->appLogService->logChange($comben, 'created');

        return $comben;
    }
    public function getAllCombens()
    {
        return EmployeePayrollComben::with(['employee'])->get();
    }

    public function getCombenById($id)
    {
        return EmployeePayrollComben::with(['employee'])->find($id);
    }

    public function deleteComben($id)
    {
        $comben = EmployeePayrollComben::findOrFail($id);
        if ($comben->delete()) {
            $this->appLogService->logChange($comben, 'deleted');
        }
        return $comben;
    }

    public function updateComben($id, array $data)
    {
        $comben = EmployeePayrollComben::find($id);
        $comben->update($data);
        $this->appLogService->logChange($comben, 'updated');
        return $comben;
    }
}
