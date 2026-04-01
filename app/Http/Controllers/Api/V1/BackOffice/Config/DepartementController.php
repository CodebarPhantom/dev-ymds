<?php

namespace App\Http\Controllers\Api\V1\BackOffice;

use App\Http\Controllers\MasterController;
use App\Models\Departement;
use Illuminate\Http\Request;
use App\Services\DepartementService;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class DepartementController extends MasterController
{
    protected $departementService;

    // Inject multiple services through the constructor
    public function __construct(DepartementService $departementService)
    {
        parent::__construct();
        $this->departementService = $departementService;
    }

    public function dataTable( Request $request){

        Gate::authorize('readPolicy', Departement::class);

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

        $departements = Departement::with('division')
        ->where(function ($query) use ($search) {
            if (!empty($search)) {
                $query->whereRaw('name ILIKE ?', ['%'.$search.'%']);
            }
        })
        ->orderBy($sortField, $sortOrder)
        ->paginate($pageSize);

        $departementData = $departements->map(function($departement) {
            return [
                'id' => $departement->id,
                'name' => $departement->name,

                'is_active'=> $departement->is_active,
                'divisionName'=> $departement->division->name,
                'isActiveName'=> $departement->is_active_name,
                'isActiveColor' => $departement->is_active_color
            ];
        });

                       // Prepare the response
        return response()->json([
            'page' => $departements->currentPage(),
            'pageCount' => $departements->lastPage(),
            'sortField' => $sortField,
            'sortOrder' => $sortOrder,
            'totalCount' => $departements->total(),
            'data' =>  $departementData,
        ]);


         // dd($request);
        // // Get the requested page size or use 10 as the default if not provided
        // $pageSize = $request->get('size', 10);
        // // Get paginated companies (adjust the pagination limit as needed)

        // $companies = Company::paginate($pageSize);
        // // Map the company data to match the structure you need
        // $data = $companies->map(function ($company) {
        //     return [
        //         'id' => $company->id,
        //         'name' => $company->name,
        //         'address' => $company->address,
        //         'status' => $company->status
        //     ];
        // });

        // Build the paginated response structure
        // $response = [
        //     'page' => $companies->currentPage(),
        //     'pageCount' => $companies->lastPage(),
        //     'sortField' => null, // Adjust this as needed if sorting is implemented
        //     'sortOrder' => null, // Adjust this as needed if sorting is implemented
        //     'totalCount' => $companies->total(),
        //     'data' => $data,
        // ];

        // Return as JSON response
    }

    public function destroy($id)
    {
        $func = function () use ($id) {
            Gate::authorize('deletePolicy', Departement::class); // is from policy


            $this->departementService->deleteDepartement($id);

        };

        return $this->callFunction($func, null, null);
    }

    public function getDepartmentsForSelect(Request $request)
    {
        $func = function () use ($request) {
            //Gate::authorize('readPolicy', Departement::class);

            $divisionId = $request->input('division_id');
            $departements = $this->departementService->getAllDepartmentsForSelect( $divisionId);

            $this->data = compact('departements');
        };

        return $this->callFunction($func);
    }
}
