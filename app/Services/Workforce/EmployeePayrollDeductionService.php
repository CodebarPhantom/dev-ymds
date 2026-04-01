<?php

namespace App\Services\Workforce;

use App\Models\Workforce\EmployeePayrollDeduction;
use App\Services\MasterService;

class EmployeePayrollDeductionService extends MasterService
{

    public function createDeduction($data)
    {
        $deduction = EmployeePayrollDeduction::create($data);
        $this->appLogService->logChange($deduction, 'created');

        return $deduction;
    }
    public function getAllDeductions()
    {
        return EmployeePayrollDeduction::with(['employee'])->get();
    }

    public function getDeductionById($id)
    {
        return EmployeePayrollDeduction::with(['employee'])->find($id);
    }

    public function deleteDeduction($id)
    {
        $deduction = EmployeePayrollDeduction::findOrFail($id);
        if ($deduction->delete()) {
            $this->appLogService->logChange($deduction, 'deleted');
        }
        return $deduction;
    }

    public function updateDeduction($id, array $data)
    {
        $deduction = EmployeePayrollDeduction::find($id);
        $deduction->update($data);
        $this->appLogService->logChange($deduction, 'updated');
        return $deduction;
    }
}
