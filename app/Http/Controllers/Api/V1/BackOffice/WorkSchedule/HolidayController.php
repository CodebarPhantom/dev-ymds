<?php

namespace App\Http\Controllers\Api\V1\BackOffice\WorkSchedule;

use App\Http\Controllers\MasterController;
use App\Models\WorkSchedule\Holiday;
use App\Services\WorkSchedule\HolidayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;


class HolidayController extends MasterController
{

    protected $holidayService;

    // Inject multiple services through the constructor
    public function __construct(HolidayService $holidayService)
    {
        parent::__construct();
        $this->holidayService = $holidayService;
    }


    /**
     * Show the form for creating a new resource.
     */
    public function dataTable(Request $request)
    {
        Gate::authorize('readPolicy', Holiday::class);

        $search = $request->input('search', ''); // Search query
        $pageSize = $request->input('size', 10); // Default to 10
        $sortField = $request->input('sortField', 'date'); // Default sort field
        $sortOrder = $request->input('sortOrder', 'desc'); // Default sort order

        // Validate sort field and order
        $allowedSortFields = ['name']; // Add your sortable fields
        $allowedSortOrders = ['asc', 'desc'];

        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'date'; // Fallback to default
        }

        if (!in_array($sortOrder, $allowedSortOrders)) {
            $sortOrder = 'desc'; // Fallback to default
        }
        $holidays = Holiday::withCount('holidayRoles')
            ->where(function ($query) use ($search) {
                if (!empty($search)) {
                    $query->whereRaw('name ILIKE ?', ['%' . $search . '%']);
                }
            })
            ->orderBy($sortField, $sortOrder)
            ->paginate($pageSize);


        // Prepare the response
        return response()->json([
            'page' => $holidays->currentPage(),
            'pageCount' => $holidays->lastPage(),
            'sortField' => $sortField,
            'sortOrder' => $sortOrder,
            'totalCount' => $holidays->total(),
            'data' =>  $holidays->items(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {


        $func = function () use ($id) {
            Gate::authorize('deletePolicy', Holiday::class);

            $this->holidayService->destroyHoliday($id);
        };

        return $this->callFunction($func, null, null);
    }
}
