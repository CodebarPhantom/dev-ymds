<?php

namespace App\Http\Controllers\Api\V1\BackOffice\Workforce;

use App\Http\Controllers\MasterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\Workforce\EmployeePermit;
use App\Services\Workforce\EmployeePermitService;
use Carbon\Carbon;

class EmployeePermitController extends MasterController
{
    protected $employeePermitService;


    // Inject multiple services through the constructor
    public function __construct( EmployeePermitService $employeePermitService)
    {
        parent::__construct();
        $this->employeePermitService = $employeePermitService;
    }

    public function dataTable(Request $request)
    {
        Gate::authorize('readPolicy', EmployeePermit::class);

        $search = $request->input('search', ''); // Search query
        $pageSize = $request->input('size', 10); // Default to 10
        $sortField = $request->input('sortField', 'created_at'); // Default sort field
        $sortOrder = $request->input('sortOrder', 'desc'); // Default sort order


        // Adjust sort field to use correct table alias
        //$sortField = $sortField === 'name' ? 'employees.name' : $sortField;

        $employees = EmployeePermit::with(['employee'])
            ->join('employees', 'employee_permits.employee_id', '=', 'employees.id') // Join with employees table
            ->where(function ($query) use ($search) {
                if (!empty($search)) {
                    $query->where('employees.name', 'ILIKE', '%' . $search . '%');
                }
            })
            ->orderBy('created_at', 'desc')
            ->select('employee_permits.*') // Ensure correct columns are selected
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
