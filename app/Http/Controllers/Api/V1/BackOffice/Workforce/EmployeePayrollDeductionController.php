<?php

namespace App\Http\Controllers\Api\V1\BackOffice\Workforce;

use App\Http\Controllers\MasterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\Workforce\EmployeePayroll;
use App\Models\Workforce\EmployeePayrollDeduction;
use App\Services\Workforce\EmployeePayrollDeductionService;



class EmployeePayrollDeductionController extends MasterController
{
    protected $employeePayrollDeductionService;

    public function __construct(
        EmployeePayrollDeductionService $employeePayrollDeductionService
    ) {
        parent::__construct();
        $this->employeePayrollDeductionService = $employeePayrollDeductionService;
    }


    public function dataTable(Request $request)
    {

        Gate::authorize('readPolicy', EmployeePayroll::class);
        $startDate = $request->start_date ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ?? now()->endOfMonth()->format('Y-m-d');
        $search = $request->input('search', ''); // Search query
        $pageSize = $request->input('size', 10); // Default to 10
        $sortField = $request->input('sortField', 'name'); // Default sort field
        $sortOrder = $request->input('sortOrder', 'asc'); // Default sort order

        // Validate sort field and order
        $search = $request->input('search', ''); // Search query
        $pageSize = $request->input('size', 10); // Default to 10
        $sortField = $request->input('sortField', 'created_at'); // Default sort field
        $sortOrder = $request->input('sortOrder', 'desc'); // Default sort order


        // Adjust sort field to use correct table alias
        //$sortField = $sortField === 'name' ? 'employees.name' : $sortField;

        $employees = EmployeePayrollDeduction::with(['employee'])
            ->join('employees', 'employee_payroll_deductions.employee_id', '=', 'employees.id') // Join with employees table
            ->where(function ($query) use ($search) {
                if (!empty($search)) {
                    $query->where('employees.name', 'ILIKE', '%' . $search . '%');
                }
            })
            ->orderBy('created_at', 'desc')
            ->select('employee_payroll_deductions.*') // Ensure correct columns are selected
            ->paginate($pageSize);

        // Prepare the response
        return response()->json([
            'page' => $employees->currentPage(),
            'pageCount' => $employees->lastPage(),
            'sortField' => $sortField,
            'sortOrder' => $sortOrder,
            'totalCount' => $employees->total(),
            'data' =>  $employees->items(),
        ]);
    }

    public function destroy($id)
    {
        $func = function () use ($id) {
            Gate::authorize('deletePolicy', EmployeePayroll::class);

            $this->employeePayrollDeductionService->deleteDeduction($id);

        };

        return $this->callFunction($func, null, null);
    }
}
