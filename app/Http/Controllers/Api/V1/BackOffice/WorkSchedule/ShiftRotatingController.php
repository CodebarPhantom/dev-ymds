<?php

namespace App\Http\Controllers\Api\V1\BackOffice\WorkSchedule;

use Illuminate\Http\Request;
use App\Models\WorkSchedule\Shift;
use App\Models\WorkSchedule\ShiftRotating;
use App\Services\WorkSchedule\ShiftRotatingService;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\MasterController;
use Illuminate\Support\Facades\Log;

class ShiftRotatingController extends MasterController
{
    protected $shiftRotatingService;

    // Inject multiple services through the constructor
    public function __construct(ShiftRotatingService $shiftRotatingService)
    {
        parent::__construct();
        $this->shiftRotatingService = $shiftRotatingService;
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

        $shiftRotating = ShiftRotating::with('shift') // Assuming a relationship `shift` exists
            ->withCount('employeeShifts') // Count related `employeeShifts`
            ->whereHas('shift', function ($query) use ($search) {
                if (!empty($search)) {
                    $query->where('name', 'ILIKE', "%{$search}%");
                }
            })
            ->orderBy($sortField === 'shift_name' ? 'shifts.name' : "shift_rotatings.{$sortField}", $sortOrder)
            ->paginate($pageSize);

        // Prepare the response
        return response()->json([
            'page' => $shiftRotating->currentPage(),
            'pageCount' => $shiftRotating->lastPage(),
            'sortField' => $sortField,
            'sortOrder' => $sortOrder,
            'totalCount' => $shiftRotating->total(),
            'data' =>  $shiftRotating->items(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {

        $func = function () use ($id) {
            Gate::authorize('deletePolicy', Shift::class);

            $this->shiftRotatingService->destroyShiftRotating($id);
        };

        return $this->callFunction($func, null, null);
    }

    public function store(Request $request)
    {
        $func = function () use ($request) {
            Gate::authorize('createPolicy', Shift::class);

            $validatedData = $request->validate([
                'shifts' => 'required|array|min:1',
                'shifts.*.start_date' => 'required|date',
                'shifts.*.end_date' => 'required|date|after_or_equal:shifts.*.start_date',
                'shifts.*.shift_employees' => 'required|array|min:1',
                'shifts.*.shift_id' => 'required|integer|exists:shifts,id',
                'shifts.*.is_dayoff' => 'required|boolean'
            ]);

            foreach ($validatedData['shifts'] as $shiftData) {
                $this->shiftRotatingService->createShiftRotating($shiftData);
            }

        };

        return $this->callFunction($func, null, null);
    }
}
