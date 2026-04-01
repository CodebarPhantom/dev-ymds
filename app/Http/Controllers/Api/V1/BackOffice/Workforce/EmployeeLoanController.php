<?php
namespace App\Http\Controllers\Api\V1\BackOffice\Workforce;


use App\Http\Controllers\MasterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\Workforce\EmployeeLoan;
use App\Services\Workforce\EmployeeLoanService;

class EmployeeLoanController extends MasterController
{
    protected $employeeLoanService;

    public function __construct(
        EmployeeLoanService $employeeLoanService
    ) {
        parent::__construct();
        $this->employeeLoanService = $employeeLoanService;
    }


    public function dataTable(Request $request)
    {
        Gate::authorize('readPolicy', EmployeeLoan::class);

        $search = $request->input('search', ''); // Search query
        $pageSize = $request->input('size', 10); // Default to 10
        $sortField = $request->input('sortField', 'created_at'); // Default sort field
        $sortOrder = $request->input('sortOrder', 'desc'); // Default sort order


        // Adjust sort field to use correct table alias
        //$sortField = $sortField === 'name' ? 'employees.name' : $sortField;

        $employees = EmployeeLoan::with(['employee'])
            ->join('employees', 'employee_loans.employee_id', '=', 'employees.id') // Join with employees table
            ->where(function ($query) use ($search) {
                if (!empty($search)) {
                    $query->where('employees.name', 'ILIKE', '%' . $search . '%');
                }
            })
            ->orderBy('created_at', 'desc')
            ->select('employee_loans.*') // Ensure correct columns are selected
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
            Gate::authorize('deletePolicy', EmployeeLoan::class);

            $this->employeeLoanService->deleteLoan($id);

        };

        return $this->callFunction($func, null, null);
    }

}
