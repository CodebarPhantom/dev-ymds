<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\MasterController;
use App\Models\Permission;
use Illuminate\Http\Request;
use App\Services\PermissionService;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class PermissionController extends MasterController
{
    protected $permissionService;

    // Inject multiple services through the constructor
    public function __construct(PermissionService $permissionService)
    {
        parent::__construct();
        $this->permissionService = $permissionService;
    }

    public function dataTable( Request $request){

        Gate::authorize('readPolicy', Permission::class);

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

        $permissions = Permission::with('permissionGroup')
        ->where(function ($query) use ($search) {
            if (!empty($search)) {
                $query->whereRaw('name ILIKE ?', ['%'.$search.'%']);
            }
        })
        ->orderBy($sortField, $sortOrder)
        ->paginate($pageSize);


        $permissionData = $permissions->map(function($permission) {
            return [
                'id' => $permission->id,
                'name' => $permission->name,
                'permissionGroupName'=> $permission->permissionGroup->name,
            ];
        });

        // Prepare the response
        return response()->json([
            'page' => $permissions->currentPage(),
            'pageCount' => $permissions->lastPage(),
            'sortField' => $sortField,
            'sortOrder' => $sortOrder,
            'totalCount' => $permissions->total(),
            'data' =>  $permissionData,
        ]);
    }


}
