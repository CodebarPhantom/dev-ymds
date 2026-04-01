<?php

namespace App\Http\Controllers\Api\V1\BackOffice;

use App\Http\Controllers\MasterController;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Services\CompanyService;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class CompanyController extends MasterController
{
    protected $companyService;

    // Inject multiple services through the constructor
    public function __construct(CompanyService $companyService)
    {
        parent::__construct();
        $this->companyService = $companyService;
    }

    public function dataTable( Request $request){
        Gate::authorize('readPolicy', Company::class); // is from policy

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

        $companies = Company::with('entity')
        ->where(function ($query) use ($search) {
            if (!empty($search)) {
                $query->whereRaw('name ILIKE ?', ['%'.$search.'%']);
            }
        })
        ->orderBy($sortField, $sortOrder)
        ->paginate($pageSize);

        $companyData = $companies->map(function($company) {
            return [
                'id' => $company->id,
                'name' => $company->name,
                'address' => $company->address,
                'phone' => $company->phone,
                'status'=> $company->status,
                'latitude' => $company->latitude,
                'longitude' => $company->longitude,
                'entityName'=> $company->entity->name,
                'statusName'=> $company->status_name,
                'statusColor' => $company->status_color, // Accessor used here
            ];
        });


                       // Prepare the response
        return response()->json([
            'page' => $companies->currentPage(),
            'pageCount' => $companies->lastPage(),
            'sortField' => $sortField,
            'sortOrder' => $sortOrder,
            'totalCount' => $companies->total(),
            'data' =>  $companyData,
        ]);
    }

    public function destroy($id)
    {
        $func = function () use ($id) {

            Gate::authorize('deletePolicy', Company::class); // is from policy

            $this->companyService->deleteCompany($id);

        };

        return $this->callFunction($func, null, null);
    }
}
