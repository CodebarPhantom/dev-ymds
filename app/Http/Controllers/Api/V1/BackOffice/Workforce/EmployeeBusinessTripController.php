<?php

namespace App\Http\Controllers\Api\V1\BackOffice\Workforce;

use App\Http\Controllers\MasterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\Workforce\EmployeeBusinessTrip;
use App\Services\Workforce\EmployeeBusinessTripService;
use Carbon\Carbon;

class EmployeeBusinessTripController extends MasterController
{
    protected $employeeBusinessTripService;


    // Inject multiple services through the constructor
    public function __construct( EmployeeBusinessTripService $employeeBusinessTripService)
    {
        parent::__construct();
        $this->employeeBusinessTripService = $employeeBusinessTripService;
    }

    public function dataTable(Request $request)
    {
        Gate::authorize('readPolicy', EmployeeBusinessTrip::class);

        $search = $request->input('search', ''); // Search query
        $pageSize = $request->input('size', 10); // Default to 10
        $sortField = $request->input('sortField', 'created_at'); // Default sort field
        $sortOrder = $request->input('sortOrder', 'desc'); // Default sort order


        // Adjust sort field to use correct table alias
        //$sortField = $sortField === 'name' ? 'employees.name' : $sortField;

        $employees = EmployeeBusinessTrip::with(['employee'])
            ->join('employees', 'employee_business_trips.employee_id', '=', 'employees.id') // Join with employees table
            ->where(function ($query) use ($search) {
                if (!empty($search)) {
                    $query->where('employees.name', 'ILIKE', '%' . $search . '%');
                }
            })
            ->orderBy('created_at', 'desc')
            ->select('employee_business_trips.*') // Ensure correct columns are selected
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
