<?php

namespace App\Http\Controllers\Api\V1\BackOffice;

use App\Http\Controllers\MasterController;
use App\Models\PermissionGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Services\PermissionGroupService;
use Illuminate\Support\Facades\Log;

class PermissionGroupController extends MasterController
{
    protected $permissionGroupService;

    // Inject multiple services through the constructor
    public function __construct(PermissionGroupService $permissionGroupService)
    {
        parent::__construct();
        $this->permissionGroupService = $permissionGroupService;
    }

    public function dataTable( Request $request){
        Gate::authorize('readPolicy', PermissionGroup::class);

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
        $permissionGroups = PermissionGroup::where(function ($query) use ($search) {
            if (!empty($search)) {
                $query->whereRaw('name ILIKE ?', ['%'.$search.'%']);
            }
        })
        ->orderBy($sortField, $sortOrder)
        ->paginate($pageSize);

        $permissionGroupData = $permissionGroups->map(function($permissionGroup) {
            return [
                'id' => $permissionGroup->id,
                'name' => $permissionGroup->name,
                'is_active'=> $permissionGroup->is_active,
                'isActiveName'=> $permissionGroup->is_active_name,
                'isActiveColor' => $permissionGroup->is_active_color, // Accessor used here
            ];
        });

                       // Prepare the response
        return response()->json([
            'page' => $permissionGroups->currentPage(),
            'pageCount' => $permissionGroups->lastPage(),
            'sortField' => $sortField,
            'sortOrder' => $sortOrder,
            'totalCount' => $permissionGroups->total(),
            'data' =>  $permissionGroupData,
        ]);
    }

    public function destroy($id)
    {
        $func = function () use ($id) {
            Gate::authorize('deletePolicy', PermissionGroup::class);

            $this->permissionGroupService->deletePermissionGroup($id);

        };

        return $this->callFunction($func, null, null);
    }
}
