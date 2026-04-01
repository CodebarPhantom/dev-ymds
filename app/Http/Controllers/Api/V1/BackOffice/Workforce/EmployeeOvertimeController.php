<?php

namespace App\Http\Controllers\Api\V1\BackOffice\Workforce;

use App\Http\Controllers\MasterController;
use App\Models\Workforce\EmployeeOvertime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Services\Workforce\EmployeeService;
use App\Services\Workforce\EmployeeOvertimeService;
use Carbon\Carbon;

class EmployeeOvertimeController extends MasterController
{
    protected $employeeService;
    protected $employeeOvertimeService;


    // Inject multiple services through the constructor
    public function __construct(EmployeeService $employeeService, EmployeeOvertimeService $employeeOvertimeService)
    {
        parent::__construct();
        $this->employeeService = $employeeService;
        $this->employeeOvertimeService = $employeeOvertimeService;
    }

    public function dataTable(Request $request)
    {
        Gate::authorize('readPolicy', EmployeeOvertime::class);

        $search = $request->input('search', ''); // Search query
        $pageSize = $request->input('size', 10); // Default to 10
        $sortField = $request->input('sortField', 'created_at'); // Default sort field
        $sortOrder = $request->input('sortOrder', 'desc'); // Default sort order


        // Adjust sort field to use correct table alias
        //$sortField = $sortField === 'name' ? 'employees.name' : $sortField;

        $employees = EmployeeOvertime::with(['employee','shift'])
            ->join('employees', 'employee_overtimes.employee_id', '=', 'employees.id') // Join with employees table
            ->where(function ($query) use ($search) {
                if (!empty($search)) {
                    $query->where('employees.name', 'ILIKE', '%' . $search . '%');
                }
            })
            ->orderBy('created_at', 'desc')
            ->select('employee_overtimes.*') // Ensure correct columns are selected
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
}
