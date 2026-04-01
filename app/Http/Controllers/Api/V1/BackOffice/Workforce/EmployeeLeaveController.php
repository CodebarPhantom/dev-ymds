<?php

namespace App\Http\Controllers\Api\V1\BackOffice\Workforce;

use App\Http\Controllers\MasterController;
use App\Models\Workforce\EmployeeLeave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Services\Workforce\EmployeeService;
use App\Services\Workforce\EmployeeLeaveService;
use Carbon\Carbon;

class EmployeeLeaveController extends MasterController
{
    protected $employeeService;
    protected $employeeLeaveService;


    // Inject multiple services through the constructor
    public function __construct(EmployeeService $employeeService, EmployeeLeaveService $employeeLeaveService)
    {
        parent::__construct();
        $this->employeeService = $employeeService;
        $this->employeeLeaveService = $employeeLeaveService;
    }

    public function dataTable(Request $request)
    {
        Gate::authorize('readPolicy', EmployeeLeave::class);

        $search = $request->input('search', ''); // Search query
        $pageSize = $request->input('size', 10); // Default to 10
        $sortField = $request->input('sortField', 'created_at'); // Default sort field
        $sortOrder = $request->input('sortOrder', 'desc'); // Default sort order


        // Adjust sort field to use correct table alias
        //$sortField = $sortField === 'name' ? 'employees.name' : $sortField;

        $employees = EmployeeLeave::with(['employee'])
            ->join('employees', 'employee_leaves.employee_id', '=', 'employees.id') // Join with employees table
            ->where(function ($query) use ($search) {
                if (!empty($search)) {
                    $query->where('employees.name', 'ILIKE', '%' . $search . '%');
                }
            })
            ->orderBy('created_at', 'desc')
            ->select('employee_leaves.*') // Ensure correct columns are selected
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
