<?php

namespace App\Http\Controllers\Api\V1\BackOffice;

use App\Http\Controllers\MasterController;
use App\Services\RoleService;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class RoleController extends MasterController
{
    protected $roleService;

    // Inject multiple services through the constructor
    public function __construct(RoleService $roleService)
    {
        parent::__construct();
        $this->roleService = $roleService;
    }

    public function dataTable( Request $request){
        Gate::authorize('readPolicy', Role::class);

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
        $roles = Role::where(function ($query) use ($search) {
            if (!empty($search)) {
                $query->whereRaw('name ILIKE ?', ['%'.$search.'%']);
            }
        })
        ->orderBy($sortField, $sortOrder)
        ->paginate($pageSize);


        $roleData = $roles->map(function($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'is_active'=> $role->is_active,
                'isActiveName'=> $role->is_active_name,
                'isActiveColor' => $role->is_active_color, // Accessor used here
            ];
        });


                       // Prepare the response
        return response()->json([
            'page' => $roles->currentPage(),
            'pageCount' => $roles->lastPage(),
            'sortField' => $sortField,
            'sortOrder' => $sortOrder,
            'totalCount' => $roles->total(),
            'data' =>  $roleData,
        ]);
    }

    public function getAllRole()
    {
        $func = function () {
            //Gate::authorize('readPolicy', Role::class);

            $rolesData = $this->roleService->getAllRole()->select('id','name')->toArray();

            $roles = array_map(function ($item) {
                return [
                    "id" => $item["id"],
                    "label" => $item["name"], // Change 'name' to 'label'
                ];
            }, $rolesData);

            $this->data = compact('roles');
        };

        return $this->callFunction($func);
    }

    // public function destroy($id)
    // {
    //     $func = function () use ($id) {

    //         $this->permissionGroupService->deletePermissionGroup($id);

    //     };

    //     return $this->callFunction($func, null, null);
    // }
}
