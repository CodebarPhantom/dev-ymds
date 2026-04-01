<?php

namespace App\Http\Controllers\Api\V1\BackOffice\Workforce;

use Illuminate\Http\Request;
use App\Http\Controllers\MasterController;
use App\Models\Role;
use App\Models\Workforce\EmployeePayroll;
use Illuminate\Support\Facades\Gate;
use App\Services\Workforce\EmployeeRoleRemunerationPackageService;
use App\Models\Workforce\Employee;
use Illuminate\Support\Facades\Log;


class EmployeeRoleRemunerationPackageController extends MasterController
{

    protected $employeeRoleRemunerationPackageService;


    public function __construct(EmployeeRoleRemunerationPackageService $employeeRoleRemunerationPackageService)
    {
        parent::__construct();
        $this->employeeRoleRemunerationPackageService = $employeeRoleRemunerationPackageService;
    }

    public function dataTable(Request $request)
    {

        Gate::authorize('readPolicy', EmployeePayroll::class);

        $search = $request->input('search', ''); // Search query
        $pageSize = $request->input('size', 10); // Default to 10
        $sortField = $request->input('sortField', 'name'); // Default sort field
        $sortOrder = $request->input('sortOrder', 'asc'); // Default sort order

        // Validate sort field and order
        $allowedSortFields = ['name']; // Add your sortable fields
        $allowedSortOrders = ['asc', 'desc'];

        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'name'; // Fallback to default
        }

        if (!in_array($sortOrder, $allowedSortOrders)) {
            $sortOrder = 'asc'; // Fallback to default
        }
        $employeeRoleRemunerationPackages = Role::with(['remunerationPackages'])->withCount(['remunerationPackages'])->active()->where(function ($query) use ($search) {
            if (!empty($search)) {
                $query->whereRaw('name ILIKE ?', ['%' . $search . '%']);
            }
        })
        ->orderBy($sortField, $sortOrder)
        ->paginate($pageSize);

       // Log::debug( $employeeRoleRemunerationPackages);
        // Prepare the response
        return response()->json([
            'page' => $employeeRoleRemunerationPackages->currentPage(),
            'pageCount' => $employeeRoleRemunerationPackages->lastPage(),
            'sortField' => $sortField,
            'sortOrder' => $sortOrder,
            'totalCount' => $employeeRoleRemunerationPackages->total(),
            'data' =>  $employeeRoleRemunerationPackages->items(),
        ]);
    }

    public function deleteRemuneration($id)
    {
        $func = function () use ($id) {
            Gate::authorize('readPolicy', EmployeePayroll::class);

            $this->employeeRoleRemunerationPackageService->deleteRemuneration($id);
            $this->messages = ['Remunerasi Berhasil di Hapus'];
            $this->code = 200;
        };

        return $this->callFunction($func);
    }
}
