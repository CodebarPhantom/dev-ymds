<?php

namespace App\Http\Controllers\Api\V1\BackOffice\Report;

use App\Exports\EmployeeAttendanceExport;
use Illuminate\Http\Request;
use App\Http\Controllers\MasterController;
use App\Services\Report\ReportEmployeeShiftService;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ReportEmployeeShiftController extends MasterController
{
    protected $reportEmployeeShiftService;

    public function __construct(ReportEmployeeShiftService  $reportEmployeeShiftService)
    {
        parent::__construct();
        $this->reportEmployeeShiftService = $reportEmployeeShiftService;
    }

    /**
     * Display a listing of the resource.
     */
    public function shift(Request $request)
    {
        $func = function () use ($request) {

            $data = $request->validate([
                'start_date' => 'required',
                'end_date' => 'required',
                'shift_id'=>'required'
            ]);

            $reports = $this->reportEmployeeShiftService->getShiftEmployee($data);
            //Log::debug(['dari controlles: ',$reports]);
            $this->data = compact('reports');
        };

        return $this->callFunction($func);
    }
}
