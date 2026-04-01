<?php

namespace App\Http\Controllers\Api\V1\BackOffice\WorkSchedule;

use App\Models\WorkSchedule\Shift;
use Illuminate\Http\Request;
use App\Http\Controllers\MasterController;
use App\Models\WorkSchedule\ShiftFixed;
use App\Models\WorkSchedule\ShiftFixedLog;
use App\Services\WorkSchedule\ShiftFixedService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class ShiftFixedController extends MasterController
{
    protected $shiftFixedService;

    // Inject multiple services through the constructor
    public function __construct(ShiftFixedService $shiftFixedService)
    {
        parent::__construct();
        $this->shiftFixedService = $shiftFixedService;
    }


    /**
     * Show the form for creating a new resource.
     */
    public function dataTable(Request $request)
    {
        Gate::authorize('readPolicy', Shift::class);

        $search = $request->input('search', ''); // Search query
        $pageSize = $request->input('size', 10); // Default to 10
        $sortField = $request->input('sortField', 'created_at'); // Default sort field
        $sortOrder = $request->input('sortOrder', 'desc'); // Default sort order

        // Validate sort field and order
        $allowedSortFields = ['created_at', 'shift_name']; // Add sortable fields
        $allowedSortOrders = ['asc', 'desc'];

        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'created_at'; // Fallback to default
        }

        if (!in_array($sortOrder, $allowedSortOrders)) {
            $sortOrder = 'desc'; // Fallback to default
        }

        $shiftFixed = ShiftFixed::with('shift') // Assuming a relationship `shift` exists
            ->withCount('rolesShiftFixed') // Count related `employeeShifts`
            ->whereHas('shift', function ($query) use ($search) {
                if (!empty($search)) {
                    $query->where('name', 'ILIKE', "%{$search}%");
                }
            })
            ->orderBy($sortField === 'shift_name' ? 'shifts.name' : "shift_fixeds.{$sortField}", $sortOrder)
            ->paginate($pageSize);


        //Log::debug($shiftFixed);

        // Prepare the response
        return response()->json([
            'page' => $shiftFixed->currentPage(),
            'pageCount' => $shiftFixed->lastPage(),
            'sortField' => $sortField,
            'sortOrder' => $sortOrder,
            'totalCount' => $shiftFixed->total(),
            'data' =>  $shiftFixed->items(),
        ]);
    }

    public function shiftFixedLogsDataTable(Request $request, $shiftFixedId)
    {
        Gate::authorize('readPolicy', Shift::class);

        $search = $request->input('search', ''); // Search query
        $pageSize = $request->input('size', 10); // Default to 10
        $sortField = $request->input('sortField', 'created_at'); // Default sort field
        $sortOrder = $request->input('sortOrder', 'desc'); // Default sort order

        $allowedSortFields = ['created_at', 'shift_name']; // Add sortable fields
        $allowedSortOrders = ['asc', 'desc'];

        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'created_at'; // Fallback to default
        }

        if (!in_array($sortOrder, $allowedSortOrders)) {
            $sortOrder = 'desc'; // Fallback to default
        }

        $shiftFixedLog = ShiftFixedLog::where('shift_fixed_id', $shiftFixedId)
            ->orderBy($sortField, $sortOrder)
            ->paginate($pageSize);

        return response()->json([
            'page' => $shiftFixedLog->currentPage(),
            'pageCount' => $shiftFixedLog->lastPage(),
            'sortField' => $sortField,
            'sortOrder' => $sortOrder,
            'totalCount' => $shiftFixedLog->total(),
            'data' =>  $shiftFixedLog->items(),
        ]);
    }

    public function shiftFixedLogDestroy($shiftFixedLogId)
    {
        $func = function () use ($shiftFixedLogId) {
            Gate::authorize('deletePolicy', Shift::class);

            $this->shiftFixedService->destroyShiftFixedLog($shiftFixedLogId);
        };

        return $this->callFunction($func, null, null);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {


        $func = function () use ($id) {
            Gate::authorize('deletePolicy', Shift::class);

            $this->shiftFixedService->destroyShiftFixed($id);
        };

        return $this->callFunction($func, null, null);
    }
}
