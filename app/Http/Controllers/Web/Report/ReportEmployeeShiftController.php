<?php

namespace App\Http\Controllers\Web\Report;


use Illuminate\Http\Request;
use App\Http\Controllers\MasterController;
use App\Services\WorkSchedule\ShiftService;



class ReportEmployeeShiftController extends MasterController
{

    protected $shiftService;

    // Inject multiple services through the constructor
    public function __construct(ShiftService $shiftService)
    {
        parent::__construct();
        $this->shiftService = $shiftService;
    }

    /**
     * Display a listing of the resource.
     */
    public function shift(Request $request)
    {
        $func = function () {
            $breadcrumbs = ['Laporan', 'Karyawan', 'Shift' ];
            $pageTitle = "Laporan Shift";
            $shifts = $this->shiftService->getAllShift();
            //auth()->user()->hasPermissionTo('report-employee-attendance');
            $this->data = compact('breadcrumbs', 'pageTitle','shifts');

        };

        return $this->callFunction($func, view('backoffice.report.employee.shift'));
    }


}
