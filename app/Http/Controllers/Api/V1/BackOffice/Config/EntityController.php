<?php

namespace App\Http\Controllers\Api\V1\BackOffice;

use App\Http\Controllers\MasterController;
use App\Models\Entity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Services\EntityService;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class EntityController extends MasterController
{
    protected $entityService;

    // Inject multiple services through the constructor
    public function __construct(EntityService $entityService)
    {
        parent::__construct();
        $this->entityService = $entityService;
    }

    public function dataTable( Request $request){

        Gate::authorize('readPolicy', Entity::class);

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
        $entities = Entity::where(function ($query) use ($search) {
            if (!empty($search)) {
                $query->whereRaw('name ILIKE ?', ['%'.$search.'%']);
            }
        })
        ->orderBy($sortField, $sortOrder)
        ->paginate($pageSize);

        $entityData = $entities->map(function($entity) {
            return [
                'id' => $entity->id,
                'name' => $entity->name,
                'status'=> $entity->status,
                'statusName'=> $entity->status_name,
                'statusColor' => $entity->status_color, // Accessor used here
            ];
        });

                       // Prepare the response
        return response()->json([
            'page' => $entities->currentPage(),
            'pageCount' => $entities->lastPage(),
            'sortField' => $sortField,
            'sortOrder' => $sortOrder,
            'totalCount' => $entities->total(),
            'data' =>  $entityData,
        ]);
    }

    public function destroy($id)
    {
        $func = function () use ($id) {
            Gate::authorize('deletePolicy', Entity::class);

            $this->entityService->deleteEntity($id);

        };

        return $this->callFunction($func, null, null);
    }
}
