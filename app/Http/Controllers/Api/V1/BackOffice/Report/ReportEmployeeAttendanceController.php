<?php

namespace App\Http\Controllers\Api\V1\BackOffice\Report;

use App\Exports\EmployeeAttendanceExport;
use Illuminate\Http\Request;
use App\Http\Controllers\MasterController;
use App\Services\Report\ReportEmployeeAttendanceService;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;


class ReportEmployeeAttendanceController extends MasterController
{
    protected $reportEmployeeAttendanceService;

    public function __construct(ReportEmployeeAttendanceService $reportEmployeeAttendanceService)
    {
        parent::__construct();
        $this->reportEmployeeAttendanceService = $reportEmployeeAttendanceService;
    }

    /**
     * Display a listing of the resource.
     */
    public function attendance(Request $request)
    {
        $func = function () use ($request) {

            $data = $request->validate([
                'month' => 'required|integer|min:1|max:12',
                'year' => 'required|integer|min:1900|max:2100',
            ]);

            $month = $data['month'];
            $year = $data['year'];
            $reports = $this->reportEmployeeAttendanceService->getEmployeeAttendance($month, $year);

            $this->data = compact('reports');
        };

        return $this->callFunction($func);
    }



    public function exportAttendance(Request $request)
    {
        $validated = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:1900|max:2100',
        ]);

        $data = $this->reportEmployeeAttendanceService->getEmployeeAttendance($validated['month'], $validated['year']);

        $period = Carbon::create($validated['year'], $validated['month'], 1)->format('F Y');
        $daysInMonth = Carbon::create($validated['year'], $validated['month'], 1)->daysInMonth;

        return Excel::download(new EmployeeAttendanceExport($data,$daysInMonth,$period),"employee_attendance_report_{$request->year}_{$request->month}.xlsx");
    }
}
