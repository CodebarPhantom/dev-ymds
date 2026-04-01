<?php

namespace App\Http\Controllers\Api\V1\BackOffice;

use App\Http\Controllers\MasterController;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Services\DivisionService;
use Illuminate\Support\Facades\Log;

class DivisionController extends MasterController
{
    protected $divisionService;

    // Inject multiple services through the constructor
    public function __construct(DivisionService $divisionService)
    {
        parent::__construct();
        $this->divisionService = $divisionService;
    }

    public function dataTable( Request $request){

        Gate::authorize('readPolicy', Division::class);


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
        $divisions = Division::where(function ($query) use ($search) {
            if (!empty($search)) {
                $query->whereRaw('name ILIKE ?', ['%'.$search.'%']);
            }
        })
        ->orderBy($sortField, $sortOrder)
        ->paginate($pageSize);

        $divisionData = $divisions->map(function($division) {
            return [
                'id' => $division->id,
                'name' => $division->name,
                'is_active'=> $division->is_active,
                'isActiveName'=> $division->is_active_name,
                'isActiveColor' => $division->is_active_color, // Accessor used here
            ];
        });

                       // Prepare the response
        return response()->json([
            'page' => $divisions->currentPage(),
            'pageCount' => $divisions->lastPage(),
            'sortField' => $sortField,
            'sortOrder' => $sortOrder,
            'totalCount' => $divisions->total(),
            'data' =>  $divisionData,
        ]);
    }

    public function destroy($id)
    {
        $func = function () use ($id) {

            Gate::authorize('deletePolicy', Division::class);

            $this->divisionService->deleteDivision($id);

        };

        return $this->callFunction($func, null, null);
    }
}
