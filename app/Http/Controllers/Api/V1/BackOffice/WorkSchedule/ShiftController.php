<?php

namespace App\Http\Controllers\Api\V1\BackOffice\WorkSchedule;

use App\Models\WorkSchedule\Shift;
use Illuminate\Http\Request;
use App\Http\Controllers\MasterController;
use App\Services\WorkSchedule\ShiftService;
use Illuminate\Support\Facades\Gate;


class ShiftController extends MasterController
{
    protected $shiftService;

    // Inject multiple services through the constructor
    public function __construct(ShiftService $shiftService)
    {
        parent::__construct();
        $this->shiftService = $shiftService;
    }


    /**
     * Show the form for creating a new resource.
     */
    public function dataTable(Request $request)
    {
        Gate::authorize('readPolicy', Shift::class);

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
        $shifts = Shift::where(function ($query) use ($search) {
            if (!empty($search)) {
                $query->whereRaw('name ILIKE ?', ['%'.$search.'%']);
            }
        })
        ->orderBy($sortField, $sortOrder)
        ->paginate($pageSize);


                       // Prepare the response
        return response()->json([
            'page' => $shifts->currentPage(),
            'pageCount' => $shifts->lastPage(),
            'sortField' => $sortField,
            'sortOrder' => $sortOrder,
            'totalCount' => $shifts->total(),
            'data' =>  $shifts->items(),
        ]);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {


        $func = function () use ($id) {
            Gate::authorize('deletePolicy', Shift::class);

            $this->shiftService->destroyShift($id);

        };

        return $this->callFunction($func, null, null);

    }
}
